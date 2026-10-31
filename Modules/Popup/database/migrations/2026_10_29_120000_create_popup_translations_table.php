<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['popups', 'languages'] as $table) {
            if (! Schema::hasTable($table)) {
                throw new RuntimeException("Popup çeviri geçişi için {$table} tablosu bulunamadı.");
            }
        }
        foreach (['title', 'content', 'extra_fields'] as $column) {
            if (! Schema::hasColumn('popups', $column)) {
                throw new RuntimeException("Popup çeviri geçişi için popups.{$column} kolonu bulunamadı; veri yapısını inceleyin.");
            }
        }

        DB::transaction(function (): void {
            DB::table('languages')->insertOrIgnore(['code' => 'tr', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
            DB::table('languages')->where('code', 'tr')->update(['is_active' => true, 'updated_at' => now()]);
            $languageId = DB::table('languages')->where('code', 'tr')->value('id');
            if ($languageId === null) {
                throw new RuntimeException('Popup çeviri geçişi aktif Türkçe dil kaydını oluşturamadı.');
            }

            if (! Schema::hasTable('popup_translations')) {
                Schema::create('popup_translations', function (Blueprint $table): void {
                    $table->id();
                    $table->foreignId('popup_id')->constrained('popups')->cascadeOnDelete();
                    $table->foreignId('lang_id')->constrained('languages')->restrictOnDelete();
                    $table->string('title');
                    $table->longText('content');
                    $table->json('extra_fields')->nullable();
                    $table->timestamps();
                    $table->unique(['popup_id', 'lang_id']);
                });
            }

            $owner = 'Modules\\Popup\\Models\\Popup';
            DB::table('popups')->orderBy('id')->chunkById(200, function ($popups) use ($languageId, $owner): void {
                foreach ($popups as $popup) {
                    $values = [
                        'popup_id' => $popup->id,
                        'lang_id' => $languageId,
                        'title' => $popup->title,
                        'content' => $popup->content,
                        'extra_fields' => $popup->extra_fields,
                        'created_at' => $popup->created_at,
                        'updated_at' => $popup->updated_at,
                    ];
                    $existing = DB::table('popup_translations')->where('popup_id', $popup->id)->where('lang_id', $languageId)->first();
                    if ($existing !== null) {
                        $sourceFields = $popup->extra_fields === null ? null : json_decode($popup->extra_fields, true, flags: JSON_THROW_ON_ERROR);
                        $existingFields = $existing->extra_fields === null ? null : json_decode($existing->extra_fields, true, flags: JSON_THROW_ON_ERROR);
                        if ($existing->title !== $popup->title || $existing->content !== $popup->content || $sourceFields !== $existingFields) {
                            throw new RuntimeException("Popup #{$popup->id} için kısmi çeviri kaydı eski alanlarla uyuşmuyor; kolonlar korunuyor.");
                        }
                        $translationId = $existing->id;
                    } else {
                        $translationId = DB::table('popup_translations')->insertGetId($values);
                    }
                    if (Schema::hasTable('media_usages')) {
                        DB::table('media_usages')->where('owner_type', $owner)->where('owner_id', $popup->id)
                            ->where('field', 'like', 'extra_fields.%')->update([
                                'owner_type' => $owner.'Translation',
                                'owner_id' => $translationId,
                                'route_name' => 'admin.popup.edit',
                            ]);
                    }
                }
            });

            $sourceCount = DB::table('popups')->count();
            $translationCount = DB::table('popup_translations')->where('lang_id', $languageId)->count();
            if ($sourceCount !== $translationCount) {
                throw new RuntimeException("Popup çeviri geçişinde kayıt sayıları eşleşmedi ({$sourceCount}/{$translationCount}); eski alanlar korunuyor.");
            }

            Schema::table('popups', function (Blueprint $table): void {
                $table->dropColumn(['title', 'content', 'extra_fields']);
            });
        });
    }

    public function down(): void
    {
        throw new RuntimeException('Popup çevirileri otomatik geri alınamaz; çeviri kaybını önlemek için ileri migration kullanın.');
    }
};
