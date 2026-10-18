<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_usages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('media_id')->constrained('media_files')->restrictOnDelete();
            $table->string('owner_type', 190);
            $table->unsignedBigInteger('owner_id');
            $table->string('field', 80);
            $table->string('label', 500);
            $table->string('route_name', 150)->nullable();
            $table->timestamps();
            $table->unique(['owner_type', 'owner_id', 'field']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_usages');
    }
};
