<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Database\Seeders\SubscriptionPlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScannerPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SubscriptionPlanSeeder::class);
    }

    public function test_scanner_page_renders_for_authenticated_user(): void
    {
        $plan = SubscriptionPlan::where('slug', 'professional')->first();
        $company = Company::create(['name' => 'Scan Co', 'email' => 'scan@co.com', 'plan_id' => $plan->id]);
        $user = User::create([
            'company_id' => $company->id,
            'name'       => 'Scanner User',
            'email'      => 'user@scan.com',
            'password'   => bcrypt('password123'),
            'role'       => 'owner',
        ]);

        $response = $this->actingAs($user)->get('/scan');
        $response->assertStatus(200)
            ->assertSee('High-Definition Document Scanner')
            ->assertSee('Take Photo with Phone Camera')
            ->assertSee('Save & Upload to Cloud', false);
    }
}
