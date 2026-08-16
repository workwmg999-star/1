<?php

namespace Tests\Feature;

use Database\Seeders\SubscriptionPlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(SubscriptionPlanSeeder::class);
    }

    public function test_company_registers_and_gets_token(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'company_name' => 'Test Import Co',
            'company_email' => 'imports@test.com',
            'company_phone' => '+212600000000',
            'name' => 'John Doe',
            'email' => 'john@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.company_id', fn ($value) => is_int($value))
            ->assertJsonStructure(['data' => ['token', 'user']]);

        $this->assertDatabaseHas('companies', ['email' => 'imports@test.com']);
        $this->assertDatabaseHas('users', ['email' => 'john@test.com', 'role' => 'owner']);
    }

    public function test_login_returns_token(): void
    {
        $this->postJson('/api/v1/auth/register', [
            'company_name' => 'Test Co',
            'company_email' => 'co@test.com',
            'name' => 'John',
            'email' => 'john@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'john@test.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['token', 'user']]);
    }

    public function test_login_rejects_wrong_password(): void
    {
        $this->postJson('/api/v1/auth/register', [
            'company_name' => 'Test Co',
            'company_email' => 'co@test.com',
            'name' => 'John',
            'email' => 'john@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'john@test.com',
            'password' => 'wrong-password',
        ])->assertStatus(422);
    }

    public function test_me_returns_authenticated_user(): void
    {
        $register = $this->postJson('/api/v1/auth/register', [
            'company_name' => 'Test Co',
            'company_email' => 'co@test.com',
            'name' => 'John',
            'email' => 'john@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $token = $register->json('data.token');

        $this->getJson('/api/v1/auth/me', ['Authorization' => "Bearer $token"])
            ->assertStatus(200)
            ->assertJsonPath('data.email', 'john@test.com');
    }

    public function test_logout_revokes_token(): void
    {
        $register = $this->postJson('/api/v1/auth/register', [
            'company_name' => 'Test Co',
            'company_email' => 'co@test.com',
            'name' => 'John',
            'email' => 'john@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $token = $register->json('data.token');

        $this->postJson('/api/v1/auth/logout', [], ['Authorization' => "Bearer $token"])
            ->assertStatus(200)
            ->assertJsonPath('success', true);

        // The token row must be revoked in storage.
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}
