<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use BackedEnum;

class ApprovalTaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'request_id' => $this->request_id,
            'step_key' => $this->step_key,
            'step_name' => $this->step_name,
            'rule' => $this->rule instanceof BackedEnum ? $this->rule->value : $this->rule,
            'status' => $this->status instanceof BackedEnum ? $this->status->value : $this->status,
            'assigned_to' => $this->assigned_to,
            'assignee' => new UserResource($this->whenLoaded('assignee')),
            'decided_by' => $this->decided_by,
            'decider' => new UserResource($this->whenLoaded('decider')),
            'decided_at' => $this->decided_at,
            'comment' => $this->comment,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
