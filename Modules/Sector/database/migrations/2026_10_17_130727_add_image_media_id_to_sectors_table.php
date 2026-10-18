<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sectors', function (Blueprint $table): void {
            $table->foreignId('image_media_id')->nullable()->constrained('media_files')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sectors', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('image_media_id');
        });
    }
};
