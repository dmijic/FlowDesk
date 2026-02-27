<?php

namespace Tests\Feature;

use App\Models\ApprovalTask;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Support\CreatesFlowDeskData;
use Tests\TestCase;

class ApprovalAllRuleTest extends TestCase
{
    use CreatesFlowDeskData;
    use RefreshDatabase;

    public function test_all_rule_requires_all_approvals(): void
    {
        $data = $this->createBaseUsersAndRoles();

        $this->createWorkflow($data['requestType'], [
            [
                'step_key' => 'all-step',
                'step_name' => 'All Step',
                'parallel' => true,
                'rule' => 'all',
                'approvers' => [$data['approverA']->id, $data['approverB']->id],
            ],
        ]);

        $serviceRequest = $this->createDraftRequest($data['requester'], $data['requestType'], $data['department']);

        Sanctum::actingAs($data['requester']);
        $this->postJson("/api/requests/{$serviceRequest->id}/submit")->assertOk();

        $taskA = ApprovalTask::query()->where('request_id', $serviceRequest->id)->where('assigned_to', $data['approverA']->id)->firstOrFail();
        $taskB = ApprovalTask::query()->where('request_id', $serviceRequest->id)->where('assigned_to', $data['approverB']->id)->firstOrFail();

        Sanctum::actingAs($data['approverA']);
        $this->postJson("/api/approvals/tasks/{$taskA->id}/approve")->assertOk();

        $this->assertDatabaseHas('requests', [
            'id' => $serviceRequest->id,
            'status' => 'in_review',
        ]);

        Sanctum::actingAs($data['approverB']);
        $this->postJson("/api/approvals/tasks/{$taskB->id}/approve")->assertOk();

        $this->assertDatabaseHas('requests', [
            'id' => $serviceRequest->id,
            'status' => 'approved',
        ]);
    }
}
