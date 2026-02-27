<?php

namespace Database\Seeders;

use App\Enums\ApprovalTaskStatus;
use App\Enums\Priority;
use App\Enums\RequestStatus;
use App\Models\ApprovalTask;
use App\Models\Department;
use App\Models\Permission;
use App\Models\RequestType;
use App\Models\Role;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Models\WorkflowDefinition;
use App\Services\ApprovalService;
use App\Services\AuditLogger;
use App\Services\WorkflowEngine;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $departments = $this->seedDepartments();
        $roles = $this->seedRbac();

        [$admin, $processOwners, $approvers, $requesters] = $this->seedUsers($departments, $roles);
        $requestTypes = $this->seedRequestTypes();

        $workflowTypes = $requestTypes->take(3);
        $this->seedWorkflows($workflowTypes, $approvers, $processOwners);

        $workflowEngine = app(WorkflowEngine::class);
        $approvalService = app(ApprovalService::class);
        $auditLogger = app(AuditLogger::class);

        $this->seedDemoRequests(
            $requesters,
            $workflowTypes,
            $requestTypes,
            $workflowEngine,
            $approvalService,
            $auditLogger
        );
    }

    private function seedDepartments(): Collection
    {
        return collect([
            'IT',
            'Finance',
            'Human Resources',
        ])->map(fn (string $name) => Department::create(['name' => $name]));
    }

    private function seedRbac(): Collection
    {
        $permissions = collect([
            ['name' => 'Manage Users', 'slug' => 'manage_users'],
            ['name' => 'Manage Workflows', 'slug' => 'manage_workflows'],
            ['name' => 'Approve Requests', 'slug' => 'approve_requests'],
            ['name' => 'Create Requests', 'slug' => 'create_requests'],
            ['name' => 'View Reports', 'slug' => 'view_reports'],
        ])->map(fn (array $permission) => Permission::create($permission));

        $roles = collect([
            ['name' => 'Admin', 'slug' => 'admin'],
            ['name' => 'Process Owner', 'slug' => 'process-owner'],
            ['name' => 'Approver', 'slug' => 'approver'],
            ['name' => 'Requester', 'slug' => 'requester'],
        ])->map(fn (array $role) => Role::create($role));

        $roles->firstWhere('slug', 'admin')
            ->permissions()
            ->sync($permissions->pluck('id')->all());

        $roles->firstWhere('slug', 'process-owner')
            ->permissions()
            ->sync($permissions->whereIn('slug', ['manage_workflows', 'approve_requests', 'view_reports'])->pluck('id')->all());

        $roles->firstWhere('slug', 'approver')
            ->permissions()
            ->sync($permissions->whereIn('slug', ['approve_requests'])->pluck('id')->all());

        $roles->firstWhere('slug', 'requester')
            ->permissions()
            ->sync($permissions->whereIn('slug', ['create_requests'])->pluck('id')->all());

        return $roles;
    }

    private function seedUsers(Collection $departments, Collection $roles): array
    {
        $password = Hash::make('Password123!');

        $admin = User::create([
            'name' => 'FlowDesk Admin',
            'email' => 'admin@flowdesk.local',
            'password' => $password,
            'department_id' => $departments->first()->id,
        ]);
        $admin->roles()->sync([$roles->firstWhere('slug', 'admin')->id]);

        $processOwners = collect(range(1, 2))->map(function (int $i) use ($password, $departments, $roles) {
            $user = User::create([
                'name' => "Process Owner {$i}",
                'email' => "owner{$i}@flowdesk.local",
                'password' => $password,
                'department_id' => $departments->random()->id,
            ]);
            $user->roles()->sync([$roles->firstWhere('slug', 'process-owner')->id]);

            return $user;
        });

        $approvers = collect(range(1, 4))->map(function (int $i) use ($password, $departments, $roles) {
            $user = User::create([
                'name' => "Approver {$i}",
                'email' => "approver{$i}@flowdesk.local",
                'password' => $password,
                'department_id' => $departments->random()->id,
            ]);
            $user->roles()->sync([$roles->firstWhere('slug', 'approver')->id]);

            return $user;
        });

        $requesters = collect(range(1, 10))->map(function (int $i) use ($password, $departments, $roles) {
            $user = User::create([
                'name' => "Requester {$i}",
                'email' => "requester{$i}@flowdesk.local",
                'password' => $password,
                'department_id' => $departments->random()->id,
            ]);
            $user->roles()->sync([$roles->firstWhere('slug', 'requester')->id]);

            return $user;
        });

        return [$admin, $processOwners, $approvers, $requesters];
    }

    private function seedRequestTypes(): Collection
    {
        return collect([
            ['name' => 'IT Access', 'description' => 'Access to internal systems and services.'],
            ['name' => 'Software Purchase', 'description' => 'Purchase or license of software tools.'],
            ['name' => 'Travel Authorization', 'description' => 'Business travel request approval.'],
            ['name' => 'Hardware Procurement', 'description' => 'Laptop, monitor, peripherals and similar.'],
            ['name' => 'Training Request', 'description' => 'Conference, course, certification training request.'],
        ])->map(fn (array $type) => RequestType::create($type));
    }

    private function seedWorkflows(Collection $workflowTypes, Collection $approvers, Collection $processOwners): void
    {
        $itAccess = $workflowTypes[0];
        $softwarePurchase = $workflowTypes[1];
        $travel = $workflowTypes[2];

        WorkflowDefinition::create([
            'request_type_id' => $itAccess->id,
            'name' => 'IT Access v1',
            'version' => 1,
            'is_active' => true,
            'definition_json' => [
                'steps' => [
                    [
                        'step_key' => 'security-review',
                        'step_name' => 'Security Review',
                        'parallel' => true,
                        'rule' => 'any',
                        'approvers' => [
                            ['by_role' => 'Approver'],
                        ],
                    ],
                    [
                        'step_key' => 'owner-signoff',
                        'step_name' => 'Process Owner Sign Off',
                        'parallel' => false,
                        'rule' => 'all',
                        'approvers' => $processOwners->map(fn (User $user) => $user->id)->values()->all(),
                    ],
                ],
            ],
        ]);

        WorkflowDefinition::create([
            'request_type_id' => $softwarePurchase->id,
            'name' => 'Software Purchase v1',
            'version' => 1,
            'is_active' => true,
            'definition_json' => [
                'steps' => [
                    [
                        'step_key' => 'finance-gate',
                        'step_name' => 'Finance Gate',
                        'parallel' => true,
                        'rule' => 'all',
                        'approvers' => $approvers->take(2)->map(fn (User $user) => ['user_id' => $user->id])->values()->all(),
                    ],
                    [
                        'step_key' => 'owner-decision',
                        'step_name' => 'Owner Decision',
                        'parallel' => true,
                        'rule' => 'any',
                        'approvers' => [
                            ['by_role' => 'ProcessOwner'],
                        ],
                    ],
                ],
            ],
        ]);

        WorkflowDefinition::create([
            'request_type_id' => $travel->id,
            'name' => 'Travel v1',
            'version' => 1,
            'is_active' => true,
            'definition_json' => [
                'steps' => [
                    [
                        'step_key' => 'manager-review',
                        'step_name' => 'Manager Review',
                        'parallel' => false,
                        'rule' => 'any',
                        'approvers' => $approvers->take(2)->map(fn (User $user) => $user->id)->values()->all(),
                    ],
                    [
                        'step_key' => 'finance-review',
                        'step_name' => 'Finance Review',
                        'parallel' => true,
                        'rule' => 'all',
                        'approvers' => [
                            ['by_role' => 'Approver'],
                        ],
                    ],
                ],
            ],
        ]);
    }

    private function seedDemoRequests(
        Collection $requesters,
        Collection $workflowTypes,
        Collection $requestTypes,
        WorkflowEngine $workflowEngine,
        ApprovalService $approvalService,
        AuditLogger $auditLogger
    ): void {
        $allTypes = $requestTypes->values();

        foreach (range(1, 25) as $i) {
            $requester = $requesters->random();
            $type = $i <= 20 ? $workflowTypes->random() : $allTypes->random();

            $serviceRequest = ServiceRequest::create([
                'title' => "Demo Request {$i} - {$type->name}",
                'description' => "Seeded demo request #{$i} for FlowDesk.",
                'type_id' => $type->id,
                'department_id' => $requester->department_id,
                'priority' => collect(Priority::cases())->random()->value,
                'status' => RequestStatus::DRAFT->value,
                'created_by' => $requester->id,
            ]);

            $auditLogger->log($requester, 'request.created', $serviceRequest, null, $serviceRequest->toArray());

            if ($i > 20 || ! $workflowTypes->pluck('id')->contains($type->id)) {
                continue;
            }

            $serviceRequest = $workflowEngine->submit($serviceRequest, $requester);

            if ($i % 5 === 0) {
                $pending = ApprovalTask::query()->where('request_id', $serviceRequest->id)->where('status', ApprovalTaskStatus::PENDING->value)->first();

                if ($pending !== null && $pending->assignee !== null) {
                    $approvalService->reject($pending, $pending->assignee, 'Rejected in demo seed data.');
                }

                continue;
            }

            if ($i % 5 === 1 || $i % 5 === 2) {
                $this->approveUntilDone($serviceRequest, $approvalService);
                continue;
            }

            if ($i % 5 === 3) {
                $pending = ApprovalTask::query()->where('request_id', $serviceRequest->id)->where('status', ApprovalTaskStatus::PENDING->value)->first();

                if ($pending !== null && $pending->assignee !== null) {
                    $approvalService->approve($pending, $pending->assignee, 'Partially approved in seed data.');
                }
            }
        }
    }

    private function approveUntilDone(ServiceRequest $serviceRequest, ApprovalService $approvalService): void
    {
        for ($i = 0; $i < 20; $i++) {
            $serviceRequest->refresh();

            if (in_array($serviceRequest->status, [RequestStatus::APPROVED, RequestStatus::REJECTED], true)) {
                return;
            }

            $pending = ApprovalTask::query()
                ->where('request_id', $serviceRequest->id)
                ->where('status', ApprovalTaskStatus::PENDING->value)
                ->orderBy('id')
                ->first();

            if ($pending === null || $pending->assignee === null) {
                return;
            }

            $approvalService->approve($pending, $pending->assignee, 'Approved in seed data.');
        }
    }
}
