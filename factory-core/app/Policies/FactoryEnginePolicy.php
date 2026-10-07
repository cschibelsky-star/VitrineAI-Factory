<?php

namespace App\Policies;

use App\Models\Engine;
use App\Models\User;

class FactoryEnginePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Engine $engine): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Engine $engine): bool
    {
        return true;
    }

    public function delete(User $user, Engine $engine): bool
    {
        return true;
    }
}
