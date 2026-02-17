<?php

declare(strict_types=1);

namespace App\Exceptions;

use DomainException;

class ImmutableFieldException extends DomainException
{
    /**
     * Create an exception for an immutable field.
     */
    public static function forField(string $field): self
    {
        return new self("The field '{$field}' cannot be modified after creation.");
    }
}
