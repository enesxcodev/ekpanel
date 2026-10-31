<?php

use App\Models\Admin\Admin;
use App\Models\Media\MediaFile;
use App\Models\Settings\Language;
use App\Services\Admin\Modules\ModulePanelService;
use Database\Seeders\MediaPermissionSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Brand\Models\Brand;
use Nwidart\Modules\Contracts\ActivatorInterface;
use Nwidart\Modules\Facades\Module;
use Nwidart\Modules\Laravel\LaravelFileRepository;

uses(LazilyRefreshDatabase::class);

function brandTestImage(string $name, int $size = 20): MediaFile
{
    $upload = UploadedFile::fake()->image($name)->size($size);
    $path = $upload->store('_media', 'public');
    if ($size > 4096) {
        $contents = Storage::disk('public')->get($path);
        Storage::disk('public')->put($path, $contents.str_repeat("\0", max(0, 4097 * 1024 - strlen($contents))));
    }

    return MediaFile::factory()->create(['path' => $path, 'name' => $name, 'kind' => 'image', 'mime_type' => 'image/png', 'extension' => 'png', 'size' => Storage::disk('public')->size($path)]);
}

function brandTestDocument(): MediaFile
{
    $upload = UploadedFile::fake()->create('brand.pdf', 10, 'application/pdf');
    $path = $upload->store('_media', 'public');

    return MediaFile::factory()->create(['path' => $path, 'name' => 'brand.pdf', 'kind' => 'document', 'mime_type' => 'application/pdf', 'extension' => 'pdf', 'size' => Storage::disk('public')->size($path)]);
}

test('brands lists records and links to its separate create form', function () {
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin');

    $this->get(route('admin.brand.index'))->assertOk()
        ->assertSee('id="brands-table"', false)
        ->assertSee('href="'.route('admin.brand.create').'"', false)
        ->assertDontSee('action="'.route('admin.brand.store').'"', false);

    $this->get(route('admin.brand.create'))->assertOk()
        ->assertSee('action="'.route('admin.brand.store').'"', false)
        ->assertSee('enctype="multipart/form-data"', false)
        ->assertSeeInOrder(['name="lang_id"', 'name="image_media_id"', 'name="link"', 'name="name"', 'name="short_description"'], false)
        ->assertSee('Marka ekle')
        ->assertSee('href="'.route('admin.brand.index').'"', false);
});

test('brands persists required fields and accepts missing optional content', function () {
    Storage::fake('public');
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin');

    $image = brandTestImage('logo.png');
    $language = Language::query()->where('code', 'tr')->sole();
    $this->post(route('admin.brand.store'), ['lang_id' => $language->id, 'name' => 'Acme', 'image_media_id' => $image->id])
        ->assertSessionHasNoErrors()->assertRedirect(route('admin.brand.edit', ['brand' => 1, 'lang' => 'tr']));

    $brand = Brand::query()->sole();
    expect($brand->translations()->where('lang_id', $language->id)->value('name'))->toBe('Acme');
    expect($brand->translations()->where('lang_id', $language->id)->value('short_description'))->toBeNull();
    expect($brand->link)->toBeNull();
    Storage::disk('public')->assertExists($brand->image);
});

