<?php

use App\Models\Admin\Admin;
use App\Models\Media\MediaFile;
use App\Models\Settings\Language;
use App\Services\Admin\Modules\ModulePanelService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Projects\Models\Project;
use Modules\Projects\Models\ProjectCategory;
use Nwidart\Modules\Contracts\ActivatorInterface;

uses(LazilyRefreshDatabase::class);
beforeEach(function () {
    $this->withoutVite();
});

function projectTestImage(string $name): MediaFile
{
    $path = UploadedFile::fake()->image($name)->store('_media', 'public');

    return MediaFile::factory()->create(['path' => $path, 'name' => $name, 'kind' => 'image', 'mime_type' => 'image/png', 'extension' => 'png', 'size' => Storage::disk('public')->size($path)]);
}

test('projects categories persist slug and icon and reject duplicate slugs', function () {
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin');
    $langId = Language::query()->firstOrCreate(['code' => 'tr'], ['is_active' => true])->id;
    $this->get(route('admin.projects.categories.create'))->assertOk()->assertSee('data-slug-title', false)->assertSee('data-slug-field', false);
    $this->post(route('admin.projects.categories.store'), ['lang_id' => $langId, 'name' => 'Tamamlanan işler', 'slug' => 'tamamlanan-isler', 'icon' => 'check'])
        ->assertSessionHasNoErrors()->assertRedirect(route('admin.projects.categories.edit', ['category' => ProjectCategory::query()->sole()->id, 'lang' => 'tr']));
    $category = ProjectCategory::query()->sole();
    $this->post(route('admin.projects.categories.store'), ['lang_id' => $langId, 'name' => 'Başka kategori', 'slug' => 'tamamlanan-isler'])
        ->assertSessionHasErrors(['slug' => 'Bu kısa bağlantı zaten kullanılıyor.']);
    $this->put(route('admin.projects.categories.update', $category), ['lang_id' => $langId, 'name' => 'Tamamlandı', 'slug' => 'tamamlanan-isler'])->assertSessionHasNoErrors();
    $this->assertDatabaseHas('project_category_translations', ['project_category_id' => $category->id, 'lang_id' => $langId, 'name' => 'Tamamlandı']);
    $this->get(route('admin.projects.categories.edit', $category))->assertOk();
    $this->get(route('admin.projects.categories.index'))->assertOk();
    $this->delete(route('admin.projects.categories.destroy', $category))->assertRedirectToRoute('admin.projects.categories.index');
    $this->assertModelMissing($category);
});

test('projects persist multiple existing images a selected cover and one project date', function () {
    Storage::fake('public');
    $category = ProjectCategory::factory()->create();
    $first = projectTestImage('first.png');
    $cover = projectTestImage('cover.png');
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin');
    $this->get(route('admin.projects.create'))->assertOk()->assertSee('data-slug-title', false)->assertSee('data-media-multiple="true"', false)->assertSee('name="project_date"', false);

    $this->post(route('admin.projects.store'), [
        'lang_id' => Language::query()->where('code', 'tr')->value('id'), 'title' => 'Ofis projesi', 'slug' => 'ofis-projesi', 'project_category_id' => $category->id,
        'project_date' => '2026-10-18', 'content' => '<p>İş tamamlandı.</p><script>alert(1)</script>',
        'is_active' => 1, 'sort_order' => 4, 'image_media_ids' => json_encode([$first->id, $cover->id]), 'cover_media_id' => $cover->id,
    ])->assertSessionHasNoErrors()->assertRedirect(route('admin.projects.edit', ['project' => Project::query()->sole()->id, 'lang' => 'tr']));

    $project = Project::query()->sole();
    $this->assertDatabaseHas('project_translations', ['project_id' => $project->id, 'title' => 'Ofis projesi', 'content' => '<p>İş tamamlandı.</p>']);
    $this->assertDatabaseHas('projects', ['id' => $project->id, 'cover_media_id' => $cover->id, 'sort_order' => 4, 'is_active' => true]);
    expect($project->project_date->format('Y-m-d'))->toBe('2026-10-18');
    expect($project->images->modelKeys())->toBe([$first->id, $cover->id]);
    $this->assertDatabaseHas('media_usages', ['owner_type' => Project::class, 'owner_id' => $project->id, 'media_id' => $cover->id]);
    $this->get(route('admin.projects.edit', $project))->assertOk()->assertSee('Ofis projesi')->assertSee('name="cover_media_id"', false);
    $this->deleteJson(route('admin.media.destroy', $cover))->assertUnprocessable();
    Storage::disk('public')->assertExists($cover->path);
});

