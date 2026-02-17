<?php

declare(strict_types=1);

namespace App\Enums;

enum EntityType: string
{
    case Individual = 'individual';
    case Business = 'business';

    /**
     * Get all entity types as an array for dropdowns.
     *
     * @return array<string, string>
     */
    public static function toArray(): array
    {
        return [
            self::Individual->value => 'Individual',
            self::Business->value => 'Business',
        ];
    }

    /**
     * Get the label for display.
     */
    public function label(): string
    {
        return match ($this) {
            self::Individual => 'Individual',
            self::Business => 'Business',
        };
    }
}
