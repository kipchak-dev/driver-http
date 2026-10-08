<?php

declare(strict_types=1);

namespace Kipchak\Driver\Http\Tests\Compliance;

use Interop\Http\Factory\ResponseFactoryTestCase;

/**
 * The official PSR-17 suite (http-interop/http-factory-tests) for the Response factory.
 */
final class Psr17ResponseFactoryTest extends ResponseFactoryTestCase
{
    use Factories;

    protected function createResponseFactory()
    {
        return $this->factory();
    }
}
