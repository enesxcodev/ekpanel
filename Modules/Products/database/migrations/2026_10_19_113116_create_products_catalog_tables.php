<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('extra_fields')->nullable();
        });
        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_category_id')->nullable()->constrained('product_categories')->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('short_description')->nullable();
            $table->text('content')->nullable();
            $table->string('image')->nullable();
            $table->foreignId('image_media_id')->nullable()->constrained('media_files')->restrictOnDelete();
            $table->foreignId('site_form_id')->nullable()->constrained('site_forms')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('extra_fields')->nullable();
            $table->timestamps();
        });
        Schema::create('product_images', function (Blueprint $table): void {
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('media_file_id')->constrained('media_files')->restrictOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->primary(['product_id', 'media_file_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_images');
        Schema::dropIfExists('products');
        Schema::dropIfExists('product_categories');
    }
};
