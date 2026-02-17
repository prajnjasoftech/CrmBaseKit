<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\EntityType;
use App\Models\ContactPerson;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\User;

class ContactPersonPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('manage contact persons');
    }

    public function view(User $user, ContactPerson $contactPerson): bool
    {
        return $user->can('manage contact persons');
    }

    public function create(User $user, Lead|Customer $parent): bool
    {
        if (! $user->can('manage contact persons')) {
            return false;
        }

        return $parent->entity_type === EntityType::Business;
    }

    public function update(User $user, ContactPerson $contactPerson): bool
    {
        return $user->can('manage contact persons');
    }

    public function delete(User $user, ContactPerson $contactPerson): bool
    {
        return $user->can('manage contact persons');
    }
}