test('projects update selected images clear stale usages and delete without deleting shared files', function () {
    Storage::fake('public');
    $category = ProjectCategory::factory()->create();
    $first = projectTestImage('old.png');
    $second = projectTestImage('new.png');
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin');
    $payload = ['lang_id' => Language::query()->where('code', 'tr')->value('id'), 'title' => 'Proje', 'slug' => 'proje', 'project_category_id' => $category->id, 'is_active' => 0, 'sort_order' => 0];
    $this->post(route('admin.projects.store'), $payload + ['image_media_ids' => [$first->id, $second->id]])->assertSessionHasNoErrors();
    $project = Project::query()->sole();
    expect($project->cover_media_id)->toBe($first->id);

    $this->put(route('admin.projects.update', $project), $payload + ['image_media_ids' => [$second->id], 'cover_media_id' => $second->id])->assertSessionHasNoErrors();
    expect($project->fresh()->images->modelKeys())->toBe([$second->id]);
    $this->assertDatabaseMissing('media_usages', ['owner_type' => Project::class, 'media_id' => $first->id]);
    $this->delete(route('admin.projects.destroy', $project))->assertRedirectToRoute('admin.projects.index');
    $this->assertModelMissing($project);
    $this->assertDatabaseCount('project_images', 0);
    $this->assertDatabaseMissing('media_usages', ['owner_type' => Project::class, 'owner_id' => $project->id]);
    Storage::disk('public')->assertExists([$first->path, $second->path]);
});

test('projects reject a cover outside the chosen images and leave existing project untouched', function () {
    Storage::fake('public');
    $project = Project::factory()->create();
    $project->translations()->first()->update(['title' => 'Original']);
    $selected = projectTestImage('selected.png');
    $other = projectTestImage('other.png');
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin');

    $this->put(route('admin.projects.update', $project), [
        'lang_id' => Language::query()->where('code', 'tr')->value('id'), 'title' => 'Changed', 'slug' => $project->translations()->first()->slug, 'project_category_id' => $project->project_category_id,
        'is_active' => 1, 'sort_order' => 1, 'image_media_ids' => [$selected->id], 'cover_media_id' => $other->id,
    ])->assertSessionHasErrors(['cover_media_id' => 'Kapak, seçilen proje görsellerinden biri olmalıdır.']);

    $this->assertDatabaseHas('project_translations', ['project_id' => $project->id, 'title' => 'Original']);
    $this->assertDatabaseHas('projects', ['id' => $project->id, 'cover_media_id' => null, 'is_active' => false]);
    $this->assertDatabaseCount('project_images', 0);
    Storage::disk('public')->assertExists([$selected->path, $other->path]);
});

test('projects allow clearing all images and preserve valid slugs during edits', function () {
    Storage::fake('public');
    $project = Project::factory()->create();
    $image = projectTestImage('clear.png');
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin');
    $payload = ['lang_id' => Language::query()->where('code', 'tr')->value('id'), 'title' => $project->translations()->first()->title, 'slug' => $project->translations()->first()->slug, 'project_category_id' => $project->project_category_id, 'is_active' => 0, 'sort_order' => 0];
    $this->put(route('admin.projects.update', $project), $payload + ['image_media_ids' => [$image->id]])->assertSessionHasNoErrors();

    $this->put(route('admin.projects.update', $project), $payload + ['image_media_ids' => '[]', 'cover_media_id' => null])->assertSessionHasNoErrors();

    $this->assertDatabaseHas('projects', ['id' => $project->id, 'cover_media_id' => null]);
    $this->assertDatabaseCount('project_images', 0);
    $this->assertDatabaseMissing('media_usages', ['owner_type' => Project::class, 'owner_id' => $project->id]);
    Storage::disk('public')->assertExists($image->path);
});

