# Waffler for Laravel

This package is a fancy wrapper for the Laravel Framework. You must know
the basics of the [waffler/waffler](https://github.com/waffler-io/waffler)
and [guzzlehttp/guzzle](https://github.com/guzzle/guzzle) packages.

## Installation

```shell
composer require waffler/waffler-laravel

php artisan vendor:publish --tag="waffler-config"
```

## How to configure:
This package exposes a `waffler.php` config file to register your
client interfaces into the application [service container](https://laravel.com/docs/8.x/container).

### The `clients` array:
Register your clients in the service container.
```php
'clients' => [
    App\Clients\MyClientInterface::class => [/* GuzzleHttp options */],
],
```

### The `aliases` array:
Give an alias to your clients.
```php
'aliases' => [
    App\Clients\MyClientInterface::class => 'my-custom-alias',
],
```

### The `global_options` array:
An array of guzzle http options to be used in all client instances.
```php
'global_options' => [/* GuzzleHttp options */],
```

### The `singletons` array:
An array of clients to be registered as singletons.
```php
'singletons' => [
    App\Clients\MyClientInterface::class,
],
```

## Contributions:
Work in progress.
