<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');                         // Free, Starter, Professional, Enterprise
            $table->string('slug')->unique();               // free, starter, professional, enterprise
            $table->integer('max_storage_gb');              // -1 = unlimited
            $table->integer('max_users');                   // -1 = unlimited
            $table->integer('max_documents');               // -1 = unlimited
            $table->integer('max_file_size_mb')->default(10);
            $table->decimal('price_monthly', 8, 2)->default(0);
            $table->decimal('price_yearly', 8, 2)->default(0);
            $table->json('features')->nullable();           // extra feature list
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
