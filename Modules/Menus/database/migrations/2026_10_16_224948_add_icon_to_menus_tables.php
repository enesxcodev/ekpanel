<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menus', function (Blueprint $table): void {
            $table->string('icon')->nullable();
        });
        Schema::table('menu_items', function (Blueprint $table): void {
            $table->string('icon')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('menu_items', function (Blueprint $table): void {
            $table->dropColumn('icon');
        });
        Schema::table('menus', function (Blueprint $table): void {
            $table->dropColumn('icon');
        });
    }
};
