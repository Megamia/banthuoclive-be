<?php

return [
    'paths' => ['api/*', 'apiUser/*', 'sanctum/csrf-cookie'],

    'allowed_origins' => [
        'https://banthuoclive-fe.vercel.app',
    ],

    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'allowed_methods' => ['*'],
    // 'allowed_origins' =>['*'],
    'exposed_headers' => [],
    'max_age' => 0,

    // 'supports_credentials' => app()->environment('local') ? false : true,

    //LOCAL
    // 'supports_credentials' => false,
    //LOCAL

    //DEPLOY
    'supports_credentials' => true,
    //DEPLOY
];
