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
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_category_id')->constrained('page_categories')->cascadeOnDelete();
            $table->string('title');
            $table->string('short_description', 500)->nullable();
            $table->longText('content')->nullable();
            $table->string('image')->nullable();
            $table->string('slug');
            $table->string('status', 20)->default('draft');
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('extra_fields')->nullable();
            $table->timestamps();
            $table->unique(['page_category_id', 'slug']);
            $table->index(['page_category_id', 'status', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
