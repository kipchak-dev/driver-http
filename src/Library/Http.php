<?php

namespace Kipchak\Driver\Http\Library;

use GuzzleHttp\Utils;
use Illuminate\Http\Client\Factory;

/**
 * Laravel's HTTP client. A named client from kipchak.http may carry a base URL, prepended to relative URLs of
 * every request it makes; its other options are applied with Factory::globalOptions().
 *
 * Every request from one instance shares one Guzzle handler, so curl keeps connections open and reuses them
 * (keep-alive and TLS sessions included) for as long as the instance lives, which in a FrankenPHP worker is the
 * life of the worker. Laravel on its own builds a new handler, and so a new connection, for every request; under
 * load in a worker that leaves thousands of sockets in TIME_WAIT and connections start to fail.
 */
class Http extends Factory
{
    private ?string $baseUrl = null;

    /** @var callable|null */
    private $handler = null;

    /**
     * Sets the base URL for every request this client makes. Called once, when the client is built.
     */
    public function withBaseUrl(string $baseUrl): static
    {
        $this->baseUrl = $baseUrl;

        return $this;
    }

    protected function newPendingRequest()
    {
        // A handler set on the request itself (setHandler()) still replaces this one.
        $request = parent::newPendingRequest()->setHandler($this->handler ??= Utils::chooseHandler());

        return $this->baseUrl === null ? $request : $request->baseUrl($this->baseUrl);
    }
}
