<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    */

    'paths' => ['api/*', 'oauth/*'],  // 'oauth/*' covers Passport's routes like /oauth/token, /oauth/authorize, etc.

    'allowed_methods' => ['*'],

    'allowed_origins' => ['*'],  // In production, replace with your exact frontend URL, e.g., 'https://local-event-management-system-lems.vercel.app'

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,  // Set to true if using credentials (cookies/tokens with withCredentials in Axios); test carefully with Passport

];