<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_first_user_registers_as_admin()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['user' => ['id', 'name', 'email', 'role']])
            ->assertJsonPath('user.role', 'admin');

        $this->assertDatabaseHas('users', [
            'email' => 'admin@example.com',
            'role' => 'admin',
        ]);
    }

    public function test_subsequent_users_register_as_author()
    {
        // Create first user to ensure count > 0
        User::factory()->create();

        $response = $this->postJson('/api/register', [
            'name' => 'Author User',
            'email' => 'author@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('user.role', 'author');

        $this->assertDatabaseHas('users', [
            'email' => 'author@example.com',
            'role' => 'author',
        ]);
    }

    public function test_user_can_login_with_valid_credentials()
    {
        $password = 'password123';

        $user = User::factory()->create([
            'email' => 'loginuser@example.com',
            'password' => bcrypt($password),
            'role' => 'author',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => $password,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['token', 'user'])
            ->assertJsonPath('user.email', $user->email);
    }

    public function test_user_cannot_login_with_invalid_password()
    {
        $user = User::factory()->create([
            'email' => 'badlogin@example.com',
            'password' => bcrypt('correctpassword'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('error', 'Invalid credentials');
    }

    public function test_authenticated_user_can_logout()
    {
        $user = User::factory()->create();

        $token = $user->createToken('api_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => "Bearer $token",
        ])->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Logged out']);
    }
}
