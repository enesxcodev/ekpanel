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
            throw new RuntimeException('Sector çevirileri için languages tablosu bulunamadı.');
        }

        DB::table('languages')->insertOrIgnore(['code' => 'tr', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $languageId = DB::table('languages')->where('code', 'tr')->value('id');
        DB::table('languages')->where('id', $languageId)->update(['is_active' => true, 'updated_at' => now()]);

        if (! Schema::hasTable('sector_translations')) {
            Schema::create('sector_translations', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('sector_id')->constrained('sectors')->cascadeOnDelete();
                $table->foreignId('lang_id')->constrained('languages')->restrictOnDelete();
                $table->string('title');
                $table->string('slug')->nullable();
                $table->text('short_description')->nullable();
                $table->text('content')->nullable();
                $table->string('meta_title')->nullable();
                $table->text('meta_keywords')->nullable();
                $table->text('meta_description')->nullable();
                $table->json('extra_fields')->nullable();
                $table->timestamps();
                $table->unique(['sector_id', 'lang_id']);
                $table->unique(['lang_id', 'slug']);
            });
        }

        if (! Schema::hasColumn('sectors', 'title')) {
            return;
        }

        DB::table('sectors')->orderBy('id')->chunkById(200, function ($sectors) use ($languageId): void {
            foreach ($sectors as $sector) {
                DB::table('sector_translations')->insertOrIgnore([
                    'sector_id' => $sector->id,
                    'lang_id' => $languageId,
                    'title' => $sector->title,
                    'slug' => $sector->slug,
                    'short_description' => $sector->short_description,
                    'content' => $sector->content,
                    'meta_title' => $sector->meta_title,
                    'meta_keywords' => $sector->meta_keywords,
                    'meta_description' => $sector->meta_description,
                    'extra_fields' => $sector->extra_fields,
                    'created_at' => $sector->created_at,
                    'updated_at' => $sector->updated_at,
                ]);
                if (Schema::hasTable('media_usages')) {
                    DB::table('media_usages')->where('owner_type', 'Modules\\Sector\\Models\\Sector')->where('owner_id', $sector->id)->where('field', 'like', 'extra_fields.%')->update([
                        'owner_type' => 'Modules\\Sector\\Models\\SectorTranslation',
                        'owner_id' => DB::table('sector_translations')->where('sector_id', $sector->id)->where('lang_id', $languageId)->value('id'),
                        'route_name' => 'admin.sector.translations.edit',
                    ]);
                }
            }
        });

        $sourceCount = DB::table('sectors')->count();
        $translationCount = DB::table('sector_translations')->where('lang_id', $languageId)->count();
        if ($sourceCount !== $translationCount || DB::table('sectors')->whereNotIn('id', DB::table('sector_translations')->select('sector_id')->where('lang_id', $languageId))->exists()) {
            throw new RuntimeException('Sector Türkçe çeviri aktarımı doğrulanamadı; eski sütunlar korunuyor.');
        }

        if (Schema::hasColumn('sectors', 'slug')) {
            Schema::table('sectors', function (Blueprint $table): void {
                $table->dropUnique(['slug']);
            });
        }
        Schema::table('sectors', function (Blueprint $table): void {
            $table->dropColumn(['title', 'slug', 'short_description', 'content', 'meta_title', 'meta_keywords', 'meta_description', 'extra_fields']);
        });
    }

    public function down(): void
    {
        throw new RuntimeException('Sector çevirileri otomatik geri alınamaz; veri kaybını önlemek için ileri migration kullanın.');
    }
};
