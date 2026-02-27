<?php

namespace App\Policies;

use App\Models\User;

class WorkflowDefinitionPolicy
{
    public function manage(User $user): bool
    {
        return $user->hasPermission('manage_workflows');
    }
}
