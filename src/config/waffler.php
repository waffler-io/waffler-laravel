<?php

/*
 * This file is part of Waffler.
 *
 * (c) Erick Johnson Almeida de Menezes <erickmenezes.dev@gmail.com>
 *
 * This source file is subject to the MIT licence that is bundled
 * with this source code in the file LICENCE.
 */

return [
    /*
     * Put here your client specifications, and it will be auto registered in the service container.
     */
    'clients' => [
        // 'App\\Clients\\MyClient' => ['base_uri' => env('EXAMPLE_CLIENT_BASE_URI')],
        // 'App\\Clients\\ClientWithoutConfiguration',
    ],

    /*
     * Register an alias for your clients.
     */
    'aliases' => [
        // 'App\\Clients\\MyClient' => 'example-alias'
    ],

    /*
     * Shared configuration to be used in every client.
     */
    'shared_config' => [
        // 'headers' => ['X-Foo-Bar' => 'Baz']
    ],

    /*
     * Clients that must be bound as singletons.
     *
     * This type of binding is recommended when you don't need to provide guzzle http options in runtime. Singleton
     * bindings is always faster to resolve from the container than regular bindings after the first instantiation.
     */
    'singletons' => [
        // 'App\\Clients\\MyClient',
    ]
];
