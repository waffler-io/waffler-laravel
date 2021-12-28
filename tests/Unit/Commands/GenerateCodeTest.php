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

use Waffler\Laravel\Tests\TestCase;

/**
 * Class GenerateCodeTest.
 *
 * @author ErickJMenezes <erickmenezes.dev@gmail.com>
 * @coversNothing
 */
class GenerateCodeTest extends TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        $app->make('config')->set('waffler.code_generation.openapi_files', [
            __DIR__.'/../../../vendor/waffler/opengen/tests/Fixtures/swagger-jsonplaceholder.json' => [
                'namespace' => 'JsonPlaceholder'
            ]
        ]);
    }

    public function testItMustGenerateClasses(): void
    {
        $this->artisan('waffler:install');
        $this->artisan('waffler:generate-code');
        $id = 'App\Clients\JsonPlaceholder\UserClientInterface';
        self::assertTrue($this->app->has($id));
        self::assertTrue(in_array($id, $this->app['config']->get('waffler.auto_generated_clients')));
    }
}
