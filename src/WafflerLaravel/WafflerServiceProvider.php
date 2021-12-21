<?php

/*
 * This file is part of Waffler.
 *
 * (c) Erick Johnson Almeida de Menezes <erickmenezes.dev@gmail.com>
 *
 * This source file is subject to the MIT licence that is bundled
 * with this source code in the file LICENCE.
 */

namespace Waffler\Laravel;

use Illuminate\Support\ServiceProvider;
use RuntimeException;
use Waffler\Client\Factory;

/**
 * Class WafflerServiceProvider.
 *
 * @author ErickJMenezes <erickmenezes.dev@gmail.com>
 */
class WafflerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/waffler.php', 'waffler');

        $this->registerClients();
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/waffler.php' => config_path('waffler.php'),
            ], 'waffler');
        }
    }

    private function registerClients(): void
    {
        $sharedConfig = config('waffler.shared_config', []);

        foreach ($this->getClientsToLoad() as $clientInterface => $options) {
            $factory = fn ($app, $args) => Factory::make(
                $clientInterface,
                array_merge_recursive($sharedConfig, $options, $args[0] ?? [])
            );

            $this->app->bind(
                $clientInterface,
                $factory,
                !empty($options)
            );

            if ($alias = config('waffler.aliases.'.$clientInterface, false)) {
                $this->app->alias($clientInterface, $alias);
            }
        }
    }

    /**
     * Retrieves the clients to be registered.
     *
     * @return array<class-string, array<string, mixed>>
     * @author ErickJMenezes <erickmenezes.dev@gmail.com>
     */
    private function getClientsToLoad(): array
    {
        /** @var array<class-string|int, class-string|array<string, mixed>> $clients */
        $clients = config('waffler.clients', []);

        /** @var array<class-string, array<string, mixed>> $normalizedArray */
        $normalizedArray = [];

        foreach ($clients as $classStringOrIndex => $classStringOrOptions) {
            if (is_string($classStringOrIndex) && is_array($classStringOrOptions)) {
                $normalizedArray[$classStringOrIndex] = $classStringOrOptions;
            } elseif (is_string($classStringOrOptions)) {
                $normalizedArray[$classStringOrOptions] = [];
            } else {
                throw new RuntimeException("The waffler config file is invalid. The type of 'clients' must match array<class-string, array<string, mixed>>|array<class-string>");
            }
        }

        return $normalizedArray;
    }
}
