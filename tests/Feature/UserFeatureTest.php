<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_all_users()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->getJson('/api/users');

        $response->assertStatus(200)
            ->assertJsonStructure([['id', 'name', 'email', 'role']]);
    }

    public function test_non_admin_cannot_view_all_users()
    {
        $user = User::factory()->create(['role' => 'author']);

        $response = $this->actingAs($user)->getJson('/api/users');

        $response->assertStatus(403)
            ->assertJson(['error' => 'Unauthorized | custom']);
    }

    public function test_admin_can_assign_role_to_user()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'author']);

        $response = $this->actingAs($admin)->postJson("/api/users/{$user->id}/assign-role", [
            'role' => 'editor',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('user.role', 'editor');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'role' => 'editor',
        ]);
    }

    public function test_non_admin_cannot_assign_role()
    {
        $user = User::factory()->create(['role' => 'author']);
        $targetUser = User::factory()->create(['role' => 'author']);

        $response = $this->actingAs($user)->postJson("/api/users/{$targetUser->id}/assign-role", [
            'role' => 'editor',
        ]);

        $response->assertStatus(403)
            ->assertJson(['error' => 'Unauthorized']);
    }

    public function test_authenticated_user_can_view_profile()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/profile');

        $response->assertStatus(200)
            ->assertJsonPath('id', $user->id);
    }

    public function test_unauthenticated_user_cannot_access_profile()
    {
        $response = $this->getJson('/api/profile');

        $response->assertStatus(401);
    }
}