test('brands preserves its required image on update and cleans replaced and deleted files', function () {
    Storage::fake('public');
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin');
    $first = brandTestImage('first.png');
    $language = Language::query()->where('code', 'tr')->sole();
    $brand = Brand::factory()->create();
    $this->put(route('admin.brand.update', $brand), ['lang_id' => $language->id, 'name' => 'Original', 'image_media_id' => $first->id])->assertSessionHasNoErrors();
    $brand->refresh();
    $firstImage = $brand->image;

    $this->put(route('admin.brand.update', $brand), ['lang_id' => $language->id, 'name' => 'Updated', 'image_media_id' => $first->id, 'short_description' => 'Partner brand', 'link' => 'https://example.com'])
        ->assertSessionHasNoErrors()->assertRedirect(route('admin.brand.edit', ['brand' => $brand->id, 'lang' => 'tr']));
    expect($brand->fresh()->image)->toBe($firstImage);
    $this->assertDatabaseHas('brand_translations', ['brand_id' => $brand->id, 'lang_id' => $language->id, 'name' => 'Updated', 'short_description' => 'Partner brand']);
    expect($brand->fresh()->link)->toBe('https://example.com');
    Storage::disk('public')->assertExists($firstImage);

    $second = brandTestImage('second.png');
    $this->put(route('admin.brand.update', $brand), ['lang_id' => $language->id, 'name' => 'Updated', 'image_media_id' => $second->id])
        ->assertSessionHasNoErrors();
    $secondImage = $brand->fresh()->image;
    Storage::disk('public')->assertExists($secondImage);
    Storage::disk('public')->assertExists($firstImage);
    $this->assertDatabaseMissing('media_usages', ['owner_type' => Brand::class, 'owner_id' => $brand->id, 'media_id' => $first->id]);
    expect($brand->fresh()->translations()->where('lang_id', $language->id)->value('short_description'))->toBeNull();
    expect($brand->fresh()->link)->toBeNull();

    $this->delete(route('admin.brand.destroy', $brand))->assertRedirectToRoute('admin.brand.index');
    $this->assertModelMissing($brand);
    Storage::disk('public')->assertExists($secondImage);
    $this->assertDatabaseMissing('media_usages', ['owner_type' => Brand::class, 'owner_id' => $brand->id]);
});

test('brands rejects missing required fields', function () {
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin')
        ->post(route('admin.brand.store'), [])->assertSessionHasErrors(['lang_id', 'name', 'image_media_id']);

    $this->assertDatabaseCount('brands', 0);
});

test('brands rejects invalid content without persisting files or rows', function (string $field, mixed $value) {
    Storage::fake('public');
    $payload = ['lang_id' => Language::query()->where('code', 'tr')->sole()->id, 'name' => 'Acme', 'image_media_id' => brandTestImage('valid.png')->id];
    $payload[$field] = $value;

    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin')
        ->post(route('admin.brand.store'), $payload)->assertSessionHasErrors($field);

    $this->assertDatabaseCount('brands', 0);
    Storage::disk('public')->assertCount('_media', 1);
})->with([
    'name is not text' => ['name', ['invalid']],
    'name is too long' => ['name', str_repeat('a', 256)],
    'description is too long' => ['short_description', str_repeat('a', 501)],
    'description is not text' => ['short_description', ['invalid']],
    'unsafe link scheme' => ['link', 'javascript:alert(1)'],
    'invalid link' => ['link', 'not-a-url'],
    'oversized link' => ['link', 'https://example.com/'.str_repeat('a', 2048)],
    'path instead of media selection' => ['image_media_id', 'brand/images/existing.png'],
    'unknown extra fields' => ['extra_fields', ['unknown' => 'injected']],
]);

test('brands rejects invalid image content and oversized uploads', function (bool $oversized) {
    Storage::fake('public');
    $image = $oversized ? brandTestImage('large.png', 4097) : brandTestDocument();

    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin')
        ->post(route('admin.brand.store'), ['lang_id' => Language::query()->where('code', 'tr')->sole()->id, 'name' => 'Acme', 'image_media_id' => $image->id])->assertSessionHasErrors('image_media_id');

    $this->assertDatabaseCount('brands', 0);
    Storage::disk('public')->assertCount('_media', 1);
})->with([true, false]);

test('brands rejects an invalid update and retains the existing content and image', function () {
    Storage::fake('public');
    Storage::disk('public')->put('brand/images/original.png', 'original');
    $language = Language::query()->where('code', 'tr')->sole();
    $brand = Brand::factory()->create(['image' => 'brand/images/original.png']);
    $brand->translations()->where('lang_id', $language->id)->update(['name' => 'Original']);

    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin')
        ->put(route('admin.brand.update', $brand), ['lang_id' => $language->id, 'name' => ''])
        ->assertSessionHasErrors('name');

    expect($brand->fresh()->translations()->where('lang_id', $language->id)->value('name'))->toBe('Original');
    Storage::disk('public')->assertExists('brand/images/original.png');
    Storage::disk('public')->assertCount('brand/images', 1);
});

