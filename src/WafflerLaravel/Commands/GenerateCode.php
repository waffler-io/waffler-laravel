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
use Waffler\OpenGen\Generator;

/**
 * Class GenerateCode.
 *
 * @author ErickJMenezes <erickmenezes.dev@gmail.com>
 */
class GenerateCode extends Command
{
    protected $signature = 'waffler:generate-code 
                            {--N|namespace=* : Generate just for the specified namespace.}';

    protected $description = 'Generate code using OpenAPI files.';

    public function handle(): bool
    {
        if (!config()->has('waffler')) {
            $this->error('Waffler config file is not published. Use waffler:install command.');
            return false;
        }

        $generator = new Generator();
        $baseNamespace = config('waffler.code_generation.namespace');

        foreach (config('waffler.code_generation.openapi_files') as $pathToFile => $options) {
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

            if ($this->mustIgnoreNamespace($options['namespace'])) {
                $this->comment("Ignoring namespace \"{$options['namespace']}\" due user filtering.");
                continue;
            }

            $this->alert("Generating clients for \"{$options['namespace']}\" namespace.");

            $outputDir = $this->convertNamespaceToPath($options['namespace']);
            $filesOutput = $generator->fromOpenApiFile(
                $pathToFile,
                $outputDir, //@phpstan-ignore-line
                $options['namespace'],
                $options
            );

            $this->printGeneratedFiles($filesOutput);
        }

        $this->newLine(2);
        $this->info("All clients successfuly generated.");

        return true;
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
            $this->info("Successfully generated interface \"$interfaceName\" in the path \"$outputFile\".\n");
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
}
