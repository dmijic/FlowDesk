<?php

namespace App\Policies;

use App\Models\ServiceRequest;
use App\Models\User;

class ServiceRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('create_requests') || $user->hasPermission('approve_requests') || $user->hasPermission('manage_workflows');
    }

    public function view(User $user, ServiceRequest $request): bool
    {
        if ($user->id === $request->created_by) {
            return true;
        }

        if ($user->hasPermission('manage_workflows') || $user->hasPermission('view_reports')) {
            return true;
        }

        return $request->approvalTasks()->where('assigned_to', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('create_requests');
    }

    public function submit(User $user, ServiceRequest $request): bool
    {
        return $user->id === $request->created_by || $user->hasPermission('manage_workflows');
    }
}
