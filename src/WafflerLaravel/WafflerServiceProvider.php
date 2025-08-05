<?php

declare(strict_types=1);

/*
 * This file is part of Waffler.
 *
 * (c) Erick Johnson Almeida de Menezes <erickmenezes.dev@gmail.com>
 *
 * This source file is subject to the MIT licence that is bundled
 * with this source code in the file LICENCE.
 */

namespace Waffler\Laravel;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Override;
use Waffler\Laravel\Commands\WafflerCacheCommand;
use Waffler\Laravel\Commands\WafflerClearCommand;
use Waffler\Laravel\DirectoryResolvers\DefaultDirectoryResolver;
use Waffler\Laravel\DirectoryResolvers\DirectoryResolverInterface;
use Waffler\Waffler\Client\Factory;

/**
 * Class WafflerServiceProvider.
 *
 * @author ErickJMenezes <erickmenezes.dev@gmail.com>
 */
class WafflerServiceProvider extends ServiceProvider
{
    #[Override]
    public function register(): void
    {
        $this->mergeConfigFrom(self::getPackageConfigPath(), 'waffler');
        $this->app->bind(Factory::class, fn () => Factory::default());
        $this->app->bind(DirectoryResolverInterface::class, DefaultDirectoryResolver::class);
        $this->app->bind(ClientListRetriever::class);
        $this->app->alias(DirectoryResolverInterface::class, 'waffler.directory-resolver');
        $this->app->alias(ClientListRetriever::class, 'waffler.client-list-retriever');
        $this->app->alias(Factory::class, 'waffler.factory');
        $this->registerClients();
    }

    public function boot(): void
    {
        if (!$this->app->runningInConsole()) {
            return;
        }
        $this->publishes([
            self::getPackageConfigPath() => config_path('waffler.php'),
        ], 'waffler-config');
        $this->registerCommands();
    }

    private function registerClients(): void
    {
        $sharedConfig = config('waffler.global_options', []);
        $clientListRetriever = $this->app->make(ClientListRetriever::class);

        foreach ($clientListRetriever->clients as $clientInterface => $options) {
            $factory = fn (Application $app, array $args) => $app
                ->make(Factory::class)
                ->make(
                    $clientInterface,
                    array_merge_recursive(
                        $sharedConfig,
                        $options,
                        $args[0] ?? [],
                    ),
                );

            $this->app->bind(
                $clientInterface,
                $factory,
                in_array($clientInterface, config('waffler.singletons', []), true)
            );

            if ($alias = config('waffler.aliases.'.$clientInterface, false)) {
                $this->app->alias($clientInterface, $alias);
            }
        }
    }

    /**
     * @return non-empty-string
     * @author ErickJMenezes <erickmenezes.dev@gmail.com>
     */
    private static function getPackageConfigPath(): string
    {
        return __DIR__.'/../config/waffler.php';
    }

    private function registerCommands(): void
    {
        $this->optimizes(
            'waffler:cache',
            'waffler:clear',
        );
        $this->registerWafflerOptimizeCommand();
        $this->registerWafflerClearCommand();
        $this->commands([
            WafflerCacheCommand::class,
            WafflerClearCommand::class,
        ]);
    }

    private function registerWafflerOptimizeCommand(): void
    {
        $this->app->bind(WafflerCacheCommand::class, function (Application $app) {
            return new WafflerCacheCommand(
                $app['waffler.client-list-retriever'],
                $app['waffler.factory'],
            );
        });
    }

    private function registerWafflerClearCommand(): void
    {
        $this->app->bind(WafflerClearCommand::class, function (Application $app) {
            return new WafflerClearCommand($app['waffler.directory-resolver']);
        });
    }
}
