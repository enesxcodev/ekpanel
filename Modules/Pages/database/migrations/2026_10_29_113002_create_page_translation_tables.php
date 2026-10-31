<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['pages' => 'page_category_id', 'page_categories' => 'parent_id'] as $table => $scope) {
            $duplicate = DB::table($table)->select($scope, 'slug')->groupBy($scope, 'slug')->havingRaw('COUNT(*) > 1')->first();
            if ($duplicate !== null) {
                throw new RuntimeException("{$table} içinde aynı kapsamda yinelenen slug var: {$duplicate->slug}");
            }
        }

        Schema::create('page_translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('page_id')->constrained('pages')->cascadeOnDelete();
            $table->foreignId('lang_id')->constrained('languages')->restrictOnDelete();
            $table->unsignedBigInteger('scope_id')->default(0);
            $table->string('title');
            $table->string('slug');
            $table->string('short_description', 500)->nullable();
            $table->longText('content')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->text('meta_description')->nullable();
            $table->json('extra_fields')->nullable();
            $table->timestamps();
            $table->unique(['page_id', 'lang_id']);
            $table->unique(['lang_id', 'scope_id', 'slug']);
        });
        Schema::create('page_category_translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('page_category_id')->constrained('page_categories')->cascadeOnDelete();
            $table->foreignId('lang_id')->constrained('languages')->restrictOnDelete();
            $table->unsignedBigInteger('scope_id')->default(0);
            $table->string('name');
            $table->string('slug');
            $table->string('short_description', 500)->nullable();
            $table->json('extra_fields')->nullable();
            $table->timestamps();
            $table->unique(['page_category_id', 'lang_id'], 'page_category_lang_unique');
            $table->unique(['lang_id', 'scope_id', 'slug'], 'page_category_lang_scope_slug_unique');
        });

        DB::table('languages')->insertOrIgnore(['code' => 'tr', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('languages')->where('code', 'tr')->update(['is_active' => true]);
        $languageId = DB::table('languages')->where('code', 'tr')->value('id');
        $sources = [
            ['pages', 'page_translations', 'page_id', 'page_category_id', ['title', 'slug', 'short_description', 'content', 'meta_title', 'meta_keywords', 'meta_description', 'extra_fields']],
            ['page_categories', 'page_category_translations', 'page_category_id', 'parent_id', ['name', 'slug', 'short_description', 'extra_fields']],
        ];
        foreach ($sources as [$source, $target, $foreignKey, $scope, $fields]) {
            DB::table($source)->orderBy('id')->chunkById(200, function ($records) use ($target, $foreignKey, $scope, $fields, $languageId): void {
                foreach ($records as $record) {
                    $data = [$foreignKey => $record->id, 'lang_id' => $languageId, 'scope_id' => $record->{$scope} ?? 0, 'created_at' => $record->created_at, 'updated_at' => $record->updated_at];
                    foreach ($fields as $field) {
                        $data[$field] = $record->{$field};
                    }
                    DB::table($target)->insert($data);
                }
            });
        }
    }

    public function down(): void
    {
        throw new RuntimeException('Sayfa çevirileri otomatik geri alınamaz; çeviri kaybını önlemek için ileri migration kullanın.');
    }
};
