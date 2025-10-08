<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Organization;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoreSaaSFlowTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_user_can_register_create_an_organization_and_create_a_workflow()
    {
        // 1. Seed the necessary roles and permissions for the 'api' guard.
        $this->seed(RolesAndPermissionsSeeder::class);

        // 2. Register a new user.
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        $this->postJson('/api/auth/register', $userData)->assertStatus(201);
        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user);

        // 3. As the new user, create an organization.
        $orgResponse = $this->actingAs($user, 'api')->postJson('/api/v1/organizations', [
            'name' => 'My First Organization',
        ]);
        $orgResponse->assertStatus(201);
        $orgId = $orgResponse->json('data.id');
        $organization = Organization::find($orgId);
        $this->assertNotNull($organization);

        // 4. Verify the user is the owner and a default team was created.
        $this->assertEquals($user->id, $organization->user_id);
        $this->assertCount(1, $organization->teams);
        $defaultTeam = $organization->teams->first();
        $this->assertEquals('General', $defaultTeam->name);
        $this->assertTrue($user->belongsToTeam($defaultTeam));
        $this->assertTrue($user->hasRole('owner', $defaultTeam->id));

        // 5. As the user, create a workflow.
        $workflowResponse = $this->actingAs($user, 'api')->postJson('/api/v1/workflows', [
            'name' => 'My First Workflow',
            'nodes' => [['id' => '1', 'type' => 'trigger']],
            'connections' => [['source' => '1', 'target' => '2']],
        ]);

        // 6. Assert that the workflow was created successfully.
        $workflowResponse->assertStatus(201);
        $this->assertDatabaseHas('workflows', [
            'name' => 'My First Workflow',
            'user_id' => $user->id,
        ]);
    }
}