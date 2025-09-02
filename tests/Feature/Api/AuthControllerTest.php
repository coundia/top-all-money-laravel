<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_register_user()
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertCreated()
            ->assertJsonStructure(['accessToken','tokenType','user' => ['id','name','email']]);

        $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
    }

    public function test_it_can_login_user()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['accessToken','tokenType','user' => ['id','name','email']]);
    }

    public function test_me_returns_authenticated_user()
    {
        $user = User::factory()->create();
        $token = $user->createToken('api')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/auth/me');

        $response->assertOk()
            ->assertJsonPath('id', $user->id);
    }

    public function test_it_can_logout_user()
    {
        $user = User::factory()->create();
        $token = $user->createToken('api')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/auth/logout');

        $response->assertNoContent();
    }

    public function test_it_can_update_password()
    {
        $user = User::factory()->create([
            'password' => bcrypt('oldpass123'),
        ]);

        $token = $user->createToken('api')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/auth/update-password', [
                'current_password' => 'oldpass123',
                'password' => 'newpass123',
                'password_confirmation' => 'newpass123',
            ]);

        $response->assertOk()
            ->assertJson(['message' => 'Password updated']);
    }
}
