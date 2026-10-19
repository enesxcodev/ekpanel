<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('galeriler_items', function (Blueprint $table): void {
            $table->foreignId('galeriler_category_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        if (DB::table('galeriler_items')->whereNull('galeriler_category_id')->exists()) {
            throw new RuntimeException('Kategori zorunluluğunu geri getirmeden önce kategorisiz galeri kayıtlarına kategori atayın.');
        }

        Schema::table('galeriler_items', function (Blueprint $table): void {
            $table->foreignId('galeriler_category_id')->nullable(false)->change();
        });
    }
};
