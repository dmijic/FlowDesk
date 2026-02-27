<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWorkflowRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('manage_workflows') ?? false;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->definition_json)) {
            $decoded = json_decode($this->definition_json, true);

            if (is_array($decoded)) {
                $this->merge(['definition_json' => $decoded]);
            }
        }
    }

    public function rules(): array
    {
        return [
            'request_type_id' => ['required', 'exists:request_types,id'],
            'name' => ['required', 'string', 'max:255'],
            'version' => ['required', 'integer', 'min:1'],
            'is_active' => ['sometimes', 'boolean'],
            'definition_json' => ['required', 'array'],
            'definition_json.steps' => ['required', 'array', 'min:1'],
            'definition_json.steps.*.step_key' => ['required', 'string', 'max:100'],
            'definition_json.steps.*.step_name' => ['required', 'string', 'max:255'],
            'definition_json.steps.*.rule' => ['required', Rule::in(['any', 'all'])],
            'definition_json.steps.*.parallel' => ['sometimes', 'boolean'],
            'definition_json.steps.*.approvers' => ['required', 'array', 'min:1'],
        ];
    }
}
