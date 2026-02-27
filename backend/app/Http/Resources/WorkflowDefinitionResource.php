<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkflowDefinitionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'request_type_id' => $this->request_type_id,
            'request_type' => new RequestTypeResource($this->whenLoaded('requestType')),
            'name' => $this->name,
            'version' => $this->version,
            'is_active' => $this->is_active,
            'definition_json' => $this->definition_json,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
