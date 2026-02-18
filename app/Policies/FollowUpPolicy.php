<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Customer;
use App\Models\FollowUp;
use App\Models\Lead;
use App\Models\User;

class FollowUpPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('manage follow ups');
    }

    public function view(User $user, FollowUp $followUp): bool
    {
        return $user->can('manage follow ups');
    }

    public function create(User $user, Lead|Customer $parent): bool
    {
        return $user->can('manage follow ups');
    }

    public function update(User $user, FollowUp $followUp): bool
    {
        return $user->can('manage follow ups');
    }

    public function delete(User $user, FollowUp $followUp): bool
    {
        return $user->can('manage follow ups');
    }

    public function complete(User $user, FollowUp $followUp): bool
    {
        return $user->can('manage follow ups') && $followUp->isPending();
    }
}
