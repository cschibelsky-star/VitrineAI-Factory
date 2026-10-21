<?php

namespace App\Policies;

use App\Models\Engine;
use App\Models\User;

class FactoryEnginePolicy
{
    private function authorized(User $user): bool
    {
        $email = config('factory_access.admin_email');
        return is_string($email) && trim($email) !== ''
            && is_string($user->email)
            && hash_equals(strtolower(trim($email)), strtolower(trim($user->email)));
    }

    public function viewAny(User $user): bool
    {
        return $this->authorized($user);
    }

    public function view(User $user, Engine $engine): bool
    {
        return $this->authorized($user);
    }

    public function create(User $user): bool
    {
        return $this->authorized($user);
    }

    public function update(User $user, Engine $engine): bool
    {
        return $this->authorized($user);
    }

    public function delete(User $user, Engine $engine): bool
    {
        return $this->authorized($user);
    }
}
