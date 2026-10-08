<?php

declare(strict_types=1);

namespace Kipchak\Driver\Http\Tests\Compliance;

use Interop\Http\Factory\ServerRequestFactoryTestCase;

/**
 * The official PSR-17 suite (http-interop/http-factory-tests) for the ServerRequest factory.
 */
final class Psr17ServerRequestFactoryTest extends ServerRequestFactoryTestCase
{
    use Factories;

    protected function createServerRequestFactory()
    {
        return $this->factory();
    }
}
