<?php

/*
 * This file is part of Waffler.
 *
 * (c) Erick Johnson Almeida de Menezes <erickmenezes.dev@gmail.com>
 *
 * This source file is subject to the MIT licence that is bundled
 * with this source code in the file LICENCE.
 */

namespace Waffler\Laravel\Tests\Unit\Commands;

use Symfony\Component\Filesystem\Filesystem;
use Waffler\Laravel\Tests\TestCase;

/**
 * Class GenerateCodeTest.
 *
 * @author ErickJMenezes <erickmenezes.dev@gmail.com>
 * @coversNothing
 */
class GenerateCodeTest extends TestCase
{
    private const OUTPUT_PATH = 'Clients/JsonPlaceholder';

    protected function setUp(): void
    {
        parent::setUp();

        $filesystem = new Filesystem();
        if ($filesystem->exists(self::OUTPUT_PATH)) {
            $filesystem->remove(self::OUTPUT_PATH);
        }

        $this->artisan('waffler:install');
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('waffler.code_generation.openapi_files', [
            __DIR__.'/../../../vendor/waffler/opengen/tests/Fixtures/swagger-jsonplaceholder.json' => [
                'namespace' => 'JsonPlaceholder',
            ]
        ]);
    }

    public function testItMustGenerateInterfaces(): void
    {
        $returnCode = $this->artisan('waffler:generate-code', [
            '--allow-continue' => true
        ]);
        $returnCode->assertSuccessful();
        self::assertFileExists($this->app->path(self::OUTPUT_PATH.'/UserClientInterface.php'));
    }
}
