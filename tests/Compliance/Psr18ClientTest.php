<?php

declare(strict_types=1);

namespace Kipchak\Driver\Http\Tests\Compliance;

use Http\Client\Tests\HttpClientTest;
use Kipchak\Driver\Http\Http;
use Kipchak\Driver\Http\Tests\Support\Clients;
use Psr\Http\Client\ClientInterface;

/**
 * The official PSR-18 suite (php-http/client-integration-tests), against a 'psr18' client from kipchak.http.
 * Needs its test server: vendor/bin/http_test_server (127.0.0.1:10000).
 */
final class Psr18ClientTest extends HttpClientTest
{
    protected function setUp(): void
    {
        $socket = @fsockopen('127.0.0.1', 10000, $errno, $error, 1);
        if ($socket === false) {
            $this->markTestSkipped('Start the PSR-18 test server first: vendor/bin/http_test_server');
        }
        fclose($socket);

        parent::setUp();
    }

    protected function createHttpAdapter(): ClientInterface
    {
        Clients::boot(['psr' => ['type' => 'psr18']]);

        return Http::psr18('psr');
    }
}
