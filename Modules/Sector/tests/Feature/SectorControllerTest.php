<?php

use App\Models\Admin\Admin;
use App\Models\Media\MediaFile;
use App\Models\Settings\Language;
use App\Services\Admin\Modules\ModulePanelService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Sector\Models\Sector;
use Nwidart\Modules\Contracts\ActivatorInterface;
use Nwidart\Modules\Facades\Module;
use Nwidart\Modules\Laravel\LaravelFileRepository;

uses(LazilyRefreshDatabase::class);

function sectorTestImage(string $name, bool $oversized = false): MediaFile
{
    $upload = UploadedFile::fake()->image($name);
    $path = $upload->store('_media', 'public');
    if ($oversized) {
        $contents = Storage::disk('public')->get($path);
        Storage::disk('public')->put($path, $contents.str_repeat("\0", max(0, 4097 * 1024 - strlen($contents))));
    }

    return MediaFile::factory()->create(['path' => $path, 'name' => $name, 'kind' => 'image', 'mime_type' => 'image/png', 'extension' => 'png', 'size' => Storage::disk('public')->size($path)]);
}

function sectorTestDocument(): MediaFile
{
    $upload = UploadedFile::fake()->create('document.pdf', 10, 'application/pdf');
    $path = $upload->store('_media', 'public');

    return MediaFile::factory()->create(['path' => $path, 'name' => 'document.pdf', 'kind' => 'document', 'mime_type' => 'application/pdf', 'extension' => 'pdf', 'size' => Storage::disk('public')->size($path)]);
}

test('sector list has a single menu and create button and its forms render all content and SEO fields', function () {
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin');
    $sector = Sector::factory()->create();
    $sector->translations()->where('lang_id', Language::query()->where('code', 'tr')->value('id'))->update(['title' => '<script>Title</script>', 'content' => '<script>Content</script>', 'meta_description' => '<script>Meta</script>']);

    $response = $this->get(route('admin.sector.index'))->assertOk()->assertSee('id="sectors-table"', false)
        ->assertSee('href="'.route('admin.sector.create').'"', false)->assertSee('Sektör ekle');
    expect(substr_count($response->getContent(), 'href="'.route('admin.sector.index').'"'))->toBe(1);
    $this->get(route('admin.sector.create'))->assertOk()->assertSeeInOrder([
        'name="image_media_id"', 'name="title"', 'name="short_description"', 'name="content"',
        'name="is_active"', 'name="meta_title"', 'name="meta_keywords"', 'name="meta_description"',
    ], false);
    $this->get(route('admin.sector.edit', ['sector' => $sector, 'lang' => 'tr']))->assertOk()
        ->assertSee('&lt;script&gt;Title&lt;/script&gt;', false)->assertSee('&lt;script&gt;Content&lt;/script&gt;', false)
        ->assertSee('&lt;script&gt;Meta&lt;/script&gt;', false)->assertDontSee('<script>Content</script>', false);
});

test('sector persists content SEO and status and manages its image through updates and deletion', function () {
    Storage::fake('public');
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin');
    $payload = ['title' => 'Otomotiv', 'short_description' => 'Kısa içerik', 'content' => 'Detaylı içerik',
        'meta_title' => 'Otomotiv sektörü', 'meta_keywords' => 'otomotiv,servis', 'meta_description' => 'Meta açıklama', 'is_active' => '1'];

    $first = sectorTestImage('first.png');
    $this->post(route('admin.sector.store'), [...$payload, 'lang_id' => Language::query()->where('code', 'tr')->value('id'), 'sort_order' => 0, 'image_media_id' => $first->id])
        ->assertSessionHasNoErrors()->assertRedirectToRoute('admin.sector.edit', ['sector' => 1, 'lang' => 'tr']);
    $sector = Sector::query()->sole();
    $firstImage = $sector->image;
    $this->assertDatabaseHas('sector_translations', ['sector_id' => $sector->id, 'title' => 'Otomotiv', 'content' => 'Detaylı içerik', 'meta_description' => 'Meta açıklama']);
    Storage::disk('public')->assertExists($firstImage);

    $this->put(route('admin.sector.update', ['sector' => $sector, 'lang' => 'tr']), [...$payload, 'lang_id' => Language::query()->where('code', 'tr')->value('id'), 'sort_order' => 0, 'is_active' => '0'])->assertSessionHasNoErrors();
    expect($sector->fresh()->is_active)->toBeFalse();
    expect($sector->fresh()->image)->toBe($firstImage);
    $second = sectorTestImage('second.png');
    $this->put(route('admin.sector.update', ['sector' => $sector, 'lang' => 'tr']), [...$payload, 'lang_id' => Language::query()->where('code', 'tr')->value('id'), 'sort_order' => 0, 'image_media_id' => $second->id])->assertSessionHasNoErrors();
    $secondImage = $sector->fresh()->image;
    Storage::disk('public')->assertExists($firstImage);
    Storage::disk('public')->assertExists($secondImage);

    $this->delete(route('admin.sector.destroy', $sector))->assertRedirectToRoute('admin.sector.index');
    $this->assertModelMissing($sector);
    Storage::disk('public')->assertExists($secondImage);
    $this->assertDatabaseMissing('media_usages', ['owner_type' => Sector::class, 'owner_id' => $sector->id]);
});

