<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branch_categories', function (Blueprint $table): void {
            $table->id();
            $table->string('slug', 80)->unique();
            $table->string('name', 150);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('branches', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('branch_category_id')->constrained('branch_categories')->restrictOnDelete();
            $table->string('name', 255);
            $table->unsignedBigInteger('city_id')->nullable();
            $table->unsignedBigInteger('town_id')->nullable();
            $table->unsignedBigInteger('district_id')->nullable();
            $table->unsignedBigInteger('neighbourhood_id')->nullable();
            $table->unsignedBigInteger('street_id')->nullable();
            $table->text('address_detail')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('website', 2048)->nullable();
            $table->string('working_hours', 255)->nullable();
            $table->string('map_url', 2048)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->json('extra_fields')->nullable();
            $table->timestamps();
            $table->index(['branch_category_id', 'is_active', 'sort_order', 'id']);
            $table->index(['city_id', 'town_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branches');
        Schema::dropIfExists('branch_categories');
    }
};
