<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon', 100)->nullable();
            $table->json('extra_fields')->nullable();
            $table->timestamps();
        });
        Schema::create('projects', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('project_category_id')->constrained('project_categories')->restrictOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->date('project_date')->nullable();
            $table->longText('content')->nullable();
            $table->boolean('is_active')->default(false);
            $table->foreignId('cover_media_id')->nullable()->constrained('media_files')->restrictOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('extra_fields')->nullable();
            $table->timestamps();
            $table->index(['is_active', 'sort_order', 'id']);
        });
        Schema::create('project_images', function (Blueprint $table): void {
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('media_file_id')->constrained('media_files')->restrictOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->primary(['project_id', 'media_file_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_images');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('project_categories');
    }
};
