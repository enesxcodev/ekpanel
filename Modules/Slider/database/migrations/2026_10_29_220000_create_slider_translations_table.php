<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['sliders', 'languages'] as $table) {
            if (! Schema::hasTable($table)) {
                throw new RuntimeException("Slider çeviri geçişi için {$table} tablosu bulunamadı.");
            }
        }
        foreach (['title', 'subtitle', 'extra_fields'] as $column) {
            if (! Schema::hasColumn('sliders', $column)) {
                throw new RuntimeException("Slider çeviri geçişi için sliders.{$column} kolonu bulunamadı.");
            }
        }

        DB::transaction(function (): void {
            DB::table('languages')->insertOrIgnore(['code' => 'tr', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
            DB::table('languages')->where('code', 'tr')->update(['is_active' => true, 'updated_at' => now()]);
            $languageId = DB::table('languages')->where('code', 'tr')->value('id');
            if ($languageId === null) {
                throw new RuntimeException('Slider Türkçe dili oluşturulamadı.');
            }

            if (! Schema::hasTable('slider_translations')) {
                Schema::create('slider_translations', function (Blueprint $table): void {
                    $table->id();
                    $table->foreignId('slider_id')->constrained('sliders')->cascadeOnDelete();
                    $table->foreignId('lang_id')->constrained('languages')->restrictOnDelete();
                    $table->string('title');
                    $table->text('subtitle')->nullable();
                    $table->json('extra_fields')->nullable();
                    $table->timestamps();
                    $table->unique(['slider_id', 'lang_id']);
                });
            }

            $owner = 'Modules\\Slider\\Models\\Slider';
            DB::table('sliders')->orderBy('id')->chunkById(200, function ($sliders) use ($languageId, $owner): void {
                foreach ($sliders as $slider) {
                    $existing = DB::table('slider_translations')->where('slider_id', $slider->id)->where('lang_id', $languageId)->first();
                    if ($existing === null) {
                        $translationId = DB::table('slider_translations')->insertGetId([
                            'slider_id' => $slider->id, 'lang_id' => $languageId,
                            'title' => $slider->title, 'subtitle' => $slider->subtitle,
                            'extra_fields' => $slider->extra_fields,
                            'created_at' => $slider->created_at, 'updated_at' => $slider->updated_at,
                        ]);
                    } else {
                        $sourceFields = $slider->extra_fields === null ? null : json_decode($slider->extra_fields, true, flags: JSON_THROW_ON_ERROR);
                        $translatedFields = $existing->extra_fields === null ? null : json_decode($existing->extra_fields, true, flags: JSON_THROW_ON_ERROR);
                        if ($existing->title !== $slider->title || $existing->subtitle !== $slider->subtitle || $sourceFields !== $translatedFields) {
                            throw new RuntimeException("Slider #{$slider->id} Türkçe çevirisi mevcut kaynakla uyuşmuyor; eski sütunlar korunuyor.");
                        }
                        $translationId = $existing->id;
                    }
                    if (Schema::hasTable('media_usages')) {
                        DB::table('media_usages')->where('owner_type', $owner)->where('owner_id', $slider->id)->where('field', 'like', 'extra_fields.%')->update([
                            'owner_type' => $owner.'Translation', 'owner_id' => $translationId, 'route_name' => 'admin.slider.edit',
                        ]);
                    }
                }
            });

            $sourceCount = DB::table('sliders')->count();
            $translationCount = DB::table('slider_translations')->where('lang_id', $languageId)->count();
            if ($sourceCount !== $translationCount) {
                throw new RuntimeException("Slider Türkçe çeviri sayıları eşleşmedi ({$sourceCount}/{$translationCount}); eski sütunlar korunuyor.");
            }
            Schema::table('sliders', function (Blueprint $table): void {
                $table->dropColumn(['title', 'subtitle', 'extra_fields']);
            });
        });
    }

    public function down(): void
    {
        throw new RuntimeException('Slider çevirileri otomatik geri alınamaz; veri kaybını önlemek için ileri migration kullanın.');
    }
};
