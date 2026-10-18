<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gallery_categories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('gallery_categories')->restrictOnDelete();
            $table->string('name');
            $table->string('icon', 100)->nullable();
            $table->string('short_description', 500)->nullable();
            $table->json('extra_fields')->nullable();
            $table->timestamps();
        });
        Schema::create('gallery_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('gallery_category_id')->constrained('gallery_categories')->restrictOnDelete();
            $table->string('type', 10);
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->foreignId('image_media_id')->nullable()->constrained('media_files')->restrictOnDelete();
            $table->string('video_url', 2048)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('extra_fields')->nullable();
            $table->timestamps();
            $table->index(['gallery_category_id', 'sort_order', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gallery_items');
        Schema::dropIfExists('gallery_categories');
    }
};
