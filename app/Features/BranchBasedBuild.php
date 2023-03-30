<?php

namespace App\Features;

use App\Models\User;

class BranchBasedBuild
{
    public function resolve(User $user) : bool
    {
        return match (true)
        {
            $user->isWorkspaceAdmin() => false,
            $user->isSuperAdmin() => false,
            default => false,
        };
    }
}
