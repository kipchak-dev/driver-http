<?php

/**
 * Optional. Copy to config/kipchak.http.php to define named HTTP clients. Without this file, Http::get()
 * returns Laravel's HTTP client, as it always has. See https://kipchak.dev/docs/driver-http.
 *
 *   type      'laravel' (Laravel's client), 'psr18' (a PSR-18 client) or 'psr17' (the PSR-17 factories)
 *   base_url  prepended to relative URLs (laravel and psr18)
 *   options   Guzzle request options applied to every request: timeout, connect_timeout, headers, proxy, verify...
 */

use function Kipchak\Core\env;

return [
    // Http::get(): Laravel's client. Omit this entry and the default is Laravel's client anyway.
    'default' => ['type' => 'laravel'],

    // Http::get('ai') or Http::psr18('ai'): a PSR-18 client for SDKs that take one.
    // 'ai' => [
    //     'type' => 'psr18',
    //     'options' => ['timeout' => 120, 'connect_timeout' => 10],
    // ],

    // Http::get('psr17') or Http::psr17('psr17'): the PSR-17 factories.
    // 'psr17' => ['type' => 'psr17'],
];
