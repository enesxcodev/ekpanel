<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('setting_values', function (Blueprint $table): void {
            $table->foreignId('media_id')->nullable()->constrained('media_files')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('setting_values')->join('media_files', 'setting_values.media_id', '=', 'media_files.id')
            ->select('setting_values.id', 'media_files.path')->orderBy('setting_values.id')->get()
            ->each(function (object $value): void {
                DB::table('setting_values')->where('id', $value->id)->update([
                    'value' => Crypt::encryptString(json_encode(['value' => $value->path], JSON_THROW_ON_ERROR)),
                ]);
            });
        Schema::table('setting_values', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('media_id');
        });
    }
};
