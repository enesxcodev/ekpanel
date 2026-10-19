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
        Schema::create('popups', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->longText('content');
            $table->foreignId('image_media_id')->nullable()->constrained('media_files')->restrictOnDelete();
            $table->boolean('is_active')->default(true);
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->string('repeat_policy')->default('never_after_close');
            $table->unsignedInteger('repeat_interval')->nullable();
            $table->string('repeat_unit')->nullable();
            $table->json('extra_fields')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('popups');
    }
};
