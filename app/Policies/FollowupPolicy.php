<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Followup;

class FollowupPolicy
{
    public function viewAny(User $user): bool { return true; }
    public function view(User $user, Followup $record): bool { return true; }
    public function create(User $user): bool { return true; }
    public function update(User $user, Followup $record): bool { return true; }
    public function delete(User $user, Followup $record): bool { return true; }
    public function restore(User $user, Followup $record): bool { return true; }
    public function forceDelete(User $user, Followup $record): bool { return true; }
}
