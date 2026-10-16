<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('setting_sections', function (Blueprint $table): void {
            $table->id();
            $table->string('key', 80)->unique();
            $table->string('label', 150);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('setting_definitions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('section_id')->constrained('setting_sections')->restrictOnDelete();
            $table->string('key', 150)->unique();
            $table->string('label', 150);
            $table->text('description')->nullable();
            $table->string('input_type', 30);
            $table->string('value_type', 30);
            $table->json('options')->nullable();
            $table->json('attributes')->nullable();
            $table->json('validation')->nullable();
            $table->json('default_value')->nullable();
            $table->boolean('is_sensitive')->default(false);
            $table->boolean('is_system')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('setting_values', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('setting_definition_id')->unique()->constrained()->cascadeOnDelete();
            $table->text('value');
            $table->foreignId('updated_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('setting_values');
        Schema::dropIfExists('setting_definitions');
        Schema::dropIfExists('setting_sections');
    }
};
