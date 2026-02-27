<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Support\CreatesFlowDeskData;
use Tests\TestCase;

class PermissionChecksTest extends TestCase
{
    use CreatesFlowDeskData;
    use RefreshDatabase;

    public function test_requester_cannot_view_other_users_request(): void
    {
        $data = $this->createBaseUsersAndRoles();

        $this->createWorkflow($data['requestType'], [
            [
                'step_key' => 'single',
                'step_name' => 'Single',
                'parallel' => true,
                'rule' => 'any',
                'approvers' => [$data['approverA']->id],
            ],
        ]);

        $otherRequest = $this->createDraftRequest($data['requesterTwo'], $data['requestType'], $data['department']);

        Sanctum::actingAs($data['requester']);

        $this->getJson("/api/requests/{$otherRequest->id}")
            ->assertForbidden();
    }

    public function test_approver_cannot_manage_users(): void
    {
        $data = $this->createBaseUsersAndRoles();

        Sanctum::actingAs($data['approverA']);

        $this->postJson('/api/users', [
            'name' => 'No Access',
            'email' => 'noaccess@test.local',
            'password' => 'Password123!',
        ])->assertForbidden();
    }
}
