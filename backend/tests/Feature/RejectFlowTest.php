<?php

namespace Tests\Feature;

use App\Models\ApprovalTask;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Support\CreatesFlowDeskData;
use Tests\TestCase;

class RejectFlowTest extends TestCase
{
    use CreatesFlowDeskData;
    use RefreshDatabase;

    public function test_reject_immediately_rejects_request_and_skips_remaining(): void
    {
        $data = $this->createBaseUsersAndRoles();

        $this->createWorkflow($data['requestType'], [
            [
                'step_key' => 'first',
                'step_name' => 'First',
                'parallel' => true,
                'rule' => 'all',
                'approvers' => [$data['approverA']->id, $data['approverB']->id],
            ],
            [
                'step_key' => 'second',
                'step_name' => 'Second',
                'parallel' => true,
                'rule' => 'any',
                'approvers' => [$data['approverA']->id],
            ],
        ]);

        $serviceRequest = $this->createDraftRequest($data['requester'], $data['requestType'], $data['department']);

        Sanctum::actingAs($data['requester']);
        $this->postJson("/api/requests/{$serviceRequest->id}/submit")->assertOk();

        $taskA = ApprovalTask::query()->where('request_id', $serviceRequest->id)->where('assigned_to', $data['approverA']->id)->firstOrFail();

        Sanctum::actingAs($data['approverA']);
        $this->postJson("/api/approvals/tasks/{$taskA->id}/reject", [
            'comment' => 'No',
        ])->assertOk();

        $this->assertDatabaseHas('requests', [
            'id' => $serviceRequest->id,
            'status' => 'rejected',
        ]);

        $this->assertDatabaseHas('approval_tasks', [
            'request_id' => $serviceRequest->id,
            'assigned_to' => $data['approverB']->id,
            'status' => 'skipped',
        ]);

        $this->assertDatabaseMissing('approval_tasks', [
            'request_id' => $serviceRequest->id,
            'step_key' => 'second',
        ]);
    }
}
