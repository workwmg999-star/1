<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('folder_id')->nullable()->constrained('folders')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('file_path');                     // main file (PDF or image)
            $table->string('file_type')->default('pdf');     // pdf | image
            $table->string('mime_type')->default('application/pdf');
            $table->bigInteger('size_bytes')->default(0);
            $table->integer('pages_count')->default(1);
            $table->string('thumbnail_path')->nullable();    // first page preview
            $table->json('metadata')->nullable();            // tags, custom fields
            $table->boolean('is_archived')->default(false);
            $table->timestamps();

            $table->index(['company_id', 'folder_id']);
            $table->index(['company_id', 'created_at']);
            if (Schema::getConnection()->getDriverName() !== 'sqlite') {
                $table->fullText('title');
            }
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
