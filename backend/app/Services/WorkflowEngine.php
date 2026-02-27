<?php

namespace App\Services;

use App\Enums\ApprovalRule;
use App\Enums\ApprovalTaskStatus;
use App\Enums\RequestStatus;
use App\Models\ApprovalTask;
use App\Models\Role;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Models\WorkflowDefinition;
use App\Notifications\ApprovalTaskAssignedNotification;
use App\Notifications\RequestDecisionNotification;
use App\Notifications\RequestSubmittedNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WorkflowEngine
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function submit(ServiceRequest $request, User $actor): ServiceRequest
    {
        if ($request->status !== RequestStatus::DRAFT) {
            abort(422, 'Only draft requests can be submitted.');
        }

        $workflow = $this->activeWorkflowFor($request);
        $steps = $this->stepsFromDefinition($workflow->definition_json);

        if ($steps === []) {
            abort(422, 'Workflow has no steps.');
        }

        DB::transaction(function () use ($request, $actor, $steps): void {
            $request->refresh();
            $before = $request->toArray();

            $request->update([
                'status' => RequestStatus::IN_REVIEW->value,
                'submitted_at' => now(),
            ]);

            $this->auditLogger->log(
                $actor,
                'request.submitted',
                $request,
                $before,
                $request->fresh()->toArray()
            );

            $this->createTasksForStep($request, $steps[0], $actor);
            $request->creator?->notify(new RequestSubmittedNotification($request));
        });

        return $request->fresh(['type', 'department', 'creator', 'attachments', 'approvalTasks.assignee']);
    }

    public function advanceToNextStep(ServiceRequest $request, string $currentStepKey, User $actor): void
    {
        $workflow = $this->activeWorkflowFor($request);
        $steps = $this->stepsFromDefinition($workflow->definition_json);

        $index = collect($steps)->search(fn(array $step): bool => $step['step_key'] === $currentStepKey);

        if ($index === false) {
            abort(422, 'Current workflow step not found.');
        }

        $next = $steps[$index + 1] ?? null;

        if ($next === null) {
            $before = $request->toArray();

            $request->update([
                'status' => RequestStatus::APPROVED->value,
                'decided_at' => now(),
            ]);

            $this->auditLogger->log(
                $actor,
                'request.approved',
                $request,
                $before,
                $request->fresh()->toArray()
            );

            $this->notifyDecisionStakeholders($request->fresh(['creator']), 'approved', null);

            return;
        }

        $this->createTasksForStep($request, $next, $actor);
    }

    public function createNextSequentialTaskIfNeeded(ServiceRequest $request, string $stepKey): bool
    {
        $step = $this->stepDefinition($request, $stepKey);

        if (($step['parallel'] ?? true) === true) {
            return false;
        }

        $approverIds = $this->resolveApproverIds($request, $step['approvers'] ?? []);
        $alreadyAssigned = ApprovalTask::query()
            ->where('request_id', $request->id)
            ->where('step_key', $stepKey)
            ->pluck('assigned_to')
            ->all();

        $nextApproverId = collect($approverIds)
            ->first(fn(int $id): bool => ! in_array($id, $alreadyAssigned, true));

        if ($nextApproverId === null) {
            return false;
        }

        $task = ApprovalTask::create([
            'request_id' => $request->id,
            'step_key' => $step['step_key'],
            'step_name' => $step['step_name'],
            'rule' => $step['rule'] ?? ApprovalRule::ALL->value,
            'status' => ApprovalTaskStatus::PENDING->value,
            'assigned_to' => $nextApproverId,
        ]);

        $this->auditLogger->log(
            null,
            'approval.task_assigned',
            $task,
            null,
            $task->toArray(),
            ['request_id' => $request->id]
        );

        $task->assignee?->notify(new ApprovalTaskAssignedNotification($task, $request));

        return true;
    }

    public function stepDefinition(ServiceRequest $request, string $stepKey): array
    {
        $workflow = $this->activeWorkflowFor($request);
        $steps = $this->stepsFromDefinition($workflow->definition_json);

        $step = collect($steps)->first(fn(array $s): bool => $s['step_key'] === $stepKey);

        if (! is_array($step)) {
            abort(422, 'Workflow step missing.');
        }

        return $step;
    }

    public function notifyDecisionStakeholders(ServiceRequest $request, string $decision, ?string $comment): void
    {
        $request->loadMissing('creator');

        $recipients = collect([$request->creator])
            ->merge(
                User::query()->whereHas('roles', fn($q) => $q->where('slug', 'process-owner'))->get()
            )
            ->filter()
            ->unique('id');

        foreach ($recipients as $recipient) {
            $recipient->notify(new RequestDecisionNotification($request, $decision, $comment));
        }
    }

    /**
     * @param  array<string, mixed>  $step
     * @return \Illuminate\Support\Collection<int, \App\Models\ApprovalTask>
     */
    private function createTasksForStep(ServiceRequest $request, array $step, User $actor): Collection
    {
        $approverIds = $this->resolveApproverIds($request, $step['approvers'] ?? []);

        if ($approverIds === []) {
            abort(422, 'Workflow step has no resolvable approvers.');
        }

        $parallel = Arr::get($step, 'parallel', true) === true;

        if (! $parallel) {
            $approverIds = [reset($approverIds)];
        }

        $tasks = collect($approverIds)
            ->map(function (int $approverId) use ($request, $step): ApprovalTask {
                return ApprovalTask::create([
                    'request_id' => $request->id,
                    'step_key' => (string) Arr::get($step, 'step_key'),
                    'step_name' => (string) Arr::get($step, 'step_name'),
                    'rule' => (string) Arr::get($step, 'rule', ApprovalRule::ALL->value),
                    'status' => ApprovalTaskStatus::PENDING->value,
                    'assigned_to' => $approverId,
                ]);
            });

        foreach ($tasks as $task) {
            $this->auditLogger->log(
                $actor,
                'approval.task_assigned',
                $task,
                null,
                $task->toArray(),
                ['request_id' => $request->id]
            );

            $task->assignee?->notify(new ApprovalTaskAssignedNotification($task, $request));
        }

        return $tasks;
    }

    /**
     * @param  array<string, mixed>  $definition
     * @return array<int, array<string, mixed>>
     */
    private function stepsFromDefinition(array $definition): array
    {
        $steps = Arr::get($definition, 'steps', []);

        if (! is_array($steps)) {
            return [];
        }

        return collect($steps)
            ->filter(fn($step) => is_array($step) && Arr::has($step, ['step_key', 'step_name']))
            ->values()
            ->all();
    }

    /**
     * @param  array<int, mixed>  $approvers
     * @return array<int, int>
     */
    private function resolveApproverIds(ServiceRequest $request, array $approvers): array
    {
        $resolved = [];

        foreach ($approvers as $approver) {
            if (is_numeric($approver)) {
                $resolved[] = (int) $approver;
                continue;
            }

            if (is_array($approver) && isset($approver['user_id'])) {
                $resolved[] = (int) $approver['user_id'];
                continue;
            }

            if (is_array($approver) && isset($approver['by_role'])) {
                $raw = (string) $approver['by_role'];
                $slugCandidates = collect([
                    Str::kebab($raw),
                    Str::slug(str_replace('_', ' ', $raw)),
                    Str::lower($raw),
                ])->unique()->values();

                $role = Role::query()->whereIn('slug', $slugCandidates)->first();

                if ($role !== null) {
                    $resolved = [...$resolved, ...$role->users()->pluck('users.id')->all()];
                }
            }
        }

        return collect($resolved)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function activeWorkflowFor(ServiceRequest $request): WorkflowDefinition
    {
        $workflow = WorkflowDefinition::query()
            ->where('request_type_id', $request->type_id)
            ->where('is_active', true)
            ->latest('version')
            ->first();

        if ($workflow === null) {
            abort(422, 'No active workflow definition for this request type.');
        }

        return $workflow;
    }
}
