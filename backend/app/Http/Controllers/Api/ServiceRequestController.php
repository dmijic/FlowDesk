<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreServiceRequestRequest;
use App\Http\Resources\ServiceRequestResource;
use App\Models\AuditLog;
use App\Models\ServiceRequest;
use App\Services\AuditLogger;
use App\Services\WorkflowEngine;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class ServiceRequestController extends Controller
{
    public function __construct(
        private readonly WorkflowEngine $workflowEngine,
        private readonly AuditLogger $auditLogger
    ) {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();

        $query = ServiceRequest::query()
            ->with(['type', 'department', 'creator', 'approvalTasks.assignee'])
            ->latest('created_at');

        if (! $user->hasPermission('view_reports') && ! $user->hasPermission('manage_workflows')) {
            $query->where(function ($inner) use ($user): void {
                $inner->where('created_by', $user->id)
                    ->orWhereHas('approvalTasks', fn ($tasks) => $tasks->where('assigned_to', $user->id));
            });
        }

        return ServiceRequestResource::collection($query->paginate(20));
    }

    public function store(StoreServiceRequestRequest $request): ServiceRequestResource
    {
        Gate::authorize('create_requests');

        $serviceRequest = ServiceRequest::create([
            ...$request->validated(),
            'created_by' => $request->user()->id,
            'status' => 'draft',
        ]);

        $this->auditLogger->log(
            $request->user(),
            'request.created',
            $serviceRequest,
            null,
            $serviceRequest->toArray()
        );

        return new ServiceRequestResource($serviceRequest->load(['type', 'department', 'creator', 'attachments', 'approvalTasks']));
    }

    public function show(ServiceRequest $requestModel): ServiceRequestResource
    {
        $this->authorize('view', $requestModel);

        $requestModel->load(['type', 'department', 'creator', 'attachments', 'approvalTasks.assignee']);

        $timeline = AuditLog::query()
            ->with('actor')
            ->where(function ($query) use ($requestModel): void {
                $query->where(function ($q) use ($requestModel): void {
                    $q->where('entity_type', ServiceRequest::class)
                        ->where('entity_id', $requestModel->id);
                })->orWhere('meta_json->request_id', $requestModel->id);
            })
            ->orderBy('created_at')
            ->get();

        $requestModel->setRelation('auditLogs', $timeline);

        return new ServiceRequestResource($requestModel);
    }

    public function submit(ServiceRequest $requestModel): ServiceRequestResource
    {
        $this->authorize('submit', $requestModel);

        $updated = $this->workflowEngine->submit($requestModel, auth()->user());

        return new ServiceRequestResource($updated->load(['type', 'department', 'creator', 'attachments', 'approvalTasks.assignee']));
    }
}
