<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('languages')) {
            throw new RuntimeException('Branches çeviri migration\'ı için languages tablosu gerekir; önce dil altyapısını kurun.');
        }

        Schema::create('branch_translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('lang_id')->constrained('languages')->restrictOnDelete();
            $table->string('name');
            $table->text('address_detail')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('email')->nullable();
            $table->string('website', 2048)->nullable();
            $table->string('working_hours')->nullable();
            $table->string('map_url', 2048)->nullable();
            $table->timestamps();
            $table->unique(['branch_id', 'lang_id']);
        });
        Schema::create('branch_category_translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('branch_category_id')->constrained('branch_categories')->cascadeOnDelete();
            $table->foreignId('lang_id')->constrained('languages')->restrictOnDelete();
            $table->string('name', 150);
            $table->timestamps();
            $table->unique(['branch_category_id', 'lang_id']);
        });

        DB::table('languages')->insertOrIgnore(['code' => 'tr', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $languageId = DB::table('languages')->where('code', 'tr')->value('id');
        if ($languageId === null) {
            throw new RuntimeException('Türkçe dil kaydı oluşturulamadı; Branches çevirileri aktarılmadı.');
        }

        DB::table('branches')->orderBy('id')->chunkById(200, function ($branches) use ($languageId): void {
            foreach ($branches as $branch) {
                DB::table('branch_translations')->insert([
                    'branch_id' => $branch->id, 'lang_id' => $languageId, 'name' => $branch->name,
                    'address_detail' => $branch->address_detail, 'phone' => $branch->phone, 'email' => $branch->email,
                    'website' => $branch->website, 'working_hours' => $branch->working_hours, 'map_url' => $branch->map_url,
                    'created_at' => $branch->created_at, 'updated_at' => $branch->updated_at,
                ]);
            }
        });
        DB::table('branch_categories')->orderBy('id')->chunkById(200, function ($categories) use ($languageId): void {
            foreach ($categories as $category) {
                DB::table('branch_category_translations')->insert([
                    'branch_category_id' => $category->id, 'lang_id' => $languageId, 'name' => $category->name,
                    'created_at' => $category->created_at, 'updated_at' => $category->updated_at,
                ]);
            }
        });

        if (DB::table('branches')->count() !== DB::table('branch_translations')->where('lang_id', $languageId)->count()
            || DB::table('branch_categories')->count() !== DB::table('branch_category_translations')->where('lang_id', $languageId)->count()) {
            throw new RuntimeException('Branches Türkçe çeviri backfill kayıt sayıları eşleşmedi; migration durduruldu.');
        }
    }

    public function down(): void
    {
        throw new RuntimeException('Branches çevirileri otomatik geri alınamaz; çeviri kaybını önlemek için ileri migration kullanın.');
    }
};
