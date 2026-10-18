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
        Schema::create('module_installations', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('actor');
            $table->string('active_key')->nullable()->unique();
            $table->string('status');
            $table->unsignedInteger('current_index')->default(0);
            $table->text('entries');
            $table->text('results');
            $table->text('error')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('module_installations');
    }
};
