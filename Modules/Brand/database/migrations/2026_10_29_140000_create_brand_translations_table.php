<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $createdTranslations = ! Schema::hasTable('brand_translations');
        if ($createdTranslations) {
            Schema::create('brand_translations', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('brand_id')->constrained('brands')->cascadeOnDelete();
                $table->foreignId('lang_id')->constrained('languages')->restrictOnDelete();
                $table->string('name');
                $table->string('short_description', 500)->nullable();
                $table->json('extra_fields')->nullable();
                $table->timestamps();
                $table->unique(['brand_id', 'lang_id']);
            });
        }

        DB::table('languages')->insertOrIgnore(['code' => 'tr', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('languages')->where('code', 'tr')->update(['is_active' => true, 'updated_at' => now()]);
        $languageId = DB::table('languages')->where('code', 'tr')->value('id');
        $hasLegacyFields = Schema::hasColumn('brands', 'name') && Schema::hasColumn('brands', 'short_description') && Schema::hasColumn('brands', 'extra_fields');
        if ($hasLegacyFields) {
            DB::table('brands')->orderBy('id')->chunkById(200, function ($brands) use ($languageId): void {
                foreach ($brands as $brand) {
                    DB::table('brand_translations')->insertOrIgnore([
                        'brand_id' => $brand->id, 'lang_id' => $languageId, 'name' => $brand->name,
                        'short_description' => $brand->short_description, 'extra_fields' => $brand->extra_fields,
                        'created_at' => $brand->created_at, 'updated_at' => $brand->updated_at,
                    ]);
                    if (Schema::hasTable('media_usages')) {
                        $translationId = DB::table('brand_translations')->where('brand_id', $brand->id)->where('lang_id', $languageId)->value('id');
                        DB::table('media_usages')->where('owner_type', 'Modules\\Brand\\Models\\Brand')->where('owner_id', $brand->id)->where('field', 'like', 'extra_fields.%')->update([
                            'owner_type' => 'Modules\\Brand\\Models\\BrandTranslation', 'owner_id' => $translationId, 'route_name' => 'admin.brand.edit',
                        ]);
                    }
                }
            });
            $missing = DB::table('brands')->whereNotExists(function ($query) use ($languageId): void {
                $query->selectRaw('1')->from('brand_translations')->whereColumn('brand_translations.brand_id', 'brands.id')->where('brand_translations.lang_id', $languageId);
            })->exists();
            if ($missing) {
                throw new RuntimeException('Brand Türkçe çeviri aktarımında kayıt sayıları eşleşmedi.');
            }
            Schema::table('brands', fn (Blueprint $table) => $table->dropColumn(['name', 'short_description', 'extra_fields']));
        } elseif ($createdTranslations && DB::table('brands')->exists()) {
            throw new RuntimeException('Brand çeviri tablosu var ancak legacy alanlar bulunamadı; mevcut kayıtları doğrulayın.');
        }
    }

    public function down(): void
    {
        throw new RuntimeException('Brand çevirileri otomatik geri alınamaz; veri kaybını önlemek için ileri migration kullanın.');
    }
};
