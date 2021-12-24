<?php

namespace Waffler\Laravel\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Waffler\OpenGen\Generator;

/**
 * Class GenerateCode.
 *
 * @author ErickJMenezes <erickmenezes.dev@gmail.com>
 */
class GenerateCode extends Command
{
    protected $signature = 'waffler:generate-code';

     protected $description = 'Generate code using OpenAPI files.';

    public function handle(): bool
    {
        if (! config()->has('waffler')) {
            $this->error('Waffler config file is not published. Use waffler:install command.');
            return false;
        }

        $generator = new Generator();
        $baseNamespace = config('waffler.code_generation.namespace');

        foreach (config('waffler.code_generation.openapi_files') as $pathToFile => $extraNamespace) {
            if (is_int($pathToFile)) {
                $pathToFile = $extraNamespace;
                $extraNamespace = $baseNamespace;
            } else {
                $extraNamespace = "$baseNamespace\\$extraNamespace";
            }

            $outputDir = $this->convertNamespaceToPath($extraNamespace);
            $generator->fromOpenApiFile($pathToFile, $outputDir, $extraNamespace);
        }

        return true;
    }

    /**
     * @param string $namespace
     *
     * @return non-empty-string
     * @author ErickJMenezes <erickmenezes.dev@gmail.com>
     */
    private function convertNamespaceToPath(string $namespace): string
    {
        return app_path(str_replace('\\', '/', substr($namespace, 4)));
    }
}