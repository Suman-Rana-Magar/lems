<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    */

    // Added 'storage/*' and 'lems/*' to ensure all your path variations are covered
    'paths' => ['api/*', 'oauth/*', 'storage/*', 'lems/*'],

    'allowed_methods' => ['*'],

    // CRITICAL: When supports_credentials is true, you MUST list the domain. 
    // Wildcard '*' will cause a CORS error in the browser.
    'allowed_origins' => [
        'https://local-event-management-system-lems.vercel.app',
        'http://localhost:3000'
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,
];