test('sector accepts optional empty fields and removes an optional image explicitly', function () {
    Storage::fake('public');
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin');
    $this->post(route('admin.sector.store'), ['title' => 'Sanayi', 'lang_id' => Language::query()->where('code', 'tr')->value('id'), 'is_active' => '0'])->assertSessionHasNoErrors();
    $sector = Sector::query()->sole();
    expect($sector->image)->toBeNull();
    expect($sector->content)->toBeNull();
    $media = sectorTestImage('photo.png');
    $this->put(route('admin.sector.update', ['sector' => $sector, 'lang' => 'tr']), ['title' => 'Sanayi', 'lang_id' => Language::query()->where('code', 'tr')->value('id'), 'sort_order' => 0, 'is_active' => '1', 'image_media_id' => $media->id])->assertSessionHasNoErrors();

    $replacement = sectorTestImage('conflict.png');
    $this->put(route('admin.sector.update', ['sector' => $sector, 'lang' => 'tr']), ['title' => 'Sanayi', 'lang_id' => Language::query()->where('code', 'tr')->value('id'), 'sort_order' => 0, 'is_active' => '1', 'remove_image' => '1', 'image_media_id' => $replacement->id])
        ->assertSessionHasErrors('image_media_id');
    Storage::disk('public')->assertCount('_media', 2);
    $this->put(route('admin.sector.update', ['sector' => $sector, 'lang' => 'tr']), ['title' => 'Sanayi', 'lang_id' => Language::query()->where('code', 'tr')->value('id'), 'sort_order' => 0, 'is_active' => '1', 'remove_image' => '1'])->assertSessionHasNoErrors();
    expect($sector->fresh()->image)->toBeNull();
    Storage::disk('public')->assertExists($media->path);
    $this->assertDatabaseMissing('media_usages', ['owner_type' => Sector::class, 'owner_id' => $sector->id]);
});

test('sector rejects invalid fields before storing uploaded files', function (string $field, mixed $value, string $message) {
    Storage::fake('public');
    $payload = ['title' => 'Sektör', 'lang_id' => Language::query()->where('code', 'tr')->value('id'), 'is_active' => '1', $field => $value];

    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin')->post(route('admin.sector.store'), $payload)
        ->assertSessionHasErrors([$field => $message]);
    $this->assertDatabaseCount('sectors', 0);
    Storage::disk('public')->assertEmpty();
})->with([
    'missing title' => ['title', '', 'Başlık alanını doldurun.'],
    'long title' => ['title', str_repeat('x', 256), 'Başlık en fazla 255 karakter olabilir.'],
    'long summary' => ['short_description', str_repeat('x', 1001), 'Kısa içerik en fazla 1000 karakter olabilir.'],
    'invalid content type' => ['content', ['invalid'], 'İçerik metin olmalıdır.'],
    'long content' => ['content', str_repeat('x', 65536), 'İçerik en fazla 65535 karakter olabilir.'],
    'long SEO title' => ['meta_title', str_repeat('x', 256), 'Meta başlık en fazla 255 karakter olabilir.'],
    'long SEO keywords' => ['meta_keywords', str_repeat('x', 1001), 'Meta anahtar en fazla 1000 karakter olabilir.'],
    'long SEO description' => ['meta_description', str_repeat('x', 1001), 'Meta açıklama en fazla 1000 karakter olabilir.'],
    'invalid state' => ['is_active', 'enabled', 'Durum geçerli bir açık / kapalı değeri olmalıdır.'],
    'invalid media selection' => ['image_media_id', 999999, 'Kütüphaneden aktif ve geçerli bir dosya seçin.'],
]);