test('projects reject invalid date duplicate slug missing category and invalid order', function (string $field, mixed $value) {
    $project = Project::factory()->create();
    $project->translations()->first()->update(['slug' => 'existing']);
    $payload = ['lang_id' => Language::query()->where('code', 'tr')->value('id'), 'title' => 'New', 'slug' => 'new', 'project_category_id' => $project->project_category_id, 'project_date' => '2026-10-18', 'is_active' => 0, 'sort_order' => 0, 'image_media_ids' => []];
    $payload[$field] = $value;

    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin')->post(route('admin.projects.store'), $payload)->assertSessionHasErrors($field);
    $this->assertDatabaseCount('projects', 1);
})->with(['invalid date' => ['project_date', '2026-02-30'], 'duplicate slug' => ['slug', 'existing'], 'unknown category' => ['project_category_id', 99999], 'negative order' => ['sort_order', -1], 'empty title' => ['title', '']]);

test('projects prevent deletion of categories containing projects', function () {
    $project = Project::factory()->create();
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin')->delete(route('admin.projects.categories.destroy', $project->category))->assertSessionHasErrors('category');
    $this->assertModelExists($project);
    $this->assertModelExists($project->category);
});

test('project and category translations use one shared category while protecting shared project fields', function () {
    Storage::fake('public');
    $tr = Language::query()->firstOrCreate(['code' => 'tr'], ['is_active' => true]);
    $en = Language::factory()->create(['code' => 'en', 'is_active' => true]);
    $category = ProjectCategory::factory()->create(['icon' => 'check']);
    $project = Project::factory()->for($category, 'category')->create(['is_active' => true, 'sort_order' => 9, 'project_date' => '2026-10-18']);
    $image = projectTestImage('shared-gallery.png');
    $project->images()->attach($image->id, ['sort_order' => 0]);
    $project->update(['cover_media_id' => $image->id]);
    $beforeImages = $project->images()->pluck('media_files.id')->all();
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin');

    $this->get(route('admin.projects.edit', ['project' => $project->id, 'lang' => 'en']))->assertOk()->assertSee('name="lang_id" value="'.$en->id.'"', false)->assertSee('Henüz eklenmedi');
    $this->put(route('admin.projects.update', $project), ['lang_id' => $en->id, 'title' => 'Office project', 'slug' => 'ofis-projesi', 'content' => '<p>English summary</p>'])
        ->assertSessionHasNoErrors()->assertRedirect(route('admin.projects.edit', ['project' => $project->id, 'lang' => 'en']));
    $this->put(route('admin.projects.categories.update', $category), ['lang_id' => $en->id, 'name' => 'Completed work', 'slug' => 'tamamlanan-isler'])
        ->assertSessionHasNoErrors();

    expect($project->fresh()->project_category_id)->toBe($category->id)
        ->and($project->fresh()->is_active)->toBeTrue()
        ->and($project->fresh()->sort_order)->toBe(9)
        ->and($project->fresh()->project_date->format('Y-m-d'))->toBe('2026-10-18')
        ->and($project->fresh()->cover_media_id)->toBe($image->id)
        ->and($project->fresh()->images()->pluck('media_files.id')->all())->toBe($beforeImages)
        ->and($category->fresh()->icon)->toBe('check');
    $this->assertDatabaseHas('project_translations', ['project_id' => $project->id, 'lang_id' => $en->id, 'title' => 'Office project']);
    $this->assertDatabaseHas('project_category_translations', ['project_category_id' => $category->id, 'lang_id' => $en->id, 'name' => 'Completed work']);
    expect($tr->is_active)->toBeTrue();
});

