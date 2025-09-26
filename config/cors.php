<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    */

    'paths' => [
        'api/*',
        'dashboard/*',
        'sanctum/csrf-cookie',
        '*',
    ],

    'allowed_methods' => ['*'],

    // Allow the frontend origin(s). Add your frontend domain(s) here.
    'allowed_origins' => [
        'http://localhost:8081',
        env('FRONTEND_URL') ?: null,
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    // Allow cookies/auth to be sent across origins
    'supports_credentials' => true,

    'max_age' => 0,
];
