<?php

declare(strict_types=1);

namespace Kipchak\Driver\Http\Tests\Compliance;

use Interop\Http\Factory\UriFactoryTestCase;

/**
 * The official PSR-17 suite (http-interop/http-factory-tests) for the Uri factory.
 */
final class Psr17UriFactoryTest extends UriFactoryTestCase
{
    use Factories;

    protected function createUriFactory()
    {
        return $this->factory();
    }
}
