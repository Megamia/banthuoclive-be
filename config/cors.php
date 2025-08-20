<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_origins' => [
        'https://banthuoclive-fe.vercel.app',
    ],

    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'allowed_methods' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,
];
