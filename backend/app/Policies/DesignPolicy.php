<?php

namespace App\Policies;

use App\Models\Design;
use App\Models\User;

class DesignPolicy
{
    public function view(User $user, Design $design): bool
    {
        return $design->user_id === $user->id;
    }

    public function delete(User $user, Design $design): bool
    {
        return $design->user_id === $user->id;
    }
}
