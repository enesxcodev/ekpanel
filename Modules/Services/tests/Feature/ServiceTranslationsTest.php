<?php

use App\Models\Admin\Admin;
use App\Models\Media\MediaFile;
use App\Models\Settings\Language;
use App\Services\Media\MediaService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Modules\Services\Models\Service;
use Modules\Services\Models\ServiceCategory;
use Modules\Services\Models\ServiceTranslation;
use Modules\Services\Services\ServicesFrontendService;
use Nwidart\Modules\Contracts\ActivatorInterface;

uses(LazilyRefreshDatabase::class);

test('saving a service translation preserves the original language', function () {
    $this->withoutVite();
    $english = Language::query()->create(['code' => 'en', 'is_active' => true]);
    $service = Service::factory()->create(['title' => 'Türkçe', 'slug' => 'turkce']);
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin')
        ->get(route('admin.services.edit', ['service' => $service->id, 'lang' => 'en']))
        ->assertOk()->assertDontSee('value="Türkçe"', false);
    $this->put(route('admin.services.update', $service), ['lang_id' => $english->id, 'title' => 'English', 'slug' => 'english', 'is_active' => true])
        ->assertSessionHasNoErrors()->assertRedirectToRoute('admin.services.edit', ['service' => $service->id, 'lang' => 'en']);
    $this->assertDatabaseHas('service_translations', ['service_id' => $service->id, 'title' => 'Türkçe']);
    $this->assertDatabaseHas('service_translations', ['service_id' => $service->id, 'lang_id' => $english->id, 'title' => 'English']);
    $english->update(['is_active' => false]);
    $this->put(route('admin.services.update', $service), ['lang_id' => $english->id, 'title' => 'Rejected', 'is_active' => true])->assertSessionHasErrors('lang_id');
});

test('frontend requires translations for the service and its category in the requested active language', function () {
    $disk = Storage::fake('services-translation-statuses');
    $disk->put('statuses.json', json_encode(['Services' => true], JSON_THROW_ON_ERROR));
    config(['modules.activators.file.statuses-file' => $disk->path('statuses.json')]);
    app()->forgetInstance(ActivatorInterface::class);
    $english = Language::query()->create(['code' => 'en', 'is_active' => true]);
    $category = ServiceCategory::factory()->create();
    $service = Service::factory()->for($category, 'category')->create();
    $service->translations()->create(['lang_id' => $english->id, 'title' => 'English']);
    $frontend = app(ServicesFrontendService::class);
    expect($frontend->find($service->id, 'en'))->toBeNull();
    $category->translations()->create(['lang_id' => $english->id, 'name' => 'Category']);
    expect($frontend->find($service->id, 'en')?->title)->toBe('English');
    expect($frontend->find($service->id, 'en')?->category->name)->toBe('Category');
    $english->update(['is_active' => false]);
    expect($frontend->find($service->id, 'en'))->toBeNull();
});

test('category translations retain their own fields and inactive languages are rejected', function () {
    $english = Language::query()->create(['code' => 'en', 'is_active' => true]);
    $category = ServiceCategory::factory()->create(['name' => 'Türkçe', 'extra_fields' => ['badge_text' => 'Eski']]);
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin')
        ->put(route('admin.services.categories.update', $category), ['lang_id' => $english->id, 'name' => 'English', 'extra_fields' => ['badge_text' => 'New']])
        ->assertSessionHasNoErrors()->assertRedirectToRoute('admin.services.categories.edit', ['category' => $category->id, 'lang' => 'en']);
    $this->assertDatabaseHas('service_category_translations', ['service_category_id' => $category->id, 'name' => 'Türkçe']);
    expect($category->translations()->where('lang_id', $english->id)->sole()->extra_fields['badge_text'])->toBe('New');
    $english->update(['is_active' => false]);
    $this->put(route('admin.services.categories.update', $category), ['lang_id' => $english->id, 'name' => 'Rejected'])->assertSessionHasErrors('lang_id');
});

test('a slug is unique per language', function () {
    $service = Service::factory()->create(['slug' => 'same-slug']);
    $turkish = Language::query()->where('code', 'tr')->sole();
    $english = Language::query()->create(['code' => 'en', 'is_active' => true]);
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin')
        ->post(route('admin.services.store'), ['lang_id' => $turkish->id, 'title' => 'Duplicate', 'slug' => 'same-slug', 'is_active' => true])->assertSessionHasErrors('slug');
    $this->post(route('admin.services.store'), ['lang_id' => $english->id, 'title' => 'Allowed', 'slug' => 'same-slug', 'is_active' => true])->assertSessionHasNoErrors();
    $this->assertDatabaseCount('services', 2);
});

