<?php

declare(strict_types=1);

namespace Kipchak\Driver\Http\Tests\Compliance;

use Interop\Http\Factory\StreamFactoryTestCase;

/**
 * The official PSR-17 suite (http-interop/http-factory-tests) for the Stream factory.
 */
final class Psr17StreamFactoryTest extends StreamFactoryTestCase
{
    use Factories;

    protected function createStreamFactory()
    {
        return $this->factory();
    }
}
