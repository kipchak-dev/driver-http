<?php

declare(strict_types=1);

namespace Kipchak\Driver\Http\Exceptions;

/**
 * Raised while the API boots, so a mistake in kipchak.http stops the API rather than failing on a request.
 */
final class InvalidConfiguration extends \InvalidArgumentException
{
    public static function at(string $path, string $problem): self
    {
        return new self("kipchak.http: {$path} {$problem}");
    }
}
