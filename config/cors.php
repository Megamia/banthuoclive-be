<?php

return [
    'paths' => ['api/*', 'apiUser/*', 'sanctum/csrf-cookie'],

    'allowed_origins' => [
        'https://www.luudanhdat.id.vn',
    ],

    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'allowed_methods' => ['*'],
    // 'allowed_origins' =>['*'],
    'exposed_headers' => [],
    'max_age' => 0,

    //LOCAL
    // 'supports_credentials' => false,
    //LOCAL

    //DEPLOY
    'supports_credentials' => true,
    //DEPLOY
];
