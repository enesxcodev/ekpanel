<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $duplicateSlug = DB::table('documents')->select('slug')->groupBy('slug')->havingRaw('COUNT(*) > 1')->first();
        if ($duplicateSlug !== null) {
            throw new RuntimeException('Belge kısa bağlantısı birden fazla kullanılıyor: '.$duplicateSlug->slug.'. Çeviri geçişinden önce çakışmayı giderin.');
        }

        Schema::create('document_translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->cascadeOnDelete();
            $table->foreignId('lang_id')->constrained('languages')->restrictOnDelete();
            $table->string('name');
            $table->longText('content')->nullable();
            $table->string('slug');
            $table->json('extra_fields')->nullable();
            $table->timestamps();
            $table->unique(['document_id', 'lang_id']);
            $table->unique(['lang_id', 'slug']);
        });
        Schema::create('document_category_translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('document_category_id')->constrained('document_categories')->cascadeOnDelete();
            $table->foreignId('lang_id')->constrained('languages')->restrictOnDelete();
            $table->string('name');
            $table->json('extra_fields')->nullable();
            $table->timestamps();
            $table->unique(['document_category_id', 'lang_id']);
        });

        DB::table('languages')->insertOrIgnore(['code' => 'tr', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('languages')->where('code', 'tr')->update(['is_active' => true]);
        $languageId = DB::table('languages')->where('code', 'tr')->value('id');
        if (! $languageId) {
            throw new RuntimeException('Belgeler çeviri geçişi için Türkçe dili hazırlanamadı.');
        }

        foreach ([
            ['documents', 'document_translations', 'document_id', ['name', 'content', 'slug', 'extra_fields'], 'Modules\\Documents\\Models\\Document', 'admin.documents.translations.edit'],
            ['document_categories', 'document_category_translations', 'document_category_id', ['name', 'extra_fields'], 'Modules\\Documents\\Models\\DocumentCategory', 'admin.documents.categories.translations.edit'],
        ] as [$source, $target, $foreignKey, $fields, $owner, $route]) {
            DB::table($source)->orderBy('id')->chunkById(200, function ($records) use ($target, $foreignKey, $fields, $languageId, $owner, $route): void {
                foreach ($records as $record) {
                    $values = [$foreignKey => $record->id, 'lang_id' => $languageId, 'created_at' => $record->created_at, 'updated_at' => $record->updated_at];
                    foreach ($fields as $field) {
                        $values[$field] = $record->{$field};
                    }
                    $translationId = DB::table($target)->insertGetId($values);
                    if (Schema::hasTable('media_usages')) {
                        DB::table('media_usages')->where('owner_type', $owner)->where('owner_id', $record->id)
                            ->where('field', 'like', 'extra_fields.%')
                            ->update(['owner_type' => $owner.'Translation', 'owner_id' => $translationId, 'route_name' => $route]);
                    }
                }
            });
            $sourceCount = DB::table($source)->count();
            $translationCount = DB::table($target)->where('lang_id', $languageId)->count();
            $missingTranslations = DB::table($source)->leftJoin($target, function ($join) use ($source, $target, $foreignKey, $languageId): void {
                $join->on($target.'.'.$foreignKey, '=', $source.'.id')->where($target.'.lang_id', '=', $languageId);
            })->whereNull($target.'.id')->count();
            if ($sourceCount !== $translationCount || $missingTranslations > 0) {
                throw new RuntimeException('Belgeler Türkçe çeviri kayıt sayısı kaynak kayıtlarla eşleşmedi: '.$source.'.');
            }
        }

        Schema::table('documents', function (Blueprint $table): void {
            $table->dropUnique(['slug']);
            $table->dropColumn(['name', 'content', 'slug', 'extra_fields']);
        });
        Schema::table('document_categories', function (Blueprint $table): void {
            $table->dropColumn(['name', 'extra_fields']);
        });
    }

    public function down(): void
    {
        throw new RuntimeException('Belgeler çevirileri otomatik geri alınamaz; veri kaybını önlemek için ileri migration kullanın.');
    }
};
