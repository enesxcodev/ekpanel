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
        Schema::create('admin_dashboard_preferences', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('admin_id')->unique()->constrained('admins')->cascadeOnDelete();
            $table->json('widget_order');
            $table->unsignedInteger('revision')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_dashboard_preferences');
    }
};
