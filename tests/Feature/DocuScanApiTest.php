<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Folder;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocuScanApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\SubscriptionPlanSeeder::class);
    }

    public function test_can_register_new_company_and_owner(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'company_name'          => 'Atlas Logistics Ltd',
            'company_email'         => 'admin@atlaslogistics.com',
            'name'                  => 'Ahmed Tazi',
            'email'                 => 'ahmed@atlaslogistics.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'user' => ['id', 'name', 'email', 'role', 'company'],
                    'token',
                ],
            ]);

        $this->assertDatabaseHas('companies', ['name' => 'Atlas Logistics Ltd']);
        $this->assertDatabaseHas('users', ['email' => 'ahmed@atlaslogistics.com', 'role' => 'owner']);
    }

    public function test_can_login_and_retrieve_profile(): void
    {
        $plan = SubscriptionPlan::where('slug', 'free')->first();
        $company = Company::create(['name' => 'Test Corp', 'email' => 'corp@test.com', 'plan_id' => $plan->id]);
        $user = User::create([
            'company_id' => $company->id,
            'name'       => 'Test User',
            'email'      => 'test@corp.com',
            'password'   => bcrypt('secret123'),
            'role'       => 'owner',
        ]);

        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email'    => 'test@corp.com',
            'password' => 'secret123',
        ]);

        $loginResponse->assertStatus(200);
        $token = $loginResponse->json('data.token');

        $meResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/auth/me');

        $meResponse->assertStatus(200)
            ->assertJsonPath('data.email', 'test@corp.com');
    }

    public function test_can_create_and_list_folders(): void
    {
        $plan = SubscriptionPlan::where('slug', 'free')->first();
        $company = Company::create(['name' => 'Import Co', 'email' => 'import@co.com', 'plan_id' => $plan->id]);
        $user = User::create([
            'company_id' => $company->id,
            'name'       => 'Importer',
            'email'      => 'importer@co.com',
            'password'   => bcrypt('secret123'),
            'role'       => 'owner',
        ]);

        $token = $user->createToken('test')->plainTextToken;

        // Create Folder
        $folderRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/folders', [
                'name'        => 'Factures Fournisseurs',
                'color'       => '#10b981',
                'icon'        => 'receipt',
                'description' => 'Supplier invoices',
            ]);

        $folderRes->assertStatus(201)
            ->assertJsonPath('data.name', 'Factures Fournisseurs');

        // List Folders
        $listRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/folders');

        $listRes->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_can_upload_and_download_document(): void
    {
        Storage::fake('local');

        $plan = SubscriptionPlan::where('slug', 'professional')->first();
        $company = Company::create(['name' => 'Customs SARL', 'email' => 'customs@sarl.com', 'plan_id' => $plan->id]);
        $user = User::create([
            'company_id' => $company->id,
            'name'       => 'Customs Officer',
            'email'      => 'officer@sarl.com',
            'password'   => bcrypt('secret123'),
            'role'       => 'owner',
        ]);

        $token = $user->createToken('test')->plainTextToken;

        $file = UploadedFile::fake()->create('customs_declaration.pdf', 500, 'application/pdf');

        $uploadRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/documents', [
                'title'       => 'Declaration Douaniere 2026',
                'description' => 'Port de Casablanca',
                'file'        => $file,
            ]);

        $uploadRes->assertStatus(201)
            ->assertJsonPath('data.title', 'Declaration Douaniere 2026')
            ->assertJsonPath('data.file_type', 'pdf');

        $docId = $uploadRes->json('data.id');

        // Download link
        $downloadRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/v1/documents/{$docId}/download");

        $downloadRes->assertStatus(200)
            ->assertJsonStructure(['success', 'download_url', 'file_name']);
    }

    public function test_dashboard_statistics(): void
    {
        $plan = SubscriptionPlan::where('slug', 'professional')->first();
        $company = Company::create(['name' => 'Stats Co', 'email' => 'stats@co.com', 'plan_id' => $plan->id]);
        $user = User::create([
            'company_id' => $company->id,
            'name'       => 'CEO',
            'email'      => 'ceo@stats.com',
            'password'   => bcrypt('secret123'),
            'role'       => 'owner',
        ]);

        $token = $user->createToken('test')->plainTextToken;

        $dashboardRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/company/dashboard');

        $dashboardRes->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'stats' => ['total_documents', 'total_folders', 'total_users'],
                    'storage' => ['used_bytes', 'used_gb', 'limit_gb', 'usage_percent'],
                    'plan',
                    'recent_documents',
                ],
            ]);
    }
}
