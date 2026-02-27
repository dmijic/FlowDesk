<?php

namespace App\Services;

use App\Enums\ApprovalRule;
use App\Enums\ApprovalTaskStatus;
use App\Enums\RequestStatus;
use App\Models\ApprovalTask;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ApprovalService
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly WorkflowEngine $workflowEngine
    ) {
    }

    public function approve(ApprovalTask $task, User $actor, ?string $comment = null): ServiceRequest
    {
        return $this->decide($task, $actor, ApprovalTaskStatus::APPROVED, $comment);
    }

    public function reject(ApprovalTask $task, User $actor, ?string $comment = null): ServiceRequest
    {
        return $this->decide($task, $actor, ApprovalTaskStatus::REJECTED, $comment);
    }

    private function decide(ApprovalTask $task, User $actor, ApprovalTaskStatus $decision, ?string $comment): ServiceRequest
    {
        return DB::transaction(function () use ($task, $actor, $decision, $comment): ServiceRequest {
            $task = ApprovalTask::query()->lockForUpdate()->findOrFail($task->id);
            $request = ServiceRequest::query()->lockForUpdate()->findOrFail($task->request_id);

            if ($task->status !== ApprovalTaskStatus::PENDING) {
                abort(422, 'Task is not pending.');
            }

            if ($task->assigned_to !== $actor->id && ! $actor->hasPermission('approve_requests')) {
                abort(403, 'Task is not assigned to you.');
            }

            $beforeTask = $task->toArray();

            $task->update([
                'status' => $decision->value,
                'decided_by' => $actor->id,
                'decided_at' => now(),
                'comment' => $comment,
            ]);

            $this->auditLogger->log(
                $actor,
                'approval.task_decided',
                $task,
                $beforeTask,
                $task->fresh()->toArray(),
                ['decision' => $decision->value]
            );

            if ($decision === ApprovalTaskStatus::REJECTED) {
                return $this->handleReject($request, $task, $actor, $comment);
            }

            $rule = $task->rule;

            if ($rule === ApprovalRule::ANY) {
                ApprovalTask::query()
                    ->where('request_id', $request->id)
                    ->where('step_key', $task->step_key)
                    ->where('status', ApprovalTaskStatus::PENDING->value)
                    ->where('id', '!=', $task->id)
                    ->update(['status' => ApprovalTaskStatus::SKIPPED->value]);

                $this->workflowEngine->advanceToNextStep($request->fresh(), $task->step_key, $actor);

                return $request->fresh(['approvalTasks', 'creator']);
            }

            $createdSequential = $this->workflowEngine->createNextSequentialTaskIfNeeded($request, $task->step_key);

            if ($createdSequential) {
                return $request->fresh(['approvalTasks', 'creator']);
            }

            $hasPendingCurrentStep = ApprovalTask::query()
                ->where('request_id', $request->id)
                ->where('step_key', $task->step_key)
                ->where('status', ApprovalTaskStatus::PENDING->value)
                ->exists();

            if (! $hasPendingCurrentStep) {
                $this->workflowEngine->advanceToNextStep($request->fresh(), $task->step_key, $actor);
            }

            return $request->fresh(['approvalTasks', 'creator']);
        });
    }

    private function handleReject(ServiceRequest $request, ApprovalTask $task, User $actor, ?string $comment): ServiceRequest
    {
        $beforeRequest = $request->toArray();

        $request->update([
            'status' => RequestStatus::REJECTED->value,
            'decided_at' => now(),
        ]);

        ApprovalTask::query()
            ->where('request_id', $request->id)
            ->where('id', '!=', $task->id)
            ->where('status', ApprovalTaskStatus::PENDING->value)
            ->update(['status' => ApprovalTaskStatus::SKIPPED->value]);

        $this->auditLogger->log(
            $actor,
            'request.rejected',
            $request,
            $beforeRequest,
            $request->fresh()->toArray(),
            ['task_id' => $task->id]
        );

        $this->workflowEngine->notifyDecisionStakeholders($request->fresh(['creator']), 'rejected', $comment);

        return $request->fresh(['approvalTasks', 'creator']);
    }
}
