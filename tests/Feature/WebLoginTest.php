<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Database\Seeders\SubscriptionPlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SubscriptionPlanSeeder::class);
    }

    public function test_user_can_login_via_web_form(): void
    {
        $plan = SubscriptionPlan::where('slug', 'professional')->first();
        $company = Company::create(['name' => 'Demo Co', 'email' => 'demo@co.com', 'plan_id' => $plan->id]);
        $user = User::create([
            'company_id' => $company->id,
            'name'       => 'Karim Benali',
            'email'      => 'owner@docuscan.test',
            'password'   => bcrypt('password123'),
            'role'       => 'owner',
        ]);

        $response = $this->post('/login', [
            'email'    => 'owner@docuscan.test',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_can_register_via_web_form(): void
    {
        $response = $this->post('/register', [
            'company_name'          => 'New Import SARL',
            'company_email'         => 'contact@newimport.com',
            'name'                  => 'Sami Tazi',
            'email'                 => 'sami@newimport.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();
        $this->assertDatabaseHas('companies', ['name' => 'New Import SARL']);
    }
}
