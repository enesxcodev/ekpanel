<?php

use App\Models\Admin\Admin;
use App\Services\Admin\Modules\ModulePanelService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Sector\Models\Sector;
use Nwidart\Modules\Contracts\ActivatorInterface;
use Nwidart\Modules\Facades\Module;
use Nwidart\Modules\Laravel\LaravelFileRepository;

uses(LazilyRefreshDatabase::class);

test('sector list has a single menu and create button and its forms render all content and SEO fields', function () {
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin');
    $sector = Sector::factory()->create(['title' => '<script>Title</script>', 'content' => '<script>Content</script>', 'meta_description' => '<script>Meta</script>']);

    $response = $this->get(route('admin.sector.index'))->assertOk()->assertSee('id="sectors-table"', false)
        ->assertSee('href="'.route('admin.sector.create').'"', false)->assertSee('Sektör ekle');
    expect(substr_count($response->getContent(), 'href="'.route('admin.sector.index').'"'))->toBe(1);
    $this->get(route('admin.sector.create'))->assertOk()->assertSeeInOrder([
        'name="image"', 'name="title"', 'name="short_description"', 'name="content"',
        'name="meta_title"', 'name="meta_keywords"', 'name="meta_description"', 'name="is_active"',
    ], false);
    $this->get(route('admin.sector.edit', $sector))->assertOk()
        ->assertSee('&lt;script&gt;Title&lt;/script&gt;', false)->assertSee('&lt;script&gt;Content&lt;/script&gt;', false)
        ->assertSee('&lt;script&gt;Meta&lt;/script&gt;', false)->assertDontSee('<script>Content</script>', false);
});

test('sector persists content SEO and status and manages its image through updates and deletion', function () {
    Storage::fake('public');
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin');
    $payload = ['title' => 'Otomotiv', 'short_description' => 'Kısa içerik', 'content' => 'Detaylı içerik',
        'meta_title' => 'Otomotiv sektörü', 'meta_keywords' => 'otomotiv,servis', 'meta_description' => 'Meta açıklama', 'is_active' => '1'];

    $this->post(route('admin.sector.store'), [...$payload, 'image' => UploadedFile::fake()->image('first.png')])
        ->assertSessionHasNoErrors()->assertRedirectToRoute('admin.sector.index');
    $sector = Sector::query()->sole();
    $firstImage = $sector->image;
    $this->assertDatabaseHas('sectors', [...$payload, 'id' => $sector->id]);
    Storage::disk('public')->assertExists($firstImage);

    $this->put(route('admin.sector.update', $sector), [...$payload, 'is_active' => '0'])->assertSessionHasNoErrors();
    expect($sector->fresh()->is_active)->toBeFalse();
    expect($sector->fresh()->image)->toBe($firstImage);
    $this->put(route('admin.sector.update', $sector), [...$payload, 'image' => UploadedFile::fake()->image('second.png')])->assertSessionHasNoErrors();
    $secondImage = $sector->fresh()->image;
    Storage::disk('public')->assertMissing($firstImage);
    Storage::disk('public')->assertExists($secondImage);

    $this->delete(route('admin.sector.destroy', $sector))->assertRedirectToRoute('admin.sector.index');
    $this->assertModelMissing($sector);
    Storage::disk('public')->assertMissing($secondImage);
});

test('sector accepts optional empty fields and removes an optional image explicitly', function () {
    Storage::fake('public');
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin');
    $this->post(route('admin.sector.store'), ['title' => 'Sanayi', 'is_active' => '0'])->assertSessionHasNoErrors();
    $sector = Sector::query()->sole();
    expect($sector->image)->toBeNull();
    expect($sector->content)->toBeNull();
    $this->put(route('admin.sector.update', $sector), ['title' => 'Sanayi', 'is_active' => '1', 'image' => UploadedFile::fake()->image('photo.png')])->assertSessionHasNoErrors();
    $image = $sector->fresh()->image;

    $this->put(route('admin.sector.update', $sector), ['title' => 'Sanayi', 'is_active' => '1', 'remove_image' => '1', 'image' => UploadedFile::fake()->image('conflict.png')])
        ->assertSessionHasErrors(['image' => 'Yeni görsel yükleme ve mevcut görseli kaldırma işlemlerinden birini seçin.']);
    Storage::disk('public')->assertCount('sector/images', 1);
    $this->put(route('admin.sector.update', $sector), ['title' => 'Sanayi', 'is_active' => '1', 'remove_image' => '1'])->assertSessionHasNoErrors();
    expect($sector->fresh()->image)->toBeNull();
    Storage::disk('public')->assertMissing($image);
});

