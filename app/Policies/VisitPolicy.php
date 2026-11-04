<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Visit;

class VisitPolicy
{
    public function create(User $user): bool
    {
        return $user->is_admin;
    }

    public function update(User $user, Visit $visit): bool
    {
        return $user->is_admin && $visit->user_id === $user->id;
    }

    public function delete(User $user, Visit $visit): bool
    {
        return $this->update($user, $visit);
    }

    public function preview(User $user, Visit $visit): bool
    {
        return $this->update($user, $visit);
    }
}