test('forms disable saving when no language is active', function () {
    $this->withoutVite();
    Language::query()->update(['is_active' => false]);
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin')
        ->get(route('admin.services.create'))->assertOk()->assertSee('<fieldset disabled>', false);
    $this->get(route('admin.services.categories.create'))->assertOk()->assertSee('<fieldset disabled>', false);
});

test('translation media links return to their language and deleting a service detaches its translations', function () {
    $english = Language::query()->create(['code' => 'en', 'is_active' => true]);
    $service = Service::factory()->create();
    $translation = $service->translations()->create(['lang_id' => $english->id, 'title' => 'English']);
    $media = MediaFile::factory()->create();
    app(MediaService::class)->attachUsage($media, $translation, 'extra_fields.document', 'Document', 'admin.services.translations.edit');
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin')
        ->get(route('admin.services.translations.edit', $translation))->assertRedirectToRoute('admin.services.edit', ['service' => $service->id, 'lang' => 'en']);
    $this->delete(route('admin.services.destroy', $service))->assertRedirectToRoute('admin.services.index');
    $this->assertDatabaseCount('service_translations', 0);
    $this->assertDatabaseCount('media_usages', 0);
    $this->assertModelExists($media);
});

test('migration transfers existing service content and media usages into Turkish translations', function () {
    config(['database.connections.services_migration_test' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => true]]);
    $original = DB::getDefaultConnection();
    DB::setDefaultConnection('services_migration_test');
    try {
        Schema::create('languages', function ($table): void {
            $table->id();
            $table->string('code')->unique();
            $table->boolean('is_active');
            $table->timestamps();
        });
        Schema::create('service_categories', function ($table): void {
            $table->id();
            $table->string('name');
            $table->json('extra_fields')->nullable();
        });
        Schema::create('services', function ($table): void {
            $table->id();
            $table->string('title');
            $table->string('slug')->nullable()->unique();
            $table->text('short_description')->nullable();
            $table->text('content')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->text('meta_description')->nullable();
            $table->json('extra_fields')->nullable();
            $table->timestamps();
        });
        Schema::create('media_usages', function ($table): void {
            $table->id();
            $table->string('owner_type');
            $table->unsignedBigInteger('owner_id');
            $table->string('field');
            $table->string('route_name');
        });
        DB::table('service_categories')->insert(['id' => 1, 'name' => 'Kategori', 'extra_fields' => '{"badge_text":"Rozet"}']);
        DB::table('services')->insert(['id' => 2, 'title' => 'Hizmet', 'slug' => 'hizmet', 'content' => 'İçerik', 'extra_fields' => '{"document":5}']);
        DB::table('media_usages')->insert(['owner_type' => Service::class, 'owner_id' => 2, 'field' => 'extra_fields.document', 'route_name' => 'admin.services.edit']);
        $migration = require module_path('Services', 'database/migrations/2026_10_29_081811_create_service_translation_tables.php');
        $migration->up();
        expect(DB::table('service_translations')->where('service_id', 2)->value('title'))->toBe('Hizmet');
        expect(DB::table('service_category_translations')->where('service_category_id', 1)->value('name'))->toBe('Kategori');
        expect(DB::table('media_usages')->value('owner_type'))->toBe(ServiceTranslation::class);
        expect(DB::table('media_usages')->value('route_name'))->toBe('admin.services.translations.edit');
        expect(Schema::hasColumn('services', 'title'))->toBeFalse();
    } finally {
        DB::setDefaultConnection($original);
        DB::purge('services_migration_test');
    }
});

test('both tables return Turkish records with their default ordering', function () {
    $service = Service::factory()->create(['title' => 'Türkçe hizmet']);
    $category = ServiceCategory::factory()->create(['name' => 'Türkçe kategori']);
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin');
    foreach ([
        ['admin.services.data', 'services.id', 'title', $service->id, 'Türkçe hizmet'],
        ['admin.services.categories.data', 'service_categories.id', 'name', $category->id, 'Türkçe kategori'],
    ] as [$route, $idColumn, $field, $id, $value]) {
        $this->getJson(route($route, [
            'draw' => 1, 'start' => 0, 'length' => 10,
            'columns' => [
                ['data' => 'id', 'name' => $idColumn, 'searchable' => 'true', 'orderable' => 'true'],
                ['data' => $field, 'name' => $field, 'searchable' => 'true', 'orderable' => 'true'],
            ],
            'order' => [['column' => 0, 'dir' => 'desc']],
        ]))->assertOk()->assertJsonPath('data.0.id', $id)->assertJsonPath('data.0.'.$field, $value);
    }
});