test('sector rejects invalid fields before storing uploaded files', function (string $field, mixed $value, string $message) {
    Storage::fake('public');
    $payload = ['title' => 'Sektör', 'is_active' => '1', 'image' => UploadedFile::fake()->image('photo.png'), $field => $value];

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
    'image path injection' => ['image', 'existing.png', 'Görsel geçerli bir görsel olmalıdır.'],
]);

test('sector rejects non image uploads and oversized images', function (bool $oversized) {
    Storage::fake('public');
    $image = $oversized ? UploadedFile::fake()->image('large.png')->size(4097) : UploadedFile::fake()->create('document.pdf', 10, 'application/pdf');
    $message = $oversized ? 'Görsel en fazla 4096 KB olabilir.' : 'Görsel geçerli bir görsel olmalıdır.';
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin')
        ->post(route('admin.sector.store'), ['title' => 'Sektör', 'is_active' => '1', 'image' => $image])
        ->assertSessionHasErrors(['image' => $message]);
    $this->assertDatabaseCount('sectors', 0);
    Storage::disk('public')->assertEmpty();
})->with([true, false]);

test('sector table escapes titles and returns active and inactive badges and authorized actions', function () {
    app(ModulePanelService::class)->syncPermissions();
    $admin = Admin::factory()->create();
    $admin->givePermissionTo(['sector.view', 'sector.update']);
    Sector::factory()->create(['title' => '<script>Title</script>']);
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
    $this->post(route('admin.sector.store'), ['title' => 'Otomotiv', 'is_active' => '1', 'extra_fields' => ['extra_description' => '<script>Metin</script>']])
        ->assertSessionHasNoErrors()->assertRedirectToRoute('admin.sector.index');
    $sector = Sector::query()->sole();
    expect($sector->extra_fields)->toBe(['extra_description' => '<script>Metin</script>']);
    $this->get(route('admin.sector.edit', $sector))->assertOk()->assertSee('&lt;script&gt;Metin&lt;/script&gt;', false);
    $this->put(route('admin.sector.update', $sector), ['title' => 'Otomotiv', 'is_active' => '1', 'extra_fields' => ['extra_description' => str_repeat('a', 51)]])
        ->assertSessionHasErrors(['extra_fields.extra_description' => 'Ek açıklama en fazla 50 karakter olabilir.']);
    expect($sector->fresh()->extra_fields)->toBe(['extra_description' => '<script>Metin</script>']);
});

test('sector rejects undefined extra fields', function () {
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin')
        ->post(route('admin.sector.store'), ['title' => 'Otomotiv', 'is_active' => '1', 'extra_fields' => ['unknown' => 'injected']])
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
    $sector = Sector::factory()->create(['title' => 'Original']);
    $parameters = in_array($action, ['edit', 'update', 'destroy'], true) ? $sector : [];

    $response = $this->actingAs($admin, 'admin')->{$method}(route('admin.sector.'.$action, $parameters), [
        'title' => 'Updated', 'is_active' => '0',
    ]);

    if (! $granted) {
        $response->assertForbidden();
        expect($sector->fresh()->title)->toBe('Original');
        $this->assertDatabaseCount('sectors', 1);
    } elseif ($action === 'destroy') {
        $response->assertRedirectToRoute('admin.sector.index');
        $this->assertModelMissing($sector);
    } elseif (in_array($action, ['store', 'update'], true)) {
        $response->assertRedirectToRoute('admin.sector.index');
        $this->assertDatabaseHas('sectors', ['title' => 'Updated', 'is_active' => false]);
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
