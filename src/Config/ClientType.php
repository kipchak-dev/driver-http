<?php

declare(strict_types=1);

namespace Kipchak\Driver\Http\Config;

/**
 * What a named client in kipchak.http is.
 */
enum ClientType: string
{
    /** Laravel's HTTP client (Kipchak\Driver\Http\Library\Http), as Http::get() has always returned. */
    case Laravel = 'laravel';

    /** A PSR-18 client: Guzzle's GuzzleHttp\Client, which implements Psr\Http\Client\ClientInterface. */
    case Psr18 = 'psr18';

    /** The PSR-17 factories: Guzzle's GuzzleHttp\Psr7\HttpFactory, which implements all six PSR-17 interfaces. */
    case Psr17 = 'psr17';
}
