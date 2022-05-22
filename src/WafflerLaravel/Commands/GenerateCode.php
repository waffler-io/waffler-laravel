<?php

/*
 * This file is part of Waffler.
 *
 * (c) Erick Johnson Almeida de Menezes <erickmenezes.dev@gmail.com>
 *
 * This source file is subject to the MIT licence that is bundled
 * with this source code in the file LICENCE.
 */

namespace Waffler\Laravel\Commands;

use Illuminate\Console\Command;
use RuntimeException;
use Symfony\Component\Filesystem\Filesystem;
use Waffler\OpenGen\Adapters\OpenApiV3Adapter;
use Waffler\OpenGen\Adapters\SwaggerV2Adapter;
use Waffler\OpenGen\ClientGenerator;

/**
 * Class GenerateCode.
 *
 * @author ErickJMenezes <erickmenezes.dev@gmail.com>
 */
class GenerateCode extends Command
{
    protected $signature = 'waffler:generate-code 
                            {--N|namespace=* : Generate just for the specified client namespace.}
                            {--r|regenerate : Regenerates the cache array.}
                            {--D|check-directory : Generates if the namespace directory does not exists.}
                            {--y|allow-continue : Agree that this is an experimental feature and you know the risks.}';

    protected $description = 'Generate code using OpenAPI files.';

    /**
     * @throws \cebe\openapi\exceptions\IOException
     * @throws \cebe\openapi\exceptions\TypeErrorException
     * @throws \cebe\openapi\exceptions\UnresolvableReferenceException
     * @throws \cebe\openapi\json\InvalidJsonPointerSyntaxException
     */
    public function handle(): int
    {
        if (!config()->has('waffler')) {
            $this->error('Waffler config file is not published. Use waffler:install command.');
            return 0;
        }

        if (! $this->option('allow-continue')) {
            $response = $this->askWithCompletion('Are you sure to continue and use this experimental feature?', ['y', 'n'], 'y');

            if ($response !== 'y') {
                return 0;
            }
        }

        $baseNamespace = config('waffler.code_generation.namespace');
        $cacheArray = [];

        foreach (config('waffler.code_generation.openapi_files') as $pathToFile => $options) {
            if ($this->mustIgnoreNamespace($options['namespace'])) {
                $this->comment("Ignoring namespace \"{$options['namespace']}\".");
                continue;
            }

            if (is_int($pathToFile)) {
                $pathToFile = $options;
                $options = [
                    'namespace' => $baseNamespace
                ];
            } else {
                if (isset($options['namespace'])) {
                    $options['namespace'] = "$baseNamespace\\{$options['namespace']}";
                } else {
                    $options['namespace'] = $baseNamespace;
                }
            }
            $options['file_format'] = $options['file_format'] ?? $this->guessFileFormatFromPath($pathToFile);
            $options['spec_type'] ??= 'openapi';

            $adapter = $options['spec_type'] === 'openapi' ? OpenApiV3Adapter::class : SwaggerV2Adapter::class;
            $method = $options['file_format'] === 'yaml' ? 'generateFromYamlFile' : 'generateFromJsonFile';

            $this->alert("Generating clients for \"{$options['namespace']}\" namespace.");

            $outputDir = $this->convertNamespaceToPath($options['namespace']);
            $filesOutput = (new ClientGenerator(new $adapter(
                $options['namespace'] ?? '',
                $options['interface_suffix'] ?? 'ClientInterface',
                $options['ignore']['parameters'] ?? [],
                $options['ignore']['methods'] ?? [],
                $options['remove_method_prefix'] ?? null,
            )))
                ->$method($pathToFile, $outputDir);

            if ($options['auto_bind'] ?? true) {
                $cacheArray[$options['namespace']] = $filesOutput;
            }

            $this->printGeneratedFiles($filesOutput);
        }


        $this->newLine(2);
        $this->saveCacheArray($cacheArray);
        $this->info("All clients successfuly generated.");

        return 0;
    }

    /**
     * @param string $namespace
     *
     * @return string
     * @author ErickJMenezes <erickmenezes.dev@gmail.com>
     */
    private function convertNamespaceToPath(string $namespace): string
    {
        if ($namespace !== 'App' && !str_starts_with($namespace, 'App\\')) {
            $this->error("The generated code namespace must be inside \"App\" namespace.");
            exit(1);
        }

        $pieces = explode('\\', $namespace);

        unset($pieces[0]);

        return app_path(implode(DIRECTORY_SEPARATOR, $pieces));
    }

    /**
     * @param array<string, string> $filesOutput
     *
     * @return void
     * @author ErickJMenezes <erickmenezes.dev@gmail.com>
     */
    private function printGeneratedFiles(array $filesOutput): void
    {
        foreach ($filesOutput as $interfaceName => $outputFile) {
            $this->info(
                "Successfully generated interface \"$interfaceName\" in the path \"$outputFile\".\n",
                'v'
            );
        }
    }

    /**
     * @param string $namespace
     *
     * @return bool
     * @author ErickJMenezes <erickmenezes.dev@gmail.com>
     */
    private function mustIgnoreNamespace(string $namespace): bool
    {
        $option = $this->option('namespace');
        return (!empty($option) && !in_array($namespace, (array) $option, true))
            || ($this->option('check-directory') && $this->namespaceExists($namespace));
    }

    /**
     * @param array<int|string, array<string, string>> $cache
     *
     * @return void
     * @author ErickJMenezes <erickmenezes.dev@gmail.com>
     */
    private function saveCacheArray(array $cache): void
    {
        $fullyQualifiedNames = $this->option('regenerate')
            ? []
            : config('waffler.auto_generated_clients', false);

        if (!is_array($fullyQualifiedNames)) {
            throw new RuntimeException("The \"auto_generated_clients\" array was not found in the waffler.php config file. Please create and avoid modifying it.");
        }

        foreach ($cache as $namespace => $namesAndPaths) {
            foreach ($namesAndPaths as $name => $path) {
                $fullyQualifiedNames[] = "$namespace\\$name";
            }
        }
        $currentFileContents = (string)file_get_contents(config_path('waffler.php'));
        $exportedCache = var_export(array_unique($fullyQualifiedNames), true);
        $exportedCache = preg_replace(
            ['/\s\s(\d)/', '/\)/', '/array\s*\(/', '/\)/', '/\d*\s*=>\s*/'],
            ['        $1', '    )', '[', ']', ''],
            (string)$exportedCache
        );
        $replacement = "'auto_generated_clients' => $exportedCache";
        $replacementCount = 0;
        $result = preg_replace(
            '#\'auto_generated_clients\'\s*=>\s*\[[\w\'\\\,\d=>\s_\n]*\]#',
            $replacement,
            $currentFileContents,
            1,
            $replacementCount
        );
        if ($replacementCount !== 1) {
            $this->error("The array key \"auto_generated_clients\" was not found in your waffler.php config file, please fix it and re-run the command.");
            exit(1);
        }
        file_put_contents(config_path('waffler.php'), $result);
    }

    private function namespaceExists(string $namespace): bool
    {
        return (new Filesystem())->exists(
            $this->convertNamespaceToPath(config('waffler.code_generation.namespace').'\\'.$namespace)
        );
    }

    private function guessFileFormatFromPath(mixed $pathToFile): string
    {
        if (str_ends_with($pathToFile, '.json')) {
            return 'json';
        } elseif (str_ends_with($pathToFile, '.yml') || str_ends_with($pathToFile, '.yaml')) {
            return 'yaml';
        } else {
            throw new RuntimeException("The file format could not be guessed from the path \"$pathToFile\".");
        }
    }
}
