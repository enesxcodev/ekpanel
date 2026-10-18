<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table): void {
            $table->string('slug')->nullable()->unique();
            $table->unsignedInteger('sort_order')->nullable();
        });
        Schema::table('service_categories', function (Blueprint $table): void {
            $table->unsignedInteger('sort_order')->nullable();
            $table->boolean('is_active')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table): void {
            $table->dropUnique(['slug']);
            $table->dropColumn(['slug', 'sort_order']);
        });
        Schema::table('service_categories', function (Blueprint $table): void {
            $table->dropColumn(['sort_order', 'is_active']);
        });
    }
};
