<?php

namespace App\Policies;

use App\Models\Opportunity;
use App\Models\User;

class OpportunityPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isGM() || $user->isManager() || $user->isSales();
    }

    public function view(User $user, Opportunity $opportunity): bool
    {
        if ($user->isGM() || $user->isManager()) {
            return true;
        }

        if ($user->isSales() && $opportunity->sales_id === $user->id) {
            return true;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isSales();
    }

    public function update(User $user, Opportunity $opportunity): bool
    {
        if ($user->isGM()) {
            return true;
        }

        if ($user->isManager()) {
            $owner = $opportunity->sales;
            return $owner && $owner->manager_id === $user->id;
        }

        if ($user->isSales() && $opportunity->sales_id === $user->id) {
            return true;
        }

        return false;
    }

    public function delete(User $user, Opportunity $opportunity): bool
    {
        return $user->isGM();
    }
}
