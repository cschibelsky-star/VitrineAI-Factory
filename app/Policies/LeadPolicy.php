<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Lead;

class LeadPolicy
{
    public function viewAny(User $user): bool { return true; }
    public function view(User $user, Lead $record): bool { return true; }
    public function create(User $user): bool { return true; }
    public function update(User $user, Lead $record): bool { return true; }
    public function delete(User $user, Lead $record): bool { return true; }
    public function restore(User $user, Lead $record): bool { return true; }
    public function forceDelete(User $user, Lead $record): bool { return true; }
}
