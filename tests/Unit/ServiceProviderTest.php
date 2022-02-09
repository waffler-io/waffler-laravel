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

use Waffler\Laravel\Tests\TestCase;
use Waffler\Laravel\WafflerServiceProvider;
use Waffler\Laravel\Tests\Fixtures\Interfaces\FooClientInterface;

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
            FooClientInterface::class => ['base_uri' => 'localhost']
        ]);
        $app['config']->set('waffler.aliases', [
            FooClientInterface::class => 'waffler.foo'
        ]);
        return parent::getPackageProviders($app);
    }

    public function test_it_must_load_the_service_provider(): void
    {
        self::assertTrue($this->app->providerIsLoaded(WafflerServiceProvider::class));
    }

    public function test_it_must_register_the_client_in_the_service_container(): void
    {
        self::assertInstanceOf(FooClientInterface::class, $this->app->make(FooClientInterface::class));
        self::assertInstanceOf(FooClientInterface::class, $this->app->make('waffler.foo'));
    }
}
