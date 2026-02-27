<?php

namespace App\Providers;

use App\Models\ApprovalTask;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Policies\ApprovalTaskPolicy;
use App\Policies\ServiceRequestPolicy;
use App\Policies\UserPolicy;
use App\Policies\WorkflowDefinitionPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(ServiceRequest::class, ServiceRequestPolicy::class);
        Gate::policy(ApprovalTask::class, ApprovalTaskPolicy::class);

        Gate::define('manage_users', fn (User $user): bool => (new UserPolicy)->manage($user));
        Gate::define('manage_workflows', fn (User $user): bool => (new WorkflowDefinitionPolicy)->manage($user));
        Gate::define('approve_requests', fn (User $user): bool => $user->hasPermission('approve_requests'));
        Gate::define('create_requests', fn (User $user): bool => $user->hasPermission('create_requests'));
        Gate::define('view_reports', fn (User $user): bool => $user->hasPermission('view_reports'));
    }
}
