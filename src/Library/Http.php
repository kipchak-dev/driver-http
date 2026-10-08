<?php

namespace Kipchak\Driver\Http\Library;

use Illuminate\Http\Client\Factory;

/**
 * Laravel's HTTP client. A named client from kipchak.http may carry a base URL, prepended to relative URLs of
 * every request it makes; its other options are applied with Factory::globalOptions().
 */
class Http extends Factory
{
    private ?string $baseUrl = null;

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
        $request = parent::newPendingRequest();

        return $this->baseUrl === null ? $request : $request->baseUrl($this->baseUrl);
    }
}
