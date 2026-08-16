<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Document;
use App\Models\Folder;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Database\Seeders\SubscriptionPlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DocumentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(SubscriptionPlanSeeder::class);
        Storage::fake('local');
    }

    private function makeUser(): User
    {
        $plan = SubscriptionPlan::where('slug', 'free')->first();
        $company = Company::create([
            'name' => 'Test Co',
            'email' => fake()->unique()->safeEmail(),
            'plan_id' => $plan->id,
        ]);

        return User::factory()->create([
            'company_id' => $company->id,
            'role' => User::ROLE_OWNER,
            'is_active' => true,
        ]);
    }

    public function test_user_can_upload_a_document(): void
    {
        $user = $this->makeUser();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/documents', [
            'title' => 'Invoice #123',
            'file' => UploadedFile::fake()->create('invoice.pdf', 100),
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.title', 'Invoice #123')
            ->assertJsonPath('data.file_type', 'pdf');

        $document = Document::firstOrFail();
        $this->assertSame($user->company_id, $document->company_id);
        $this->assertSame($document->size_bytes, $user->company->fresh()->storage_used_bytes);

        $this->assertDatabaseHas('document_files', ['document_id' => $document->id, 'page_number' => 1]);
        $this->assertDatabaseHas('activity_logs', ['action' => 'uploaded', 'subject_id' => $document->id]);
    }

    public function test_user_can_upload_multiple_pages(): void
    {
        $user = $this->makeUser();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/documents', [
            'title' => 'Contract',
            'files' => [
                UploadedFile::fake()->image('page1.jpg'),
                UploadedFile::fake()->image('page2.jpg'),
                UploadedFile::fake()->image('page3.jpg'),
            ],
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.pages_count', 3);

        $document = Document::firstOrFail();
        $this->assertSame(3, $document->files()->count());
    }

    public function test_user_can_list_and_search_documents(): void
    {
        $user = $this->makeUser();
        Sanctum::actingAs($user);

        $folder = Folder::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'name' => 'Factures',
        ]);

        $this->postJson('/api/v1/documents', [
            'title' => 'Invoice Alpha',
            'folder_id' => $folder->id,
            'file' => UploadedFile::fake()->create('a.pdf', 10),
        ])->assertStatus(201);

        $this->postJson('/api/v1/documents', [
            'title' => 'Delivery Note Beta',
            'file' => UploadedFile::fake()->create('b.pdf', 10),
        ])->assertStatus(201);

        $this->getJson('/api/v1/documents?folder_id='.$folder->id)
            ->assertStatus(200)
            ->assertJsonCount(1, 'data');

        $this->getJson('/api/v1/documents/search?q=Alpha')
            ->assertStatus(200)
            ->assertJsonPath('data.0.title', 'Invoice Alpha');
    }

    public function test_user_can_rename_and_delete_document(): void
    {
        $user = $this->makeUser();
        Sanctum::actingAs($user);

        $upload = $this->postJson('/api/v1/documents', [
            'title' => 'Old Title',
            'file' => UploadedFile::fake()->create('old.pdf', 50),
        ])->assertStatus(201);

        $id = $upload->json('data.id');

        $this->putJson("/api/v1/documents/$id", ['title' => 'New Title'])
            ->assertStatus(200)
            ->assertJsonPath('data.title', 'New Title');

        $this->deleteJson("/api/v1/documents/$id")
            ->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('documents', ['id' => $id]);
        $this->assertSame(0, $user->company->fresh()->storage_used_bytes);
    }

    public function test_document_cannot_be_moved_to_folder_of_another_company(): void
    {
        $user = $this->makeUser();
        $other = $this->makeUser();

        Sanctum::actingAs($user);

        $otherFolder = Folder::create([
            'company_id' => $other->company_id,
            'user_id' => $other->id,
            'name' => 'Other Folder',
        ]);

        $upload = $this->postJson('/api/v1/documents', [
            'title' => 'Doc',
            'file' => UploadedFile::fake()->create('d.pdf', 10),
        ])->assertStatus(201);

        $this->putJson('/api/v1/documents/'.$upload->json('data.id'), [
            'folder_id' => $otherFolder->id,
        ])->assertStatus(422);
    }

    public function test_download_streams_file(): void
    {
        $user = $this->makeUser();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/documents', [
            'title' => 'Invoice',
            'file' => UploadedFile::fake()->create('invoice.pdf', 100),
        ])->assertStatus(201);

        $document = Document::firstOrFail();

        $this->get("/api/v1/documents/{$document->id}/download")
            ->assertOk();

        $this->assertDatabaseHas('activity_logs', ['action' => 'downloaded', 'subject_id' => $document->id]);
    }
}
