<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Lead;
use App\Models\User;

class LeadPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view leads');
    }

    public function view(User $user, Lead $lead): bool
    {
        return $user->can('view leads');
    }

    public function create(User $user): bool
    {
        return $user->can('create leads');
    }

    public function update(User $user, Lead $lead): bool
    {
        return $user->can('edit leads');
    }

    public function delete(User $user, Lead $lead): bool
    {
        return $user->can('delete leads');
    }

    public function convert(User $user, Lead $lead): bool
    {
        return $user->can('convert leads') && $lead->canBeConverted();
    }

    public function restore(User $user, Lead $lead): bool
    {
        return $user->can('delete leads');
    }

    public function forceDelete(User $user, Lead $lead): bool
    {
        return $user->hasRole('super-admin');
    }
}
