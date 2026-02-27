<?php

namespace App\Policies;

use App\Models\ApprovalTask;
use App\Models\User;

class ApprovalTaskPolicy
{
    public function decide(User $user, ApprovalTask $task): bool
    {
        return $task->assigned_to === $user->id || $user->hasPermission('approve_requests');
    }
}