test('sector rejects non image uploads and oversized images', function (bool $oversized) {
    Storage::fake('public');
    $image = $oversized ? sectorTestImage('large.png', true) : sectorTestDocument();
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin')
        ->post(route('admin.sector.store'), ['title' => 'Sektör', 'lang_id' => Language::query()->where('code', 'tr')->value('id'), 'is_active' => '1', 'image_media_id' => $image->id])
        ->assertSessionHasErrors('image_media_id');
    $this->assertDatabaseCount('sectors', 0);
    Storage::disk('public')->assertCount('_media', 1);
})->with([true, false]);

test('sector table escapes titles and returns active and inactive badges and authorized actions', function () {
    app(ModulePanelService::class)->syncPermissions();
    $admin = Admin::factory()->create();
    $admin->givePermissionTo(['sector.view', 'sector.update']);
    $escaped = Sector::factory()->create();
    $escaped->translations()->update(['title' => '<script>Title</script>']);
    Sector::factory()->inactive()->create();

    $this->actingAs($admin, 'admin')->getJson(route('admin.sector.data'))->assertOk()->assertJsonPath('recordsTotal', 2)
        ->assertJsonPath('data.0.title', '&lt;script&gt;Title&lt;/script&gt;')
        ->assertJsonPath('data.0.is_active', fn (string $status): bool => str_contains($status, 'Aktif'))
        ->assertJsonPath('data.1.is_active', fn (string $status): bool => str_contains($status, 'Pasif'))
        ->assertJsonPath('data.0.actions', fn (string $actions): bool => str_contains($actions, 'Düzenle') && ! str_contains($actions, '>Sil<'));
});

test('sector connects configured extra fields to its forms validation and persisted values', function () {
    $disk = Storage::fake('sector-feature-definitions');
    $disk->put('Sector/module.json', json_encode(['name' => 'Sector', 'providers' => []], JSON_THROW_ON_ERROR));
    $disk->put('Sector/features.json', json_encode(['schema_version' => 1, 'forms' => ['sector' => ['label' => 'Ek sektör alanları', 'fields' => [
        ['key' => 'extra_description', 'label' => 'Ek açıklama', 'input_type' => 'textarea', 'value_type' => 'string', 'validation' => ['max' => 50]],
    ]]]], JSON_THROW_ON_ERROR));
    $disk->put('statuses.json', json_encode(['Sector' => true], JSON_THROW_ON_ERROR));
    config(['modules.activators.file.statuses-file' => $disk->path('statuses.json')]);
    app()->forgetInstance(ActivatorInterface::class);
    Module::swap(new LaravelFileRepository(app(), $disk->path('')));
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin');

    $this->get(route('admin.sector.create'))->assertOk()->assertSee('name="extra_fields[extra_description]"', false);
    $this->post(route('admin.sector.store'), ['title' => 'Otomotiv', 'lang_id' => Language::query()->where('code', 'tr')->value('id'), 'sort_order' => 0, 'is_active' => '1', 'extra_fields' => ['extra_description' => '<script>Metin</script>']])
        ->assertSessionHasNoErrors()->assertRedirectToRoute('admin.sector.edit', ['sector' => 1, 'lang' => 'tr']);
    $sector = Sector::query()->sole();
    expect($sector->translations()->where('lang_id', Language::query()->where('code', 'tr')->value('id'))->firstOrFail()->extra_fields)->toBe(['extra_description' => '<script>Metin</script>']);
    $this->get(route('admin.sector.edit', ['sector' => $sector, 'lang' => 'tr']))->assertOk()->assertSee('&lt;script&gt;Metin&lt;/script&gt;', false);
    $this->put(route('admin.sector.update', ['sector' => $sector, 'lang' => 'tr']), ['title' => 'Otomotiv', 'lang_id' => Language::query()->where('code', 'tr')->value('id'), 'sort_order' => 0, 'is_active' => '1', 'extra_fields' => ['extra_description' => str_repeat('a', 51)]])
        ->assertSessionHasErrors(['extra_fields.extra_description' => 'Ek açıklama en fazla 50 karakter olabilir.']);
    expect($sector->translations()->where('lang_id', Language::query()->where('code', 'tr')->value('id'))->firstOrFail()->extra_fields)->toBe(['extra_description' => '<script>Metin</script>']);
});

test('sector rejects undefined extra fields', function () {
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin')
        ->post(route('admin.sector.store'), ['title' => 'Otomotiv', 'lang_id' => Language::query()->where('code', 'tr')->value('id'), 'sort_order' => 0, 'is_active' => '1', 'extra_fields' => ['unknown' => 'injected']])
        ->assertSessionHasErrors('extra_fields');

    $this->assertDatabaseCount('sectors', 0);
});

