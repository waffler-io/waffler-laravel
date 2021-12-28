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
    'global_options' => [
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
    ],

    /*
     * Generate code using your swagger or another openapi files.
     */
    'code_generation' => [

        /*
         * The base namespace where all interfaces will be generated.
         */
        'namespace' => 'App\\Clients',

        /*
         * Path to openapi files to generate the code.
         *
         * You can specify an inner namespace to better organization when you have multiple files.
         * Deeper Namespaces will create Deeper folders.
         */
        'openapi_files' => [
            // resource_path('swagger/your-swagger-file.json'),
            // resource_path('swagger/another-swagger-file.json') => [
            //     'namespace' => 'Extra\\Namespace',
            //     'namespace_options' => ['base_uri' => 'https://share-namespace-config.example.com/api/'],
            //     'ignore' => [
            //         'parameters' => [
            //              'header' => ['X-SOME-HEADER-NAME']
            //         ]
            //     ]
            // ],
        ]
    ],

    /*
     * Do not modify the lines below.
     */
    'auto_generated_clients' => [],
];
