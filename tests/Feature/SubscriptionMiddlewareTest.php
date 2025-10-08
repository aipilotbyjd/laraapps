<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Workflow;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        config(['permission.teams' => false]);
    }

    /** @test */
    public function it_restricts_workflow_creation_based_on_subscription_plan()
    {
        // 1. Create a user with a 'free' subscription.
        $user = User::factory()->create([
            'subscription_level' => 'free',
        ]);

        $this->seed(RolesAndPermissionsSeeder::class);
        $user->assignRole('editor', 'api');

        // 2. Create 5 workflows for the user.
        Workflow::factory()->count(5)->create([
            'user_id' => $user->id,
        ]);

        // 3. Attempt to create a 6th workflow.
        $response = $this->actingAs($user, 'api')->postJson('/api/v1/workflows', [
            'name' => 'My 6th Workflow',
            'description' => 'This should fail.',
            'nodes' => [
                [
                    'id' => '1',
                    'type' => 'trigger',
                ]
            ],
            'connections' => [
                [
                    'source' => '1',
                    'target' => '2',
                ]
            ],
        ]);

        // Assert that the request is forbidden.
        $response->assertStatus(403);

        // 4. Upgrade the user's subscription to 'pro'.
        $user->update(['subscription_level' => 'pro']);

        // 5. Attempt to create a 6th workflow again.
        $response = $this->actingAs($user, 'api')->postJson('/api/v1/workflows', [
            'name' => 'My 6th Workflow',
            'description' => 'This should succeed.',
            'nodes' => [
                [
                    'id' => '1',
                    'type' => 'trigger',
                ]
            ],
            'connections' => [
                [
                    'source' => '1',
                    'target' => '2',
                ]
            ],
        ]);

        // Assert that the request is successful.
        $response->assertStatus(201);
    }
}
