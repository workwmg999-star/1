<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Database\Seeders\SubscriptionPlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(SubscriptionPlanSeeder::class);
    }

    private function makeOwnerWithCompany(string $name = 'Owner'): User
    {
        $plan = SubscriptionPlan::where('slug', 'free')->first();

        $company = Company::create([
            'name' => "{$name} Co",
            'email' => strtolower($name).'@test.com',
            'plan_id' => $plan->id,
        ]);

        return User::factory()->create([
            'company_id' => $company->id,
            'name' => $name,
            'role' => User::ROLE_OWNER,
        ]);
    }

    public function test_employee_cannot_manage_users(): void
    {
        $owner = $this->makeOwnerWithCompany();
        $employee = User::factory()->create([
            'company_id' => $owner->company_id,
            'role' => User::ROLE_EMPLOYEE,
        ]);

        Sanctum::actingAs($employee);

        $this->getJson('/api/v1/company/users')->assertStatus(403);
        $this->postJson('/api/v1/company/users', [
            'name' => 'New Guy',
            'email' => 'new@test.com',
            'password' => 'password123',
            'role' => 'employee',
        ])->assertStatus(403);
    }

    public function test_owner_can_create_and_list_users(): void
    {
        $owner = $this->makeOwnerWithCompany();
        Sanctum::actingAs($owner);

        $this->postJson('/api/v1/company/users', [
            'name' => 'Alice Employee',
            'email' => 'alice@test.com',
            'password' => 'password123',
            'role' => 'employee',
        ])->assertStatus(201)
            ->assertJsonPath('data.role', 'employee');

        $this->getJson('/api/v1/company/users')
            ->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_user_limit_from_free_plan_is_enforced(): void
    {
        // Free plan: max 2 users. Owner counts as 1.
        $owner = $this->makeOwnerWithCompany();
        Sanctum::actingAs($owner);

        $this->postJson('/api/v1/company/users', [
            'name' => 'Employee One',
            'email' => 'one@test.com',
            'password' => 'password123',
            'role' => 'employee',
        ])->assertStatus(201);

        $this->postJson('/api/v1/company/users', [
            'name' => 'Employee Two',
            'email' => 'two@test.com',
            'password' => 'password123',
            'role' => 'employee',
        ])->assertStatus(402)
            ->assertJsonPath('success', false);
    }

    public function test_owner_cannot_delete_the_last_owner(): void
    {
        $owner = $this->makeOwnerWithCompany();
        Sanctum::actingAs($owner);

        $this->deleteJson("/api/v1/company/users/{$owner->id}")->assertStatus(422);

        $this->assertDatabaseHas('users', ['id' => $owner->id]);
    }

    public function test_owner_can_deactivate_an_employee(): void
    {
        $owner = $this->makeOwnerWithCompany();
        $employee = User::factory()->create([
            'company_id' => $owner->company_id,
            'role' => User::ROLE_EMPLOYEE,
        ]);

        Sanctum::actingAs($owner);

        $this->putJson("/api/v1/company/users/{$employee->id}", ['is_active' => false])
            ->assertStatus(200)
            ->assertJsonPath('data.is_active', false);

        $this->assertDatabaseHas('users', ['id' => $employee->id, 'is_active' => 0]);
    }
}
