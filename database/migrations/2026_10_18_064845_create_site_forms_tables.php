<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_forms', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('key', 100)->unique();
            $table->boolean('is_active')->default(false);
            $table->string('submit_label', 100)->default('Gönder');
            $table->string('success_message', 500)->default('Başvurunuz alındı. Teşekkür ederiz.');
            $table->string('notification_email')->nullable();
            $table->string('css_class', 255)->nullable();
            $table->timestamps();
        });
        Schema::create('form_fields', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('site_form_id')->constrained('site_forms')->cascadeOnDelete();
            $table->string('key', 100);
            $table->string('label', 150);
            $table->string('input_type', 20);
            $table->boolean('required')->default(false);
            $table->string('placeholder', 255)->nullable();
            $table->string('description', 500)->nullable();
            $table->string('width', 10)->default('full');
            $table->string('css_class', 255)->nullable();
            $table->string('input_class', 255)->nullable();
            $table->string('role', 20)->nullable();
            $table->json('options')->nullable();
            $table->json('validation')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['site_form_id', 'key']);
        });
        Schema::create('form_submissions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('site_form_id')->constrained('site_forms')->restrictOnDelete();
            $table->string('form_name');
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('status', 20)->default('new');
            $table->json('answers');
            $table->timestamps();
            $table->index(['site_form_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_submissions');
        Schema::dropIfExists('form_fields');
        Schema::dropIfExists('site_forms');
    }
};
