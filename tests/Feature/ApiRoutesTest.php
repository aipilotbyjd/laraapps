<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

class ApiRoutesTest extends TestCase
{
    public function test_health_check_endpoint()
    {
        $response = $this->get('/api/health');
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'timestamp',
                'version',
                'services'
            ]);
    }
    
    public function test_authenticated_user_endpoint()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        
        $response = $this->get('/api/user');
        
        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email
            ]);
    }
    
    public function test_public_webhook_endpoint_exists()
    {
        $response = $this->get('/api/webhook/test123');
        
        // Should return 404 since the webhook doesn't exist, not 500
        $response->assertStatus(404);
    }
    
    public function test_api_routes_require_authentication()
    {
        $response = $this->get('/api/v1/workflows');
        
        $response->assertStatus(401);
    }
}