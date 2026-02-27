<?php

namespace Tests\Support;

use App\Models\Department;
use App\Models\Permission;
use App\Models\RequestType;
use App\Models\Role;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Models\WorkflowDefinition;
use Illuminate\Support\Facades\Hash;

trait CreatesFlowDeskData
{
    protected function createBaseUsersAndRoles(): array
    {
        $department = Department::create(['name' => 'IT']);

        $permissions = collect([
            ['name' => 'Manage Users', 'slug' => 'manage_users'],
            ['name' => 'Manage Workflows', 'slug' => 'manage_workflows'],
            ['name' => 'Approve Requests', 'slug' => 'approve_requests'],
            ['name' => 'Create Requests', 'slug' => 'create_requests'],
            ['name' => 'View Reports', 'slug' => 'view_reports'],
        ])->map(fn (array $permission) => Permission::create($permission));

        $roles = collect([
            ['name' => 'Admin', 'slug' => 'admin', 'permissions' => $permissions->pluck('id')->all()],
            ['name' => 'Approver', 'slug' => 'approver', 'permissions' => $permissions->where('slug', 'approve_requests')->pluck('id')->all()],
            ['name' => 'Requester', 'slug' => 'requester', 'permissions' => $permissions->where('slug', 'create_requests')->pluck('id')->all()],
            ['name' => 'Process Owner', 'slug' => 'process-owner', 'permissions' => $permissions->whereIn('slug', ['manage_workflows', 'approve_requests', 'view_reports'])->pluck('id')->all()],
        ])->map(function (array $roleData): Role {
            $role = Role::create([
                'name' => $roleData['name'],
                'slug' => $roleData['slug'],
            ]);
            $role->permissions()->sync($roleData['permissions']);

            return $role;
        });

        $requestType = RequestType::create([
            'name' => 'IT Access',
            'description' => 'Test type',
        ]);

        $requester = User::create([
            'name' => 'Requester',
            'email' => 'requester@test.local',
            'password' => Hash::make('Password123!'),
            'department_id' => $department->id,
        ]);
        $requester->roles()->sync([$roles->firstWhere('slug', 'requester')->id]);

        $requesterTwo = User::create([
            'name' => 'Requester Two',
            'email' => 'requester2@test.local',
            'password' => Hash::make('Password123!'),
            'department_id' => $department->id,
        ]);
        $requesterTwo->roles()->sync([$roles->firstWhere('slug', 'requester')->id]);

        $approverA = User::create([
            'name' => 'Approver A',
            'email' => 'approver.a@test.local',
            'password' => Hash::make('Password123!'),
            'department_id' => $department->id,
        ]);
        $approverA->roles()->sync([$roles->firstWhere('slug', 'approver')->id]);

        $approverB = User::create([
            'name' => 'Approver B',
            'email' => 'approver.b@test.local',
            'password' => Hash::make('Password123!'),
            'department_id' => $department->id,
        ]);
        $approverB->roles()->sync([$roles->firstWhere('slug', 'approver')->id]);

        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@test.local',
            'password' => Hash::make('Password123!'),
            'department_id' => $department->id,
        ]);
        $admin->roles()->sync([$roles->firstWhere('slug', 'admin')->id]);

        return compact('department', 'requestType', 'requester', 'requesterTwo', 'approverA', 'approverB', 'admin');
    }

    /**
     * @param  array<int, array<string, mixed>>  $steps
     */
    protected function createWorkflow(RequestType $requestType, array $steps): WorkflowDefinition
    {
        return WorkflowDefinition::create([
            'request_type_id' => $requestType->id,
            'name' => 'Workflow v1',
            'version' => 1,
            'is_active' => true,
            'definition_json' => [
                'steps' => $steps,
            ],
        ]);
    }

    protected function createDraftRequest(User $requester, RequestType $requestType, Department $department): ServiceRequest
    {
        return ServiceRequest::create([
            'title' => 'Need access',
            'description' => 'Request for testing',
            'type_id' => $requestType->id,
            'department_id' => $department->id,
            'priority' => 'medium',
            'status' => 'draft',
            'created_by' => $requester->id,
        ]);
    }
}
