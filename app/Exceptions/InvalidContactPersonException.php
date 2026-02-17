<?php

declare(strict_types=1);

namespace App\Exceptions;

use DomainException;

class InvalidContactPersonException extends DomainException
{
    /**
     * Contact persons can only be added to business entities.
     */
    public static function notBusinessEntity(): self
    {
        return new self('Contact persons can only be added to business entities.');
    }

    /**
     * Only one primary contact is allowed per entity.
     */
    public static function multiplePrimaryContacts(): self
    {
        return new self('Only one primary contact is allowed per entity.');
    }

    /**
     * Cannot delete the only contact person.
     */
    public static function cannotDeleteOnlyContact(): self
    {
        return new self('Cannot delete the only contact person for a business entity.');
    }
}
