<?php

declare(strict_types=1);

namespace Kipchak\Driver\Http\Tests\Compliance;

use GuzzleHttp\Psr7\HttpFactory;
use Kipchak\Driver\Http\Http;
use Kipchak\Driver\Http\Tests\Support\Clients;

/**
 * The PSR-17 factories as an API gets them: a 'psr17' client from kipchak.http, through Http::psr17().
 */
trait Factories
{
    private function factory(): HttpFactory
    {
        Clients::boot(['factories' => ['type' => 'psr17']]);

        return Http::psr17('factories');
    }

    protected function createUri($uri)
    {
        return $this->factory()->createUri($uri);
    }

    protected function createStream($content)
    {
        return $this->factory()->createStream($content);
    }
}
