<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Document;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Database\Seeders\SubscriptionPlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MultiTenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(SubscriptionPlanSeeder::class);
        Storage::fake('local');
    }

    private function makeOwner(string $name): array
    {
        $plan = SubscriptionPlan::where('slug', 'free')->first();

        $company = Company::create([
            'name' => "{$name} Co",
            'email' => strtolower($name).'@test.com',
            'plan_id' => $plan->id,
        ]);

        $user = User::factory()->create([
            'company_id' => $company->id,
            'name' => $name,
            'role' => User::ROLE_OWNER,
        ]);

        return [$user, $company];
    }

    public function test_user_cannot_access_another_companys_document(): void
    {
        [$ownerA, $companyA] = $this->makeOwner('Alpha');
        [$ownerB] = $this->makeOwner('Beta');

        Sanctum::actingAs($ownerA);

        $this->postJson('/api/v1/documents', [
            'title' => 'Secret Document',
            'file' => UploadedFile::fake()->create('secret.pdf', 10),
        ])->assertStatus(201);

        $document = Document::where('company_id', $companyA->id)->firstOrFail();

        // Company B tries every action on A's document.
        Sanctum::actingAs($ownerB);

        $this->getJson("/api/v1/documents/{$document->id}")->assertStatus(404);
        $this->putJson("/api/v1/documents/{$document->id}", ['title' => 'Hijacked'])->assertStatus(404);
        $this->deleteJson("/api/v1/documents/{$document->id}")->assertStatus(404);
        $this->get("/api/v1/documents/{$document->id}/download")->assertStatus(404);

        // Document still intact.
        $this->assertDatabaseHas('documents', ['id' => $document->id, 'title' => 'Secret Document']);
    }

    public function test_user_cannot_list_another_companys_documents(): void
    {
        [$ownerA] = $this->makeOwner('Alpha');
        [$ownerB] = $this->makeOwner('Beta');

        Sanctum::actingAs($ownerA);

        $this->postJson('/api/v1/documents', [
            'title' => 'A Doc',
            'file' => UploadedFile::fake()->create('a.pdf', 10),
        ])->assertStatus(201);

        Sanctum::actingAs($ownerB);

        $this->getJson('/api/v1/documents')
            ->assertStatus(200)
            ->assertJsonCount(0, 'data');
    }

    public function test_user_cannot_access_another_companys_folder(): void
    {
        [$ownerA] = $this->makeOwner('Alpha');
        [$ownerB] = $this->makeOwner('Beta');

        Sanctum::actingAs($ownerA);

        $folderA = $this->postJson('/api/v1/folders', ['name' => 'Alpha Folder'])
            ->assertStatus(201)
            ->json('data.id');

        Sanctum::actingAs($ownerB);

        $this->getJson("/api/v1/folders/{$folderA}")->assertStatus(404);
        $this->deleteJson("/api/v1/folders/{$folderA}")->assertStatus(404);
    }

    public function test_employee_cannot_see_another_companys_dashboard(): void
    {
        [$ownerA, $companyA] = $this->makeOwner('Alpha');
        [$ownerB] = $this->makeOwner('Beta');

        $employeeB = User::factory()->create([
            'company_id' => $ownerB->company_id,
            'role' => User::ROLE_EMPLOYEE,
        ]);

        Sanctum::actingAs($ownerA);
        $this->postJson('/api/v1/documents', [
            'title' => 'A Secret',
            'file' => UploadedFile::fake()->create('s.pdf', 10),
        ])->assertStatus(201);

        Sanctum::actingAs($employeeB);

        $response = $this->getJson('/api/v1/company/dashboard')->assertStatus(200);

        $this->assertSame(0, $response->json('data.stats.total_documents'));
    }
}
