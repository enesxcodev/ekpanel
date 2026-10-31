<?php

use App\Models\Settings\Language;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('faq_translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('faq_id')->constrained('faqs')->cascadeOnDelete();
            $table->foreignId('lang_id')->constrained('languages')->restrictOnDelete();
            $table->string('name');
            $table->longText('content');
            $table->json('extra_fields')->nullable();
            $table->timestamps();
            $table->unique(['faq_id', 'lang_id']);
        });
        Schema::create('faq_category_translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('faq_category_id')->constrained('faq_categories')->cascadeOnDelete();
            $table->foreignId('lang_id')->constrained('languages')->restrictOnDelete();
            $table->string('name');
            $table->json('extra_fields')->nullable();
            $table->timestamps();
            $table->unique(['faq_category_id', 'lang_id']);
            $table->unique(['lang_id', 'name']);
        });

        $language = Language::query()->firstOrCreate(['code' => 'tr'], ['is_active' => true]);
        if (! $language->is_active) {
            $language->is_active = true;
            $language->save();
        }
        $languageId = $language->id;
        foreach ([['faqs', 'faq_translations', 'faq_id', ['name', 'content', 'extra_fields'], 'Modules\\Faqs\\Models\\Faq', 'admin.faqs.translations.edit'], ['faq_categories', 'faq_category_translations', 'faq_category_id', ['name', 'extra_fields'], 'Modules\\Faqs\\Models\\FaqCategory', 'admin.faqs.categories.translations.edit']] as [$source, $target, $foreignKey, $fields, $owner, $route]) {
            DB::table($source)->orderBy('id')->chunkById(200, function ($records) use ($target, $foreignKey, $fields, $languageId, $owner, $route): void {
                foreach ($records as $record) {
                    $values = [$foreignKey => $record->id, 'lang_id' => $languageId, 'created_at' => $record->created_at, 'updated_at' => $record->updated_at];
                    foreach ($fields as $field) {
                        $values[$field] = $record->{$field};
                    }
                    $translationId = DB::table($target)->insertGetId($values);
                    if (Schema::hasTable('media_usages')) {
                        DB::table('media_usages')->where('owner_type', $owner)->where('owner_id', $record->id)->where('field', 'like', 'extra_fields.%')->update(['owner_type' => $owner.'Translation', 'owner_id' => $translationId, 'route_name' => $route]);
                    }
                }
            });
            $sourceCount = DB::table($source)->count();
            $translationCount = DB::table($target)->where('lang_id', $languageId)->count();
            $missingTranslations = DB::table($source)->leftJoin($target, function ($join) use ($source, $target, $foreignKey, $languageId): void {
                $join->on($target.'.'.$foreignKey, '=', $source.'.id')->where($target.'.lang_id', '=', $languageId);
            })->whereNull($target.'.id')->count();
            if ($sourceCount !== $translationCount || $missingTranslations > 0) {
                throw new RuntimeException('SSS Türkçe çeviri kayıt sayısı kaynak kayıtlarla eşleşmedi.');
            }
        }

        Schema::table('faqs', function (Blueprint $table): void {
            $table->dropColumn(['name', 'content', 'extra_fields']);
        });
        Schema::table('faq_categories', function (Blueprint $table): void {
            $table->dropColumn(['name', 'extra_fields']);
        });
    }

    public function down(): void
    {
        throw new RuntimeException('SSS çevirileri otomatik geri alınamaz; veri kaybını önlemek için ileri migration kullanın.');
    }
};
