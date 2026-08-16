<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Folder;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocuScanWebPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\SubscriptionPlanSeeder::class);
    }

    public function test_landing_page_renders_successfully(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200)
            ->assertSee('DocuScan')
            ->assertSee('Scan, Organise', false)
            ->assertSee('Create Free Account');
    }

    public function test_login_page_renders_successfully(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200)
            ->assertSee('Welcome back')
            ->assertSee('owner@docuscan.test');
    }

    public function test_register_page_renders_successfully(): void
    {
        $response = $this->get('/register');
        $response->assertStatus(200)
            ->assertSee('Create Company Account')
            ->assertSee('Company Name');
    }

    public function test_dashboard_renders_for_authenticated_user(): void
    {
        $plan = SubscriptionPlan::where('slug', 'professional')->first();
        $company = Company::create(['name' => 'Casablanca Logistics', 'email' => 'casa@logistics.com', 'plan_id' => $plan->id]);
        $user = User::create([
            'company_id' => $company->id,
            'name'       => 'Hassan Alami',
            'email'      => 'hassan@casa.com',
            'password'   => bcrypt('password123'),
            'role'       => 'owner',
        ]);

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200)
            ->assertSee('Dashboard')
            ->assertSee('Total Documents')
            ->assertSee('Casablanca Logistics');
    }

    public function test_folders_page_renders(): void
    {
        $plan = SubscriptionPlan::where('slug', 'professional')->first();
        $company = Company::create(['name' => 'Import Co', 'email' => 'import@co.com', 'plan_id' => $plan->id]);
        $user = User::create([
            'company_id' => $company->id,
            'name'       => 'Test User',
            'email'      => 'test@import.com',
            'password'   => bcrypt('password123'),
            'role'       => 'owner',
        ]);

        Folder::create([
            'company_id' => $company->id,
            'user_id'    => $user->id,
            'name'       => 'Factures 2026',
            'color'      => '#10b981',
            'icon'       => 'receipt',
        ]);

        $response = $this->actingAs($user)->get('/folders');
        $response->assertStatus(200)
            ->assertSee('Folders')
            ->assertSee('Factures 2026');
    }

    public function test_documents_page_renders(): void
    {
        $plan = SubscriptionPlan::where('slug', 'professional')->first();
        $company = Company::create(['name' => 'Import Co', 'email' => 'import@co.com', 'plan_id' => $plan->id]);
        $user = User::create([
            'company_id' => $company->id,
            'name'       => 'Test User',
            'email'      => 'test@import.com',
            'password'   => bcrypt('password123'),
            'role'       => 'owner',
        ]);

        $response = $this->actingAs($user)->get('/documents');
        $response->assertStatus(200)
            ->assertSee('Documents');
    }

    public function test_subscriptions_page_renders(): void
    {
        $plan = SubscriptionPlan::where('slug', 'free')->first();
        $company = Company::create(['name' => 'Import Co', 'email' => 'import@co.com', 'plan_id' => $plan->id]);
        $user = User::create([
            'company_id' => $company->id,
            'name'       => 'Test User',
            'email'      => 'test@import.com',
            'password'   => bcrypt('password123'),
            'role'       => 'owner',
        ]);

        $response = $this->actingAs($user)->get('/subscriptions');
        $response->assertStatus(200)
            ->assertSee('Subscription Plans')
            ->assertSee('Free')
            ->assertSee('Professional');
    }
}
