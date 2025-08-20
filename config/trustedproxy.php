<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Trusted Proxies
    |--------------------------------------------------------------------------
    |
    | Set trusted proxies for your application, to avoid infinite redirect loops
    | when running behind load balancers or reverse proxies (like Clever Cloud).
    |
    */

    'proxies' => '*',

    /*
    |--------------------------------------------------------------------------
    | Headers
    |--------------------------------------------------------------------------
    |
    | Headers that will be used to detect proxy information.
    |
    */

    'headers' => [
        \Symfony\Component\HttpFoundation\Request::HEADER_FORWARDED => null,
        \Symfony\Component\HttpFoundation\Request::HEADER_X_FORWARDED_FOR => 'X_FORWARDED_FOR',
        \Symfony\Component\HttpFoundation\Request::HEADER_X_FORWARDED_HOST => 'X_FORWARDED_HOST',
        \Symfony\Component\HttpFoundation\Request::HEADER_X_FORWARDED_PROTO => 'X_FORWARDED_PROTO',
        \Symfony\Component\HttpFoundation\Request::HEADER_X_FORWARDED_PORT => 'X_FORWARDED_PORT',
    ],

];
