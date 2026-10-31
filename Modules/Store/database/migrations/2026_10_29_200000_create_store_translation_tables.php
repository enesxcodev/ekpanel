<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach ([['store_products', 'slug'], ['store_categories', 'slug']] as [$table, $column]) {
            $duplicate = DB::table($table)->select($column)->groupBy($column)->havingRaw('COUNT(*) > 1')->first();
            if ($duplicate !== null) {
                throw new RuntimeException('Store çeviri geçişinden önce aynı tabloda yinelenen slug değerini giderin: '.$table.'.'.$duplicate->{$column});
            }
        }

        Schema::create('store_product_translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('store_product_id')->constrained('store_products')->cascadeOnDelete();
            $table->foreignId('lang_id')->constrained('languages')->restrictOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->text('short_description')->nullable();
            $table->longText('content')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('delivery_description')->nullable();
            $table->json('extra_fields')->nullable();
            $table->timestamps();
            $table->unique(['store_product_id', 'lang_id']);
            $table->unique(['lang_id', 'slug']);
        });
        Schema::create('store_category_translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('store_category_id')->constrained('store_categories')->cascadeOnDelete();
            $table->foreignId('lang_id')->constrained('languages')->restrictOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->json('extra_fields')->nullable();
            $table->timestamps();
            $table->unique(['store_category_id', 'lang_id']);
            $table->unique(['lang_id', 'slug']);
        });

        DB::table('languages')->insertOrIgnore(['code' => 'tr', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('languages')->where('code', 'tr')->update(['is_active' => true]);
        $languageId = DB::table('languages')->where('code', 'tr')->value('id');
        foreach ([
            ['table' => 'store_products', 'translations' => 'store_product_translations', 'foreign' => 'store_product_id', 'fields' => ['title', 'slug', 'short_description', 'content', 'meta_title', 'meta_description', 'delivery_description', 'extra_fields']],
            ['table' => 'store_categories', 'translations' => 'store_category_translations', 'foreign' => 'store_category_id', 'fields' => ['name', 'slug', 'extra_fields']],
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
                throw new RuntimeException('Store Türkçe çeviri backfill sayıları eşleşmedi: '.$definition['table']);
            }
            if (Schema::hasTable('media_usages')) {
                $owner = $definition['table'] === 'store_products' ? 'Modules\\Store\\Models\\StoreProduct' : 'Modules\\Store\\Models\\StoreCategory';
                $route = $definition['table'] === 'store_products' ? 'admin.store.edit' : 'admin.store.categories.edit';
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

        Schema::table('store_products', function (Blueprint $table): void {
            $table->dropUnique(['slug']);
            $table->dropColumn(['title', 'slug', 'short_description', 'content', 'meta_title', 'meta_description', 'delivery_description', 'extra_fields']);
        });
        Schema::table('store_categories', function (Blueprint $table): void {
            $table->dropUnique(['slug']);
            $table->dropColumn(['name', 'slug', 'extra_fields']);
        });
    }

    public function down(): void
    {
        throw new RuntimeException('Store çevirileri otomatik geri alınamaz; veri kaybını önlemek için ileri migration kullanın.');
    }
};
