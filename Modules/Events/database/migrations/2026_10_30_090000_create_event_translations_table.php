<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $createdTranslations = ! Schema::hasTable('event_translations');
        if ($createdTranslations) {
            Schema::create('event_translations', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
                $table->foreignId('lang_id')->constrained('languages')->restrictOnDelete();
                $table->string('title');
                $table->string('venue')->nullable();
                $table->longText('description')->nullable();
                $table->timestamps();
                $table->unique(['event_id', 'lang_id']);
            });
        }

        DB::table('languages')->insertOrIgnore(['code' => 'tr', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('languages')->where('code', 'tr')->update(['is_active' => true, 'updated_at' => now()]);
        $languageId = DB::table('languages')->where('code', 'tr')->value('id');
        $hasLegacyFields = Schema::hasColumn('events', 'title') && Schema::hasColumn('events', 'venue') && Schema::hasColumn('events', 'description');

        if ($hasLegacyFields) {
            DB::table('events')->orderBy('id')->chunkById(200, function ($events) use ($languageId): void {
                foreach ($events as $event) {
                    DB::table('event_translations')->insertOrIgnore([
                        'event_id' => $event->id,
                        'lang_id' => $languageId,
                        'title' => $event->title,
                        'venue' => $event->venue,
                        'description' => $event->description,
                        'created_at' => $event->created_at,
                        'updated_at' => $event->updated_at,
                    ]);
                }
            });

            $missing = DB::table('events')->whereNotExists(function ($query) use ($languageId): void {
                $query->selectRaw('1')->from('event_translations')->whereColumn('event_translations.event_id', 'events.id')->where('event_translations.lang_id', $languageId);
            })->exists();
            if ($missing || DB::table('events')->count() !== DB::table('event_translations')->where('lang_id', $languageId)->count()) {
                throw new RuntimeException('Events Türkçe çeviri aktarımında kayıt sayıları eşleşmedi.');
            }

            Schema::table('events', fn (Blueprint $table) => $table->dropColumn(['title', 'venue', 'description']));
        } elseif ($createdTranslations && DB::table('events')->exists()) {
            throw new RuntimeException('Events çeviri tablosu var ancak eski metin alanları bulunamadı; mevcut kayıtları doğrulayın.');
        }
    }

    public function down(): void
    {
        throw new RuntimeException('Events çevirileri otomatik geri alınamaz; veri kaybını önlemek için ileri migration kullanın.');
    }
};
