<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequestTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('manage_workflows') ?? false;
    }

    public function rules(): array
    {
        $requestTypeId = $this->route('request_type')?->id;

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('request_types', 'name')->ignore($requestTypeId)],
            'description' => ['nullable', 'string'],
        ];
    }
}
