<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('media_folders', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('media_folders')->restrictOnDelete();
            $table->string('name', 150);
            $table->string('source_path', 500)->nullable()->unique();
            $table->timestamps();
            $table->unique(['parent_id', 'name']);
        });
        Schema::create('media_files', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('folder_id')->nullable()->constrained('media_folders')->restrictOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->string('disk', 30)->default('public');
            $table->string('path', 500)->unique();
            $table->string('name');
            $table->string('mime_type', 150);
            $table->string('extension', 10);
            $table->string('kind', 20);
            $table->unsignedBigInteger('size');
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->string('alt_text', 500)->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['folder_id', 'deleted_at', 'created_at']);
        });
        Schema::create('media_favorites', function (Blueprint $table): void {
            $table->foreignId('admin_id')->constrained('admins')->cascadeOnDelete();
            $table->foreignId('media_file_id')->constrained('media_files')->cascadeOnDelete();
            $table->primary(['admin_id', 'media_file_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media_favorites');
        Schema::dropIfExists('media_files');
        Schema::dropIfExists('media_folders');
    }
};
