<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sectors', function (Blueprint $table): void {
            $table->string('slug')->nullable()->unique();
            $table->unsignedInteger('sort_order')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('sectors', function (Blueprint $table): void {
            $table->dropUnique(['slug']);
            $table->dropColumn(['slug', 'sort_order']);
        });
    }
};
