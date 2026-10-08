<?php

declare(strict_types=1);

namespace Kipchak\Driver\Http\Tests\Compliance;

use Interop\Http\Factory\RequestFactoryTestCase;

/**
 * The official PSR-17 suite (http-interop/http-factory-tests) for the Request factory.
 */
final class Psr17RequestFactoryTest extends RequestFactoryTestCase
{
    use Factories;

    protected function createRequestFactory()
    {
        return $this->factory();
    }
}
