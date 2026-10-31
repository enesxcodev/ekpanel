<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $duplicate = DB::table('blog_categories')->select('slug')->groupBy('slug')->havingRaw('COUNT(*) > 1')->first();
        if ($duplicate !== null) {
            throw new RuntimeException('Blog kategori kısa bağlantısı birden fazla kullanılıyor: '.$duplicate->slug.'. Çeviri geçişinden önce çakışmayı giderin.');
        }

        Schema::create('blog_content_translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('blog_content_id')->constrained('blog_contents')->cascadeOnDelete();
            $table->foreignId('lang_id')->constrained('languages')->restrictOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->string('short_description', 500)->nullable();
            $table->longText('content')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description', 1000)->nullable();
            $table->json('extra_fields')->nullable();
            $table->timestamps();
            $table->unique(['blog_content_id', 'lang_id']);
            $table->unique(['lang_id', 'slug']);
        });
        Schema::create('blog_category_translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('blog_category_id')->constrained('blog_categories')->cascadeOnDelete();
            $table->foreignId('lang_id')->constrained('languages')->restrictOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->json('extra_fields')->nullable();
            $table->timestamps();
            $table->unique(['blog_category_id', 'lang_id']);
            $table->unique(['lang_id', 'slug']);
        });
        DB::table('languages')->insertOrIgnore(['code' => 'tr', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $languageId = DB::table('languages')->where('code', 'tr')->value('id');
        foreach (['content' => ['title', 'slug', 'short_description', 'content', 'meta_title', 'meta_description', 'extra_fields'], 'category' => ['name', 'slug', 'extra_fields']] as $type => $fields) {
            $source = $type === 'content' ? 'blog_contents' : 'blog_categories';
            DB::table($source)->orderBy('id')->chunkById(200, function ($records) use ($type, $fields, $languageId): void {
                foreach ($records as $record) {
                    $values = ['blog_'.$type.'_id' => $record->id, 'lang_id' => $languageId, 'created_at' => $record->created_at, 'updated_at' => $record->updated_at];
                    foreach ($fields as $field) {
                        $values[$field] = $record->{$field};
                    }
                    $translationId = DB::table('blog_'.$type.'_translations')->insertGetId($values);
                    if (Schema::hasTable('media_usages')) {
                        $owner = $type === 'content' ? 'Modules\\Blog\\Models\\BlogContent' : 'Modules\\Blog\\Models\\BlogCategory';
                        $route = $type === 'content' ? 'admin.blog.translations.edit' : 'admin.blog.categories.translations.edit';
                        DB::table('media_usages')->where('owner_type', $owner)->where('owner_id', $record->id)->where('field', 'like', 'extra_fields.%')->update(['owner_type' => $owner.'Translation', 'owner_id' => $translationId, 'route_name' => $route]);
                    }
                }
            });
            if (DB::table($source)->count() !== DB::table('blog_'.$type.'_translations')->where('lang_id', $languageId)->count()) {
                throw new RuntimeException('Blog çeviri geçiþinde kayýt sayýlarý eþleþmedi.');
            }
        }
        Schema::table('blog_categories', function (Blueprint $table): void {
            $table->dropUnique(['parent_id', 'slug']);
            $table->dropColumn(['name', 'slug', 'extra_fields']);
        });
        Schema::table('blog_contents', function (Blueprint $table): void {
            $table->dropUnique(['slug']);
            $table->dropColumn(['title', 'slug', 'short_description', 'content', 'meta_title', 'meta_description', 'extra_fields']);
        });
    }

    public function down(): void
    {
        throw new RuntimeException('Blog çevirileri otomatik geri alınamaz; çeviri kaybını önlemek için ileri migration kullanın.');
    }
};
