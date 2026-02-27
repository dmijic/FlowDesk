<?php

namespace App\Http\Resources;

use App\Models\ServiceRequest;
use BackedEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ServiceRequest
 */
class ServiceRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'type_id' => $this->type_id,
            'type' => new RequestTypeResource($this->whenLoaded('type')),
            'department_id' => $this->department_id,
            'department' => new DepartmentResource($this->whenLoaded('department')),
            'priority' => $this->priority instanceof BackedEnum ? $this->priority->value : $this->priority,
            'status' => $this->status instanceof BackedEnum ? $this->status->value : $this->status,
            'created_by' => $this->created_by,
            'creator' => new UserResource($this->whenLoaded('creator')),
            'submitted_at' => $this->submitted_at,
            'decided_at' => $this->decided_at,
            'attachments' => $this->whenLoaded('attachments', function () {
                return $this->attachments->map(fn ($attachment) => [
                    'id' => $attachment->id,
                    'original_name' => $attachment->original_name,
                    'mime' => $attachment->mime,
                    'size' => $attachment->size,
                    'created_at' => $attachment->created_at,
                ]);
            }),
            'approval_tasks' => ApprovalTaskResource::collection($this->whenLoaded('approvalTasks')),
            'timeline' => AuditLogResource::collection($this->whenLoaded('auditLogs')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
