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

use Illuminate\Contracts\Container\BindingResolutionException;
use Override;
use PHPUnit\Framework\Attributes\Test;
use Waffler\Laravel\Commands\WafflerClearCommand;
use Waffler\Laravel\Commands\WafflerInstallCommand;
use Waffler\Laravel\Commands\WafflerCacheCommand;
use Waffler\Laravel\Tests\TestCase;
use Waffler\Laravel\WafflerServiceProvider;
use Waffler\Laravel\Tests\Fixtures\Interfaces\FooClientInterface;

/**
 * Class ServiceProviderTest.
 *
 * @author ErickJMenezes <erickmenezes.dev@gmail.com>
 */
class ServiceProviderTest extends TestCase
{
    #[Override]
    protected function getPackageProviders($app): array
    {
        $app['config']->set('waffler.clients', [
            FooClientInterface::class => ['base_uri' => 'localhost']
        ]);
        $app['config']->set('waffler.aliases', [
            FooClientInterface::class => 'waffler.foo'
        ]);
        $app['config']->set('waffler.singletons', [
            FooClientInterface::class,
        ]);
        return parent::getPackageProviders($app);
    }

    #[Test]
    public function it_must_load_the_service_provider(): void
    {
        self::assertTrue($this->app->providerIsLoaded(WafflerServiceProvider::class));
    }

    /**
     * @throws BindingResolutionException
     */
    #[Test]
    public function it_must_register_the_client_in_the_service_container(): void
    {
        self::assertInstanceOf(FooClientInterface::class, $this->app->make(FooClientInterface::class));
        self::assertInstanceOf(FooClientInterface::class, $this->app->make('waffler.foo'));
    }

    #[Test]
    public function it_must_register_the_client_as_a_singleton(): void
    {
        self::assertSame($this->app->make(FooClientInterface::class), $this->app->make(FooClientInterface::class));
        self::assertSame($this->app->make(FooClientInterface::class), $this->app->make('waffler.foo'));
    }

    #[Test]
    public function it_must_register_the_commands(): void
    {
        self::assertTrue($this->app->runningInConsole());
        self::assertTrue($this->app->has(WafflerClearCommand::class));
        self::assertTrue($this->app->has(WafflerCacheCommand::class));
    }
}
