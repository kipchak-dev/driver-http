<?php

declare(strict_types=1);

namespace Kipchak\Driver\Http\Tests\Compliance;

use Interop\Http\Factory\UploadedFileFactoryTestCase;

/**
 * The official PSR-17 suite (http-interop/http-factory-tests) for the UploadedFile factory.
 */
final class Psr17UploadedFileFactoryTest extends UploadedFileFactoryTestCase
{
    use Factories;

    protected function createUploadedFileFactory()
    {
        return $this->factory();
    }
}