test('project slugs are unique only within the active language and inactive languages are rejected', function () {
    $tr = Language::query()->firstOrCreate(['code' => 'tr'], ['is_active' => true]);
    $en = Language::factory()->create(['code' => 'en', 'is_active' => true]);
    $inactive = Language::factory()->inactive()->create(['code' => 'fr']);
    $project = Project::factory()->create();
    $otherProject = Project::factory()->create();
    $trTranslation = $project->translations()->where('lang_id', $tr->id)->firstOrFail();
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin');

    $this->put(route('admin.projects.update', $project), ['lang_id' => $en->id, 'title' => 'English title', 'slug' => $trTranslation->slug])
        ->assertSessionHasNoErrors();
    $this->put(route('admin.projects.update', $otherProject), ['lang_id' => $en->id, 'title' => 'Duplicate title', 'slug' => $trTranslation->slug])
        ->assertSessionHasErrors('slug');
    $this->put(route('admin.projects.update', $project), ['lang_id' => $inactive->id, 'title' => 'Inactive title', 'slug' => 'inactive-title'])
        ->assertSessionHasErrors('lang_id');
    $this->assertDatabaseMissing('project_translations', ['project_id' => $project->id, 'lang_id' => $inactive->id]);
});

test('projects public scope returns published records in configured order', function () {
    $category = ProjectCategory::factory()->create();
    $later = Project::factory()->create(['project_category_id' => $category->id, 'is_active' => true, 'sort_order' => 8]);
    Project::factory()->create(['project_category_id' => $category->id, 'is_active' => false, 'sort_order' => 0]);
    $first = Project::factory()->create(['project_category_id' => $category->id, 'is_active' => true, 'sort_order' => 1]);

    expect(Project::published()->pluck('id')->all())->toBe([$first->id, $later->id]);
});

test('project tables escape content and limit actions to permissions', function () {
    app(ModulePanelService::class)->syncPermissions();
    $admin = Admin::factory()->create();
    $admin->givePermissionTo(['projects.view', 'projects.update', 'projects.categories.view']);
    $project = Project::factory()->create();
    $project->translations()->first()->update(['title' => '<script>alert(1)</script>']);
    $this->actingAs($admin, 'admin')->get(route('admin.projects.index'))->assertOk();
    $this->getJson(route('admin.projects.data'))->assertOk()->assertJsonPath('recordsTotal', 1)
        ->assertJsonPath('data.0.title', '&lt;script&gt;alert(1)&lt;/script&gt;')
        ->assertJsonPath('data.0.actions', fn (string $actions): bool => str_contains($actions, 'Düzenle') && ! str_contains($actions, '>Sil<'));
    $this->getJson(route('admin.projects.categories.data'))->assertOk()->assertJsonPath('recordsTotal', 1);
});

test('projects require authentication write and media permissions and module activation', function () {
    $this->get(route('admin.projects.index'))->assertRedirectToRoute('admin.login');
    $this->actingAs(Admin::factory()->create(), 'admin')->get(route('admin.projects.index'))->assertForbidden();
    $this->post(route('admin.projects.store'), [])->assertForbidden();
    $this->post(route('admin.projects.categories.store'), [])->assertForbidden();

    Storage::fake('public');
    app(ModulePanelService::class)->syncPermissions();
    $admin = Admin::factory()->create();
    $admin->givePermissionTo('projects.create');
    $category = ProjectCategory::factory()->create();
    $image = projectTestImage('no-access.png');
    $this->actingAs($admin, 'admin')->post(route('admin.projects.store'), ['lang_id' => Language::query()->where('code', 'tr')->value('id'), 'title' => 'No access', 'slug' => 'no-access', 'project_category_id' => $category->id, 'is_active' => 0, 'sort_order' => 0, 'image_media_ids' => [$image->id]])->assertForbidden();
    $this->assertDatabaseCount('projects', 0);
    Storage::disk('public')->assertExists($image->path);

    $disk = Storage::fake('disabled-projects');
    $disk->put('statuses.json', json_encode(['Projects' => false]));
    config(['modules.activators.file.statuses-file' => $disk->path('statuses.json')]);
    app()->forgetInstance(ActivatorInterface::class);
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin')->get(route('admin.projects.index'))->assertNotFound();
});
