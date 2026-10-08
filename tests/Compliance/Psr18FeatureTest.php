<?php

declare(strict_types=1);

namespace Kipchak\Driver\Http\Tests\Compliance;

use Http\Client\Tests\HttpFeatureTest;
use Kipchak\Driver\Http\Http;
use Kipchak\Driver\Http\Tests\Support\Clients;
use Psr\Http\Client\ClientInterface;

/**
 * The official PSR-18 feature tests (php-http/client-integration-tests), against a 'psr18' client from
 * kipchak.http. They call httpbin.org, so they need internet access.
 */
final class Psr18FeatureTest extends HttpFeatureTest
{
    protected function setUp(): void
    {
        $socket = @fsockopen('ssl://httpbin.org', 443, $errno, $error, 3);
        if ($socket === false) {
            $this->markTestSkipped('The PSR-18 feature tests need internet access to httpbin.org');
        }
        fclose($socket);
    }

    protected function createClient(): ClientInterface
    {
        Clients::boot(['psr' => ['type' => 'psr18']]);

        return Http::psr18('psr');
    }

    /**
     * Following redirects is an optional client feature, which PSR-18 does not specify. Guzzle's PSR-18
     * sendRequest() deliberately returns the 3xx response so that the calling library decides, and the
     * driver keeps that behaviour. This pins it in place of the suite's "follows redirects" check.
     */
    public function testRedirect(): void
    {
        $request = self::$messageFactory->createRequest('GET', 'https://httpbin.org/redirect/1');

        $response = $this->createClient()->sendRequest($request);

        $this->assertSame(302, $response->getStatusCode());
        $this->assertNotSame('', $response->getHeaderLine('Location'));
    }
}
