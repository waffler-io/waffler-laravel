<?php

/*
 * This file is part of Waffler.
 *
 * (c) Erick Johnson Almeida de Menezes <erickmenezes.dev@gmail.com>
 *
 * This source file is subject to the MIT licence that is bundled
 * with this source code in the file LICENCE.
 */

namespace Waffler\Laravel\Tests\Unit;

use Waffler\Attributes\Verbs\Get;
use Waffler\Laravel\Tests\TestCase;
use Waffler\Laravel\WafflerServiceProvider;

/**
 * Class ServiceProviderTest.
 *
 * @author ErickJMenezes <erickmenezes.dev@gmail.com>
 * @coversNothing
 */
class ServiceProviderTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        $app['config']->set('waffler.clients', [
            FooClient::class => ['base_uri' => 'localhost']
        ]);
        $app['config']->set('waffler.aliases', [
            FooClient::class => 'waffler.foo'
        ]);
        return parent::getPackageProviders($app);
    }

    public function test_it_must_load_the_service_provider(): void
    {
        self::assertTrue($this->app->providerIsLoaded(WafflerServiceProvider::class));
    }

    public function test_it_must_register_the_client_in_the_service_container(): void
    {
        self::assertInstanceOf(FooClient::class, $this->app->make(FooClient::class));
        self::assertInstanceOf(FooClient::class, $this->app->make('waffler.foo'));
    }
}

interface FooClient
{
    #[Get('/foo')]
    public function getFoo(): array;
}
