<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $duplicate = DB::table('products')->select('slug')->groupBy('slug')->havingRaw('COUNT(*) > 1')->first();
        if ($duplicate !== null) {
            throw new RuntimeException('Ürün kısa bağlantısı birden fazla kullanılıyor: '.$duplicate->slug.'. Çeviri geçişinden önce çakışmayı giderin.');
        }

        Schema::create('product_translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('lang_id')->constrained('languages')->restrictOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->text('short_description')->nullable();
            $table->longText('content')->nullable();
            $table->json('extra_fields')->nullable();
            $table->timestamps();
            $table->unique(['product_id', 'lang_id']);
            $table->unique(['lang_id', 'slug']);
        });
        Schema::create('product_category_translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_category_id')->constrained('product_categories')->cascadeOnDelete();
            $table->foreignId('lang_id')->constrained('languages')->restrictOnDelete();
            $table->string('name');
            $table->json('extra_fields')->nullable();
            $table->timestamps();
            $table->unique(['product_category_id', 'lang_id']);
        });

        DB::table('languages')->insertOrIgnore(['code' => 'tr', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('languages')->where('code', 'tr')->update(['is_active' => true]);
        $languageId = DB::table('languages')->where('code', 'tr')->value('id');
        foreach ([
            ['table' => 'products', 'translations' => 'product_translations', 'foreign' => 'product_id', 'fields' => ['title', 'slug', 'short_description', 'content', 'extra_fields']],
            ['table' => 'product_categories', 'translations' => 'product_category_translations', 'foreign' => 'product_category_id', 'fields' => ['name', 'extra_fields']],
        ] as $definition) {
            DB::table($definition['table'])->orderBy('id')->chunkById(200, function ($records) use ($definition, $languageId): void {
                foreach ($records as $record) {
                    $values = [$definition['foreign'] => $record->id, 'lang_id' => $languageId, 'created_at' => $record->created_at ?? now(), 'updated_at' => $record->updated_at ?? now()];
                    foreach ($definition['fields'] as $field) {
                        $values[$field] = $record->{$field};
                    }
                    DB::table($definition['translations'])->insertOrIgnore($values);
                }
            });
            if (DB::table($definition['table'])->count() !== DB::table($definition['translations'])->where('lang_id', $languageId)->count()) {
                throw new RuntimeException('Ürün kataloğu Türkçe çeviri geçişinde kayıt sayıları eşleşmedi: '.$definition['table']);
            }
            if (Schema::hasTable('media_usages')) {
                $owner = $definition['table'] === 'products' ? 'Modules\\Products\\Models\\Product' : 'Modules\\Products\\Models\\ProductCategory';
                $route = $definition['table'] === 'products' ? 'admin.products.edit' : 'admin.products.categories.edit';
                DB::table('media_usages')->where('owner_type', $owner)->where('field', 'like', 'extra_fields.%')->orderBy('id')->chunkById(200, function ($usages) use ($definition, $languageId, $owner, $route): void {
                    foreach ($usages as $usage) {
                        $translationId = DB::table($definition['translations'])->where($definition['foreign'], $usage->owner_id)->where('lang_id', $languageId)->value('id');
                        if ($translationId !== null) {
                            DB::table('media_usages')->where('id', $usage->id)->update(['owner_type' => $owner.'Translation', 'owner_id' => $translationId, 'route_name' => $route]);
                        }
                    }
                });
            }
        }

        Schema::table('products', function (Blueprint $table): void {
            $table->dropUnique(['slug']);
            $table->dropColumn(['title', 'slug', 'short_description', 'content', 'extra_fields']);
        });
        Schema::table('product_categories', function (Blueprint $table): void {
            $table->dropColumn(['name', 'extra_fields']);
        });
    }

    public function down(): void
    {
        throw new RuntimeException('Ürün çevirileri otomatik geri alınamaz; çeviri kaybını önlemek için ileri migration kullanın.');
    }
};
