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
use Waffler\OpenGen\Generator;

/**
 * Class GenerateCode.
 *
 * @author ErickJMenezes <erickmenezes.dev@gmail.com>
 */
class GenerateCode extends Command
{
    protected $signature = 'waffler:generate-code 
                            {--N|namespace=* : Generate just for the specified client namespace.}
                            {--r|regenerate : Regenerates the cache array.}';

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
            return false;
        }

        $generator = new Generator();
        $baseNamespace = config('waffler.code_generation.namespace');
        $cacheArray = [];

        foreach (config('waffler.code_generation.openapi_files') as $pathToFile => $options) {
            if ($this->mustIgnoreNamespace($options['namespace'])) {
                $this->comment("Ignoring namespace \"{$options['namespace']}\" due user filtering.");
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

            $this->alert("Generating clients for \"{$options['namespace']}\" namespace.");

            $outputDir = $this->convertNamespaceToPath($options['namespace']);
            $filesOutput = $generator->fromOpenApiFile(
                $pathToFile,
                $outputDir,
                $options['namespace'],
                $options
            );
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
        if (!str_starts_with($namespace, 'App\\')) {
            $this->error("The generated code namespace must be inside \"App\" namespace.");
            exit(1);
        }

        return app_path(str_replace('\\', '/', substr($namespace, 4)));
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
        return !empty($option)
            && !in_array($namespace, (array) $option, true);
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
}
