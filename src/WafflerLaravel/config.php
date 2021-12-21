<?php

return [
    /**
     * Put here your client specifications, and it will be auto registered in the service container.
     */
    'clients' => [
        /**
         * These clients will be loaded if the environment is set to local.
         */
        'local' => [
            // ExampleClient::class => ['example_guzzlehttp_option' => 'example_value'],
            // ExampleClientWithNoOptions::class,
        ],

        /**
         * These clients will be loaded if the environment is set to production.
         */
        'production' => [],
    ]
];