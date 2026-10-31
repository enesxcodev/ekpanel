<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('languages')->insertOrIgnore(['code' => 'tr', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('languages')->where('code', 'tr')->update(['is_active' => true, 'updated_at' => now()]);
        $languageId = (int) DB::table('languages')->where('code', 'tr')->value('id');

        foreach ([['projects', 'slug'], ['project_categories', 'slug']] as [$table, $column]) {
            $duplicate = DB::table($table)->select($column)->whereNotNull($column)->groupBy($column)->havingRaw('COUNT(*) > 1')->value($column);
            if ($duplicate !== null) {
                throw new RuntimeException("{$table}.{$column} içinde tekrarlanan '{$duplicate}' değeri var; çeviri migration'ından önce slug'ları düzeltin.");
            }
        }

        Schema::create('project_translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('lang_id')->constrained('languages')->restrictOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->longText('content')->nullable();
            $table->json('extra_fields')->nullable();
            $table->timestamps();
            $table->unique(['project_id', 'lang_id']);
            $table->unique(['lang_id', 'slug']);
        });
        Schema::create('project_category_translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('project_category_id')->constrained('project_categories')->cascadeOnDelete();
            $table->foreignId('lang_id')->constrained('languages')->restrictOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->json('extra_fields')->nullable();
            $table->timestamps();
            $table->unique(['project_category_id', 'lang_id']);
            $table->unique(['lang_id', 'slug']);
        });

        DB::table('projects')->orderBy('id')->chunkById(200, function ($projects) use ($languageId): void {
            foreach ($projects as $project) {
                DB::table('project_translations')->insertOrIgnore([
                    'project_id' => $project->id, 'lang_id' => $languageId, 'title' => $project->title, 'slug' => $project->slug,
                    'content' => $project->content, 'extra_fields' => $project->extra_fields,
                    'created_at' => $project->created_at, 'updated_at' => $project->updated_at,
                ]);
                $translation = DB::table('project_translations')->where('project_id', $project->id)->where('lang_id', $languageId)->first();
                if ($translation === null || $translation->title !== $project->title || $translation->slug !== $project->slug || $translation->content !== $project->content || $translation->extra_fields !== $project->extra_fields) {
                    throw new RuntimeException("Project #{$project->id} legacy içeriği Türkçe çeviriyle eşleşmiyor; eski sütunlar korunuyor.");
                }
                $translationId = $translation->id;
                if (Schema::hasTable('media_usages')) {
                    DB::table('media_usages')->where('owner_type', 'Modules\\Projects\\Models\\Project')->where('owner_id', $project->id)->where('field', 'like', 'extra_fields.%')->update([
                        'owner_type' => 'Modules\\Projects\\Models\\ProjectTranslation', 'owner_id' => $translationId, 'route_name' => 'admin.projects.translations.edit',
                    ]);
                }
            }
        });
        DB::table('project_categories')->orderBy('id')->chunkById(200, function ($categories) use ($languageId): void {
            foreach ($categories as $category) {
                DB::table('project_category_translations')->insertOrIgnore([
                    'project_category_id' => $category->id, 'lang_id' => $languageId, 'name' => $category->name, 'slug' => $category->slug,
                    'extra_fields' => $category->extra_fields, 'created_at' => $category->created_at, 'updated_at' => $category->updated_at,
                ]);
                $translation = DB::table('project_category_translations')->where('project_category_id', $category->id)->where('lang_id', $languageId)->first();
                if ($translation === null || $translation->name !== $category->name || $translation->slug !== $category->slug || $translation->extra_fields !== $category->extra_fields) {
                    throw new RuntimeException("ProjectCategory #{$category->id} legacy içeriği Türkçe çeviriyle eşleşmiyor; eski sütunlar korunuyor.");
                }
                $translationId = $translation->id;
                if (Schema::hasTable('media_usages')) {
                    DB::table('media_usages')->where('owner_type', 'Modules\\Projects\\Models\\ProjectCategory')->where('owner_id', $category->id)->where('field', 'like', 'extra_fields.%')->update([
                        'owner_type' => 'Modules\\Projects\\Models\\ProjectCategoryTranslation', 'owner_id' => $translationId, 'route_name' => 'admin.projects.categories.translations.edit',
                    ]);
                }
            }
        });

        $missingProjects = DB::table('projects')->whereNotExists(fn ($query) => $query->selectRaw('1')->from('project_translations')->whereColumn('project_translations.project_id', 'projects.id')->where('project_translations.lang_id', $languageId))->exists();
        $missingCategories = DB::table('project_categories')->whereNotExists(fn ($query) => $query->selectRaw('1')->from('project_category_translations')->whereColumn('project_category_translations.project_category_id', 'project_categories.id')->where('project_category_translations.lang_id', $languageId))->exists();
        if ($missingProjects || $missingCategories) {
            throw new RuntimeException('Projects Türkçe çeviri aktarımı doğrulanamadı; eski alanlar korunuyor.');
        }

        Schema::table('projects', function (Blueprint $table): void {
            $table->dropUnique('projects_slug_unique');
            $table->dropColumn(['title', 'slug', 'content', 'extra_fields']);
        });
        Schema::table('project_categories', function (Blueprint $table): void {
            $table->dropUnique('project_categories_slug_unique');
            $table->dropColumn(['name', 'slug', 'extra_fields']);
        });
    }

    public function down(): void
    {
        throw new RuntimeException('Projects çeviri verisi otomatik geri alınamaz; veri kaybını önlemek için ileri migration kullanın.');
    }
};
