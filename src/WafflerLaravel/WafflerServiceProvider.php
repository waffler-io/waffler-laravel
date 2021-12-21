<?php

namespace Waffler\Laravel;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Waffler\Client\Factory;

/**
 * Class WafflerServiceProvider.
 *
 * @author ErickJMenezes <erickmenezes.dev@gmail.com>
 */
class WafflerServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function register()
    {
        $this->registerClients();
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/config.php' => $this->app->configPath('waffler.php')
        ]);
    }

    public function provides()
    {
        return array_keys($this->getClientsToLoad());
    }

    private function registerClients(): void
    {
        foreach ($this->getClientsToLoad() as $clientInterface => $options) {
            if (!empty($options)) {
                $this->app->singleton(
                    $clientInterface,
                    fn() => Factory::make($clientInterface, $options)
                );
            } else {
                $this->app->bind(
                    $clientInterface,
                    fn($app, $args) => Factory::make($clientInterface, $args[0] ?? $options)
                );
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
        $clients = Config::get('waffler.clients.'.$this->app->environment(), []);

        /** @var array<class-string, array<string, mixed>> $normalizedArray */
        $normalizedArray = [];

        foreach ($clients as $classStringOrIndex => $classStringOrOptions) {
            if (is_string($classStringOrIndex)) {
                $normalizedArray[$classStringOrIndex] = $classStringOrOptions;
            } else {
                $normalizedArray[$classStringOrOptions] = [];
            }
        }

        return $normalizedArray;
    }
}