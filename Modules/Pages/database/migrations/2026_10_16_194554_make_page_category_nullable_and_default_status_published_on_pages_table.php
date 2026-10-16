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
        Schema::table('pages', function (Blueprint $table): void {
            $table->foreignId('page_category_id')->nullable()->change();
            $table->string('status', 20)->default('published')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table): void {
            $table->foreignId('page_category_id')->nullable(false)->change();
            $table->string('status', 20)->default('draft')->change();
        });
    }
};
