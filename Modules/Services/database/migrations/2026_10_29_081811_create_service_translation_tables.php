<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
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
            $table->unique(['service_id', 'lang_id']);
            $table->unique(['lang_id', 'slug']);
        });
        Schema::create('service_category_translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('service_category_id')->constrained('service_categories')->cascadeOnDelete();
            $table->foreignId('lang_id')->constrained('languages')->restrictOnDelete();
            $table->string('name');
            $table->json('extra_fields')->nullable();
            $table->timestamps();
            $table->unique(['service_category_id', 'lang_id']);
        });

        DB::table('languages')->insertOrIgnore(['code' => 'tr', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $languageId = DB::table('languages')->where('code', 'tr')->value('id');
        foreach (['service' => ['title', 'slug', 'short_description', 'content', 'meta_title', 'meta_keywords', 'meta_description', 'extra_fields'], 'service_category' => ['name', 'extra_fields']] as $type => $fields) {
            $source = $type === 'service' ? 'services' : 'service_categories';
            DB::table($source)->orderBy('id')->chunkById(200, function ($records) use ($type, $fields, $languageId): void {
                foreach ($records as $record) {
                    $values = [$type.'_id' => $record->id, 'lang_id' => $languageId, 'created_at' => $record->created_at ?? now(), 'updated_at' => $record->updated_at ?? now()];
                    foreach ($fields as $field) {
                        $values[$field] = $record->{$field};
                    }
                    $translationId = DB::table($type.'_translations')->insertGetId($values);
                    if (Schema::hasTable('media_usages')) {
                        $owner = $type === 'service' ? 'Modules\\Services\\Models\\Service' : 'Modules\\Services\\Models\\ServiceCategory';
                        $route = $type === 'service' ? 'admin.services.translations.edit' : 'admin.services.categories.translations.edit';
                        DB::table('media_usages')->where('owner_type', $owner)->where('owner_id', $record->id)->where('field', 'like', 'extra_fields.%')->update(['owner_type' => $owner.'Translation', 'owner_id' => $translationId, 'route_name' => $route]);
                    }
                }
            });
        }
        Schema::table('services', function (Blueprint $table): void {
            $table->dropUnique(['slug']);
            $table->dropColumn(['title', 'slug', 'short_description', 'content', 'meta_title', 'meta_keywords', 'meta_description', 'extra_fields']);
        });
        Schema::table('service_categories', function (Blueprint $table): void {
            $table->dropColumn(['name', 'extra_fields']);
        });
    }

    public function down(): void
    {
        throw new RuntimeException('Hizmet çevirileri otomatik geri alınamaz; çeviri kaybını önlemek için ileri migration kullanın.');
    }
};
