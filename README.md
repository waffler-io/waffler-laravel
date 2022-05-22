# Waffler for Laravel

This package is a fancy wrapper for the Laravel Framework. You must know
the basics of the [waffler/waffler](https://github.com/waffler-io/waffler)
and [guzzlehttp/guzzle](https://github.com/guzzle/guzzle) packages.

## Installation

```shell
composer require waffler/waffler-laravel

php artisan waffler:install # publish the config file
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

### The `auto_generated_clients` array:
An array of auto generated classes. Do not modify it, the contents of this array is auto-generated when you run
the `waffler:generate-code` command.
```php
'auto_generated_clients' => [
    'App\Clients\FooBarClientInterface',
],
```

### The `code_generation` option:
This package also can generate the client interfaces if you have a swagger or another open-api spec file.
```php
'code_generation' => [
    'namespace' => 'App\\Clients',
    'openapi_files' => []
]
```

### The `code_generation.namespace` option:
The base namespace where all interfaces will be generated. 

*This namespace will be auto converted to a path relative to the `app/` folder.*
```php
'namespace' => 'App\\Clients',
```

### The `code_generation.openapi_files` array:
Path to openapi files to generate the code. You can specify just the path to the openapi file, or provide an array
of generation options. See example 1 and example 2 below.

```php
'openapi_files' => [
    // Example 1:
    resource_path('swagger/my-swagger-file.json'),
    
    // Example 2 with custom options:
    resource_path('swagger/my-swagger-file.json') => [
        'namespace' => 'MyCustomApi',
        'namespace_options' => [
            'base_uri' => env('MY_CUSTOM_API_BASE_URI')
        ],
        'ignore' => [
            'parameters' => [
                'header' => ['X-SOME-HEADER-NAME']
            ]
        ]
    ]
],
```

### The `code_generation.openapi_files.*.namespace` option:
The generated clients will be put inside `code_generation.namespace` plus this option value.
```php
'namespace' => 'App\\Clients',
'openapi_files' => [
    resource_path('swagger/my-swagger-file.json') => [
        'namespace' => 'MyCustomApi', // Will be converted to App\Clients\MyCustomApi
    ],
],
```

### The `code_generation.openapi_files.*.spec_type` option:
Indicates the specification file schema type. It can be either `openapi` or `swagger`.
The default value is `openapi`
```php
'namespace' => 'App\\Clients',
'openapi_files' => [
    resource_path('swagger/my-swagger-file.json') => [
        'namespace' => 'MyCustomApi',
        'spec_type' => 'swagger',
    ],
],
```

### The `code_generation.openapi_files.*.namespace_options` option:
The generated clients under the configured namespace will share this guzzle configurations.
This can be useful, for instance, when clients share the same options, like `base_uri`.
```php
'openapi_files' => [
    resource_path('swagger/my-swagger-file.json') => [
        'namespace' => 'MyCustomApi',
        'namespace_options' => [
            'base_uri' => env('MY_CUSTOM_API_BASE_URI')
        ],
    ],
],
```

### The `code_generation.openapi_files.*.ignore` option:
This option still under development, but here you can ignore the generation of some method parameters.
In the future, this will allow more configuration.

In the example below, a `HeaderParam` with the name of `Authorization` will not be included in
the generated method parameters.

```php
'openapi_files' => [
    resource_path('swagger/my-swagger-file.json') => [
        'ignore' => [
            'parameters' => [
                'header' => ['Authorization']
            ]
        ],
        'namespace_options' => [
            'headers' => [
                'Authorization' => env('MY_CLIENT_API_KEY')
            ],
        ],
    ],
],
```
Example code:

Before ignoring:
```php
<?php

use Waffler\Attributes\Request\HeaderParam;

interface MyClientInterface
{
    public function users(#[HeaderParam('Authorization')] string $authorization): array;
}
```

After ignoring:
```php
<?php

use Waffler\Attributes\Request\HeaderParam;

interface MyClientInterface
{
    public function users(): array;
}
```

Ignorable parameter types are:
- header
- path
- query
- body
- formData

### The `code_generation.openapi_files.*.auto_bind` option:
Automatic register the generated interfaces in the service container.
Default value is `true`.
```php
'openapi_files' => [
    resource_path('swagger/my-swagger-file.json') => [
        'auto_bind' => false
    ],
],
```

## Contributions:
Work in progress.
