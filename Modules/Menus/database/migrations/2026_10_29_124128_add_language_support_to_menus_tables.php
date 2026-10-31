<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach (['languages', 'menus', 'menu_items'] as $table) {
            if (! Schema::hasTable($table)) {
                throw new RuntimeException("Menüler dil geçişi için gerekli '{$table}' tablosu bulunamadı.");
            }
        }

        $crossMenuParent = DB::table('menu_items as item')
            ->join('menu_items as parent', 'parent.id', '=', 'item.parent_id')
            ->whereColumn('parent.menu_id', '!=', 'item.menu_id')
            ->select('item.id', 'item.parent_id')
            ->first();
        if ($crossMenuParent !== null) {
            throw new RuntimeException("Menü öğesi {$crossMenuParent->id}, başka bir menüdeki {$crossMenuParent->parent_id} üst öğesine bağlı. Önce ağaç ilişkisini düzeltin.");
        }

        DB::table('languages')->insertOrIgnore(['code' => 'tr', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('languages')->where('code', 'tr')->update(['is_active' => true]);
        $languageId = DB::table('languages')->where('code', 'tr')->value('id');
        if (! is_numeric($languageId)) {
            throw new RuntimeException('Menü geçişi için aktif Türkçe dil kaydı oluşturulamadı.');
        }

        Schema::create('menu_translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('menu_id')->constrained('menus')->cascadeOnDelete();
            $table->foreignId('lang_id')->constrained('languages')->restrictOnDelete();
            $table->string('name');
            $table->timestamps();
            $table->unique(['menu_id', 'lang_id']);
        });

        Schema::table('menu_items', function (Blueprint $table): void {
            $table->foreignId('lang_id')->nullable()->after('menu_id');
        });

        DB::table('menus')->orderBy('id')->chunkById(200, function ($menus) use ($languageId): void {
            foreach ($menus as $menu) {
                DB::table('menu_translations')->insert([
                    'menu_id' => $menu->id,
                    'lang_id' => $languageId,
                    'name' => $menu->name,
                    'created_at' => $menu->created_at ?? now(),
                    'updated_at' => $menu->updated_at ?? now(),
                ]);
            }
        });

        DB::table('menu_items')->whereNull('lang_id')->update(['lang_id' => $languageId]);

        if (DB::table('menus')->count() !== DB::table('menu_translations')->where('lang_id', $languageId)->count()
            || DB::table('menu_items')->whereNull('lang_id')->exists()
            || DB::table('menu_items')->count() !== DB::table('menu_items')->where('lang_id', $languageId)->count()) {
            throw new RuntimeException('Menü Türkçe veri aktarımında kayıt sayıları eşleşmedi.');
        }

        Schema::table('menu_items', function (Blueprint $table): void {
            $table->unsignedBigInteger('lang_id')->nullable(false)->change();
            $table->foreign('lang_id')->references('id')->on('languages')->restrictOnDelete();
            $table->index(['menu_id', 'lang_id', 'parent_id', 'sort_order'], 'menu_items_lang_tree_order_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        throw new RuntimeException('Menü çevirileri ve dil bazlı ağaçlar otomatik geri alınamaz; verileri korumak için ileri migration kullanın.');
    }
};
