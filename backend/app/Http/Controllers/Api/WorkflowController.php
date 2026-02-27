<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWorkflowRequest;
use App\Http\Resources\WorkflowDefinitionResource;
use App\Models\WorkflowDefinition;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

class WorkflowController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        Gate::authorize('manage_workflows');

        return WorkflowDefinitionResource::collection(
            WorkflowDefinition::query()->with('requestType')->latest('updated_at')->get()
        );
    }

    public function store(StoreWorkflowRequest $request): WorkflowDefinitionResource
    {
        Gate::authorize('manage_workflows');

        $this->assertWorkflowJson($request->validated('definition_json'));

        $data = $request->validated();

        if (($data['is_active'] ?? false) === true) {
            WorkflowDefinition::query()
                ->where('request_type_id', $data['request_type_id'])
                ->update(['is_active' => false]);
        }

        $workflow = WorkflowDefinition::create($data)->load('requestType');

        return new WorkflowDefinitionResource($workflow);
    }

    public function show(WorkflowDefinition $workflow): WorkflowDefinitionResource
    {
        Gate::authorize('manage_workflows');

        return new WorkflowDefinitionResource($workflow->load('requestType'));
    }

    public function update(StoreWorkflowRequest $request, WorkflowDefinition $workflow): WorkflowDefinitionResource
    {
        Gate::authorize('manage_workflows');

        $this->assertWorkflowJson($request->validated('definition_json'));

        $data = $request->validated();

        if (($data['is_active'] ?? false) === true) {
            WorkflowDefinition::query()
                ->where('request_type_id', $data['request_type_id'])
                ->where('id', '!=', $workflow->id)
                ->update(['is_active' => false]);
        }

        $workflow->update($data);

        return new WorkflowDefinitionResource($workflow->fresh('requestType'));
    }

    public function destroy(WorkflowDefinition $workflow)
    {
        Gate::authorize('manage_workflows');

        $workflow->delete();

        return response()->json(status: 204);
    }

    private function assertWorkflowJson(array $definition): void
    {
        Validator::make($definition, [
            'steps' => ['required', 'array', 'min:1'],
            'steps.*.step_key' => ['required', 'string'],
            'steps.*.step_name' => ['required', 'string'],
            'steps.*.approvers' => ['required', 'array', 'min:1'],
            'steps.*.rule' => ['required', 'in:any,all'],
            'steps.*.parallel' => ['sometimes', 'boolean'],
        ])->validate();
    }
}
