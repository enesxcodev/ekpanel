<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('team_member_translations')) {
            Schema::create('team_member_translations', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('team_member_id')->constrained('team_members')->cascadeOnDelete();
                $table->foreignId('lang_id')->constrained('languages')->restrictOnDelete();
                $table->string('name');
                $table->string('title')->nullable();
                $table->json('extra_fields')->nullable();
                $table->timestamps();
                $table->unique(['team_member_id', 'lang_id']);
            });
        }
        DB::table('languages')->insertOrIgnore(['code' => 'tr', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('languages')->where('code', 'tr')->update(['is_active' => true, 'updated_at' => now()]);
        $languageId = DB::table('languages')->where('code', 'tr')->value('id');
        $hasLegacyFields = Schema::hasColumn('team_members', 'name') && Schema::hasColumn('team_members', 'title') && Schema::hasColumn('team_members', 'extra_fields');
        if ($hasLegacyFields) {
            DB::table('team_members')->orderBy('id')->chunkById(200, function ($members) use ($languageId): void {
                foreach ($members as $member) {
                    DB::table('team_member_translations')->insertOrIgnore([
                        'team_member_id' => $member->id, 'lang_id' => $languageId,
                        'name' => $member->name, 'title' => $member->title, 'extra_fields' => $member->extra_fields,
                        'created_at' => $member->created_at, 'updated_at' => $member->updated_at,
                    ]);
                    if (Schema::hasTable('media_usages')) {
                        DB::table('media_usages')->where('owner_type', 'Modules\\Team\\Models\\TeamMember')->where('owner_id', $member->id)->where('field', 'like', 'extra_fields.%')->update([
                            'owner_type' => 'Modules\\Team\\Models\\TeamMemberTranslation',
                            'owner_id' => DB::table('team_member_translations')->where('team_member_id', $member->id)->where('lang_id', $languageId)->value('id'),
                            'route_name' => 'admin.team.edit',
                        ]);
                    }
                }
            });
        }
        $legacyCount = DB::table('team_members')->count();
        $translationCount = DB::table('team_member_translations')->where('lang_id', $languageId)->count();
        $missingTranslations = DB::table('team_members')->whereNotExists(function ($query) use ($languageId): void {
            $query->selectRaw('1')->from('team_member_translations')->whereColumn('team_member_translations.team_member_id', 'team_members.id')->where('team_member_translations.lang_id', $languageId);
        })->exists();
        if ($translationCount < $legacyCount || $missingTranslations) {
            throw new RuntimeException('Team Türkçe çeviri aktarımında kayıt sayıları eşleşmedi.');
        }
        if ($hasLegacyFields) {
            Schema::table('team_members', function (Blueprint $table): void {
                $table->dropColumn(['name', 'title', 'extra_fields']);
            });
        }
    }

    public function down(): void
    {
        throw new RuntimeException('Team çevirileri otomatik geri alınamaz; veri kaybını önlemek için ileri migration kullanın.');
    }
};
