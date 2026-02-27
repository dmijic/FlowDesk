<?php

namespace Tests\Feature;

use App\Models\ApprovalTask;
use App\Models\ServiceRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Support\CreatesFlowDeskData;
use Tests\TestCase;

class ApprovalAnyRuleTest extends TestCase
{
    use CreatesFlowDeskData;
    use RefreshDatabase;

    public function test_any_rule_completes_step_and_skips_other_tasks(): void
    {
        $data = $this->createBaseUsersAndRoles();

        $this->createWorkflow($data['requestType'], [
            [
                'step_key' => 'any-step',
                'step_name' => 'Any Step',
                'parallel' => true,
                'rule' => 'any',
                'approvers' => [$data['approverA']->id, $data['approverB']->id],
            ],
        ]);

        $serviceRequest = $this->createDraftRequest($data['requester'], $data['requestType'], $data['department']);

        Sanctum::actingAs($data['requester']);
        $this->postJson("/api/requests/{$serviceRequest->id}/submit")->assertOk();

        $taskA = ApprovalTask::query()->where('request_id', $serviceRequest->id)->where('assigned_to', $data['approverA']->id)->firstOrFail();

        Sanctum::actingAs($data['approverA']);
        $this->postJson("/api/approvals/tasks/{$taskA->id}/approve", [
            'comment' => 'Looks good',
        ])->assertOk();

        $this->assertDatabaseHas('requests', [
            'id' => $serviceRequest->id,
            'status' => 'approved',
        ]);

        $this->assertDatabaseHas('approval_tasks', [
            'request_id' => $serviceRequest->id,
            'assigned_to' => $data['approverB']->id,
            'status' => 'skipped',
        ]);

        $this->assertEquals('approved', ServiceRequest::findOrFail($serviceRequest->id)->status->value);
    }
}
