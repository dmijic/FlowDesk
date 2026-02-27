<?php

namespace Tests\Feature;

use App\Models\ApprovalTask;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Support\CreatesFlowDeskData;
use Tests\TestCase;

class RequestSubmissionTest extends TestCase
{
    use CreatesFlowDeskData;
    use RefreshDatabase;

    public function test_request_submission_creates_tasks_for_first_step(): void
    {
        $data = $this->createBaseUsersAndRoles();

        $this->createWorkflow($data['requestType'], [
            [
                'step_key' => 'step-1',
                'step_name' => 'First Step',
                'parallel' => true,
                'rule' => 'all',
                'approvers' => [$data['approverA']->id, $data['approverB']->id],
            ],
        ]);

        $serviceRequest = $this->createDraftRequest($data['requester'], $data['requestType'], $data['department']);

        Sanctum::actingAs($data['requester']);

        $this->postJson("/api/requests/{$serviceRequest->id}/submit")
            ->assertOk()
            ->assertJsonPath('data.status', 'in_review');

        $this->assertDatabaseCount('approval_tasks', 2);
        $this->assertDatabaseHas('approval_tasks', [
            'request_id' => $serviceRequest->id,
            'step_key' => 'step-1',
            'status' => 'pending',
        ]);

        $this->assertEquals(2, ApprovalTask::query()->where('request_id', $serviceRequest->id)->count());
    }
}