test('brands escapes editable content and only offers permitted actions', function () {
    app(ModulePanelService::class)->syncPermissions();
    $admin = Admin::factory()->create();
    $admin->givePermissionTo(['brand.view', 'brand.update']);
    $language = Language::query()->where('code', 'tr')->sole();
    $brand = Brand::factory()->create();
    $brand->translations()->where('lang_id', $language->id)->update(['name' => '<script>name</script>', 'short_description' => '<script>description</script>']);
    $this->actingAs($admin, 'admin');

    $this->get(route('admin.brand.index'))->assertOk()->assertDontSee('action="'.route('admin.brand.store').'"', false);
    $this->get(route('admin.brand.edit', $brand))->assertOk()
        ->assertSee('&lt;script&gt;name&lt;/script&gt;', false)
        ->assertSee('&lt;script&gt;description&lt;/script&gt;', false)
        ->assertDontSee('<script>name</script>', false);
    $this->getJson(route('admin.brand.data'))->assertOk()
        ->assertJsonPath('recordsTotal', 1)
        ->assertJsonPath('data.0.name', '&lt;script&gt;name&lt;/script&gt;')
        ->assertJsonPath('data.0.short_description', '&lt;script&gt;description&lt;/script&gt;')
        ->assertJsonPath('data.0.actions', fn (string $actions): bool => str_contains($actions, 'Düzenle') && ! str_contains($actions, '>Sil<'));
});

test('brands enforces operation permissions', function (string $method, string $action, string $permission, bool $granted) {
    Storage::fake('public');
    app(ModulePanelService::class)->syncPermissions();
    $this->seed(MediaPermissionSeeder::class);
    $admin = Admin::factory()->create();
    if ($granted) {
        $admin->givePermissionTo($permission);
        if (in_array($action, ['store', 'update'], true)) {
            $admin->givePermissionTo('media.view');
        }
    }
    $language = Language::query()->where('code', 'tr')->sole();
    $brand = Brand::factory()->create();
    $brand->translations()->where('lang_id', $language->id)->update(['name' => 'Original']);
    $parameters = in_array($action, ['edit', 'update', 'destroy'], true) ? $brand : [];

    $payload = ['lang_id' => $language->id, 'name' => 'Updated'];
    if ($granted && in_array($action, ['store', 'update'], true)) {
        $payload['image_media_id'] = brandTestImage('logo.png')->id;
    }
    $response = $this->actingAs($admin, 'admin')->{$method}(route('admin.brand.'.$action, $parameters), $payload);

    if (! $granted) {
        $response->assertForbidden();
        expect($brand->fresh()->translations()->where('lang_id', $language->id)->value('name'))->toBe('Original');
        $this->assertDatabaseCount('brands', 1);
        Storage::disk('public')->assertEmpty();
    } elseif ($action === 'destroy') {
        $response->assertRedirectToRoute('admin.brand.index');
        $this->assertModelMissing($brand);
    } elseif (in_array($action, ['store', 'update'], true)) {
        $savedBrand = Brand::query()->latest('id')->firstOrFail();
        $response->assertRedirect(route('admin.brand.edit', ['brand' => $savedBrand->id, 'lang' => 'tr']));
        $this->assertDatabaseHas('brand_translations', ['brand_id' => $savedBrand->id, 'lang_id' => $language->id, 'name' => 'Updated']);
        $this->assertDatabaseHas('media_usages', ['owner_type' => Brand::class, 'owner_id' => $savedBrand->id]);
    } else {
        $response->assertOk();
    }
})->with([
    ['get', 'index', 'brand.view'],
    ['get', 'data', 'brand.view'],
    ['get', 'create', 'brand.create'],
    ['post', 'store', 'brand.create'],
    ['get', 'edit', 'brand.update'],
    ['put', 'update', 'brand.update'],
    ['delete', 'destroy', 'brand.delete'],
])->with([true, false]);

test('brands separates sidebar page and create permissions', function (array $permissions, bool $visible, bool $canCreate) {
    app(ModulePanelService::class)->syncPermissions();
    $admin = Admin::factory()->create();
    $admin->givePermissionTo($permissions);
    $this->actingAs($admin, 'admin');

    $dashboard = $this->get(route('admin.dashboard'))->assertOk();
    if ($visible) {
        $dashboard->assertSee('href="'.route('admin.brand.index').'"', false);
    } else {
        $dashboard->assertDontSee('href="'.route('admin.brand.index').'"', false);
    }
    $page = $this->get(route('admin.brand.index'));
    if (in_array('brand.view', $permissions, true)) {
        $page->assertOk();
        if ($canCreate) {
            $page->assertSee('href="'.route('admin.brand.create').'"', false);
        } else {
            $page->assertDontSee('href="'.route('admin.brand.create').'"', false);
        }
    } else {
        $page->assertForbidden();
    }
})->with([
    [['brand.menu'], false, false],
    [['brand.view'], false, false],
    [['brand.menu', 'brand.view'], true, false],
    [['brand.menu', 'brand.view', 'brand.create'], true, true],
]);