test('sector endpoints enforce their operation permissions', function (string $method, string $action, string $permission, bool $granted) {
    Storage::fake('public');
    app(ModulePanelService::class)->syncPermissions();
    $admin = Admin::factory()->create();
    if ($granted) {
        $admin->givePermissionTo($permission);
    }
    $sector = Sector::factory()->create();
    $sectorCountBeforeRequest = Sector::query()->count();
    $sector->translations()->update(['title' => 'Original']);
    $parameters = in_array($action, ['edit', 'update', 'destroy'], true) ? $sector : [];

    $response = $this->actingAs($admin, 'admin')->{$method}(route('admin.sector.'.$action, $parameters), [
        'title' => 'Updated', 'lang_id' => Language::query()->where('code', 'tr')->value('id'), 'sort_order' => 0, 'is_active' => '0',
    ]);

    if (! $granted) {
        $response->assertForbidden();
        expect($sector->fresh()->translations()->where('lang_id', Language::query()->where('code', 'tr')->value('id'))->value('title'))->toBe('Original');
        $this->assertDatabaseCount('sectors', 1);
    } elseif ($action === 'destroy') {
        $response->assertRedirectToRoute('admin.sector.index');
        $this->assertModelMissing($sector);
    } elseif (in_array($action, ['store', 'update'], true)) {
        $response->assertRedirectToRoute('admin.sector.edit', ['sector' => $action === 'store' ? $sectorCountBeforeRequest + 1 : $sector->id, 'lang' => 'tr']);
        $this->assertDatabaseHas('sector_translations', ['title' => 'Updated']);
        if ($action === 'update') {
            $this->assertDatabaseHas('sectors', ['id' => $sector->id, 'is_active' => false]);
        }
    } else {
        $response->assertOk();
    }
    Storage::disk('public')->assertEmpty();
})->with([
    'index' => ['get', 'index', 'sector.view'],
    'data' => ['get', 'data', 'sector.view'],
    'create' => ['get', 'create', 'sector.create'],
    'store' => ['post', 'store', 'sector.create'],
    'edit' => ['get', 'edit', 'sector.update'],
    'update' => ['put', 'update', 'sector.update'],
    'destroy' => ['delete', 'destroy', 'sector.delete'],
])->with([true, false]);

test('sector menu and page permissions remain independent', function (array $permissions, bool $visible, bool $accessible) {
    app(ModulePanelService::class)->syncPermissions();
    $admin = Admin::factory()->create();
    $admin->givePermissionTo($permissions);
    $this->actingAs($admin, 'admin');

    $dashboard = $this->get(route('admin.dashboard'))->assertOk();
    if ($visible) {
        $dashboard->assertSee('href="'.route('admin.sector.index').'"', false);
    } else {
        $dashboard->assertDontSee('href="'.route('admin.sector.index').'"', false);
    }
    $index = $this->get(route('admin.sector.index'));
    if ($accessible) {
        $index->assertOk()->assertDontSee('href="'.route('admin.sector.create').'"', false);
    } else {
        $index->assertForbidden();
    }
})->with([
    'menu alone' => [['sector.menu'], false, false],
    'page alone' => [['sector.view'], false, true],
    'menu and page' => [['sector.menu', 'sector.view'], true, true],
]);

test('sector blocks guests and inactive accounts', function () {
    $this->get(route('admin.sector.index'))->assertRedirectToRoute('admin.login');
    $this->actingAs(Admin::factory()->superAdmin()->inactive()->create(), 'admin')
        ->get(route('admin.sector.index'))->assertRedirectToRoute('admin.login');
});

test('disabled sector module is hidden and all endpoints reject even super admins', function (string $method, string $action) {
    $disk = Storage::fake('disabled-sector');
    $disk->put('statuses.json', json_encode(['Services' => true, 'Slider' => true, 'Sector' => false], JSON_THROW_ON_ERROR));
    config(['modules.activators.file.statuses-file' => $disk->path('statuses.json')]);
    app()->forgetInstance(ActivatorInterface::class);
    $sector = Sector::factory()->create();
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin');
    $parameters = in_array($action, ['edit', 'update', 'destroy'], true) ? $sector : [];

    $this->get(route('admin.dashboard'))->assertOk()->assertDontSee('href="'.route('admin.sector.index').'"', false);
    $this->{$method}(route('admin.sector.'.$action, $parameters), ['title' => 'Forbidden', 'is_active' => '1'])->assertNotFound();

    $this->assertModelExists($sector);
    $this->assertDatabaseCount('sectors', 1);
})->with([
    ['get', 'index'], ['get', 'data'], ['get', 'create'], ['post', 'store'],
    ['get', 'edit'], ['put', 'update'], ['delete', 'destroy'],
]);
