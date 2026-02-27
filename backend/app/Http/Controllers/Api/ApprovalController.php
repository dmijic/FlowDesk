<?php

namespace App\Http\Controllers\Api;

use App\Enums\ApprovalTaskStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\DecisionRequest;
use App\Http\Resources\ApprovalTaskResource;
use App\Models\ApprovalTask;
use App\Services\ApprovalService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class ApprovalController extends Controller
{
    public function __construct(private readonly ApprovalService $approvalService)
    {
    }

    public function inbox(): AnonymousResourceCollection
    {
        Gate::authorize('approve_requests');

        return ApprovalTaskResource::collection(
            ApprovalTask::query()
                ->where('assigned_to', auth()->id())
                ->where('status', ApprovalTaskStatus::PENDING->value)
                ->with(['request.type', 'request.creator', 'assignee'])
                ->latest()
                ->get()
        );
    }

    public function approve(DecisionRequest $request, ApprovalTask $task)
    {
        $this->authorize('decide', $task);

        $serviceRequest = $this->approvalService->approve($task, $request->user(), $request->string('comment')->toString() ?: null);

        return response()->json([
            'message' => 'Task approved.',
            'request_id' => $serviceRequest->id,
            'request_status' => $serviceRequest->status->value,
        ]);
    }

    public function reject(DecisionRequest $request, ApprovalTask $task)
    {
        $this->authorize('decide', $task);

        $serviceRequest = $this->approvalService->reject($task, $request->user(), $request->string('comment')->toString() ?: null);

        return response()->json([
            'message' => 'Task rejected.',
            'request_id' => $serviceRequest->id,
            'request_status' => $serviceRequest->status->value,
        ]);
    }
}