test('brands blocks guests and inactive accounts', function () {
    $this->get(route('admin.brand.index'))->assertRedirectToRoute('admin.login');
    $this->actingAs(Admin::factory()->superAdmin()->inactive()->create(), 'admin')
        ->get(route('admin.brand.index'))->assertRedirectToRoute('admin.login');
});

test('disabled brands rejects every endpoint even for super admins', function (string $method, string $action) {
    $disk = Storage::fake('disabled-brand');
    $disk->put('statuses.json', json_encode(['Brand' => false], JSON_THROW_ON_ERROR));
    config(['modules.activators.file.statuses-file' => $disk->path('statuses.json')]);
    app()->forgetInstance(ActivatorInterface::class);
    $brand = Brand::factory()->create();
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin');
    $parameters = in_array($action, ['edit', 'update', 'destroy'], true) ? $brand : [];

    $this->get(route('admin.dashboard'))->assertOk()->assertDontSee('href="'.route('admin.brand.index').'"', false);
    $this->{$method}(route('admin.brand.'.$action, $parameters), ['lang_id' => Language::query()->where('code', 'tr')->sole()->id, 'name' => 'Forbidden'])->assertNotFound();

    $this->assertModelExists($brand);
    $this->assertDatabaseCount('brands', 1);
})->with([
    ['get', 'index'], ['get', 'data'], ['get', 'create'], ['post', 'store'], ['get', 'edit'], ['put', 'update'], ['delete', 'destroy'],
]);

test('brands renders validates and persists configured extra fields', function () {
    Storage::fake('public');
    $disk = Storage::fake('brand-feature-definitions');
    $disk->put('Brand/module.json', json_encode(['name' => 'Brand', 'providers' => []], JSON_THROW_ON_ERROR));
    $disk->put('Brand/features.json', json_encode(['schema_version' => 1, 'forms' => ['brand' => ['label' => 'Ek marka alanları', 'fields' => [
        ['key' => 'tagline', 'label' => 'Slogan', 'input_type' => 'text', 'value_type' => 'string', 'validation' => ['max' => 20]],
    ]]]], JSON_THROW_ON_ERROR));
    $disk->put('statuses.json', json_encode(['Brand' => true], JSON_THROW_ON_ERROR));
    config(['modules.activators.file.statuses-file' => $disk->path('statuses.json')]);
    app()->forgetInstance(ActivatorInterface::class);
    Module::swap(new LaravelFileRepository(app(), $disk->path('')));
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin');

    $this->get(route('admin.brand.create'))->assertOk()->assertSee('name="extra_fields[tagline]"', false);
    $image = brandTestImage('logo.png');
    $language = Language::query()->where('code', 'tr')->sole();
    $brand = Brand::factory()->create();
    $this->put(route('admin.brand.update', $brand), ['lang_id' => $language->id, 'name' => 'Acme', 'image_media_id' => $image->id, 'extra_fields' => ['tagline' => '<b>Brand</b>']])
        ->assertSessionHasNoErrors();
    $brand->refresh();
    expect($brand->translations()->where('lang_id', $language->id)->value('extra_fields'))->toBe(['tagline' => '<b>Brand</b>']);
    $this->get(route('admin.brand.edit', $brand))->assertOk()->assertSee('&lt;b&gt;Brand&lt;/b&gt;', false);
    $brandImage = $brand->fresh()->image;
    $this->put(route('admin.brand.update', $brand), ['lang_id' => $language->id, 'name' => 'Acme', 'extra_fields' => ['tagline' => str_repeat('a', 21)]])
        ->assertSessionHasErrors('extra_fields.tagline');
    expect($brand->fresh()->translations()->where('lang_id', $language->id)->value('extra_fields'))->toBe(['tagline' => '<b>Brand</b>']);
    expect($brand->fresh()->image)->toBe($brandImage);
});
