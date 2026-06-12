<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Driver;

class DriverPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Driver $driver): bool
    {
        if ($user->isOperational() && $user->pool_id !== null) {
            return $driver->pool_id === $user->pool_id;
        }
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isOperational();
    }

    public function update(User $user, Driver $driver): bool
    {
        if (!$user->isOperational()) {
            return false;
        }
        if ($user->pool_id !== null) {
            return $driver->pool_id === $user->pool_id;
        }
        return true;
    }

    public function delete(User $user, Driver $driver): bool
    {
        if (!$user->isOperational()) {
            return false;
        }
        if ($user->pool_id !== null) {
            return $driver->pool_id === $user->pool_id;
        }
        return true;
    }
}
