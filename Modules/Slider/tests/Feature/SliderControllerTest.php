<?php

use App\Models\Admin\Admin;
use App\Models\Media\MediaFile;
use App\Services\Admin\Modules\ModulePanelService;
use Database\Seeders\MediaPermissionSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Slider\Models\Slider;
use Nwidart\Modules\Contracts\ActivatorInterface;

uses(LazilyRefreshDatabase::class);

function sliderTestImage(string $name, bool $oversized = false): MediaFile
{
    $upload = UploadedFile::fake()->image($name);
    $path = $upload->store('_media', 'public');
    if ($oversized) {
        $contents = Storage::disk('public')->get($path);
        Storage::disk('public')->put($path, $contents.str_repeat("\0", max(0, 4097 * 1024 - strlen($contents))));
    }

    return MediaFile::factory()->create(['path' => $path, 'name' => $name, 'kind' => 'image', 'mime_type' => 'image/png', 'extension' => 'png', 'size' => Storage::disk('public')->size($path)]);
}

function sliderTestDocument(): MediaFile
{
    $upload = UploadedFile::fake()->create('document.pdf', 10, 'application/pdf');
    $path = $upload->store('_media', 'public');

    return MediaFile::factory()->create(['path' => $path, 'name' => 'document.pdf', 'kind' => 'document', 'mime_type' => 'application/pdf', 'extension' => 'pdf', 'size' => Storage::disk('public')->size($path)]);
}

test('slider create and edit forms show the shared extra fields below the core fields', function () {
    $slider = Slider::factory()->create(['extra_fields' => ['button_text' => '<script>Example</script>']]);
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin');
    $this->get(route('admin.slider.create'))->assertOk()
        ->assertSeeInOrder(['name="title"', 'name="subtitle"', 'name="image_media_id"', 'name="extra_fields[button_text]"'], false);
    $this->get(route('admin.slider.edit', $slider))->assertOk()
        ->assertSee('name="extra_fields[button_text]"', false)->assertSee('&lt;script&gt;Example&lt;/script&gt;', false)
        ->assertDontSee('<script>Example</script>', false);
});

test('slider records keep required images during edits and replace and delete their files', function () {
    Storage::fake('public');
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin');
    $firstMedia = sliderTestImage('first.png');
    $this->post(route('admin.slider.store'), [
        'title' => 'İlk slider', 'image_media_id' => $firstMedia->id,
        'extra_fields' => ['button_text' => 'İncele', 'short_description' => 'Kısa metin'],
    ])->assertSessionHasNoErrors()->assertRedirectToRoute('admin.slider.index');
    $slider = Slider::query()->sole();
    expect($slider->subtitle)->toBeNull();
    expect($slider->extra_fields)->toBe(['background_image' => null, 'button_text' => 'İncele', 'short_description' => 'Kısa metin']);
    $first = $slider->image;
    Storage::disk('public')->assertExists($first);

    $this->put(route('admin.slider.update', $slider), ['title' => 'Yeni slider', 'subtitle' => 'Alt başlık', 'image_media_id' => $firstMedia->id, 'extra_fields' => ['button_text' => null]])
        ->assertSessionHasNoErrors()->assertRedirectToRoute('admin.slider.index');
    expect($slider->fresh()->image)->toBe($first);
    expect($slider->fresh()->subtitle)->toBe('Alt başlık');
    expect($slider->fresh()->extra_fields)->toBe(['background_image' => null, 'button_text' => null, 'short_description' => 'Kısa metin']);
    Storage::disk('public')->assertExists($first);

    $replacementMedia = sliderTestImage('replacement.png');
    $this->put(route('admin.slider.update', $slider), ['title' => 'Son slider', 'image_media_id' => $replacementMedia->id])
        ->assertSessionHasNoErrors();
    $replacement = $slider->fresh()->image;
    Storage::disk('public')->assertExists($replacement);
    Storage::disk('public')->assertExists($first);
    $this->delete(route('admin.slider.destroy', $slider))->assertRedirectToRoute('admin.slider.index');
    $this->assertModelMissing($slider);
    Storage::disk('public')->assertExists($replacement);
    $this->assertDatabaseMissing('media_usages', ['owner_type' => Slider::class, 'owner_id' => $slider->id]);
});

test('slider requires its title and image when creating a record', function () {
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin')->post(route('admin.slider.store'), [])
        ->assertSessionHasErrors(['title' => 'Başlık alanını doldurun.', 'image_media_id' => 'Görsel alanını doldurun.']);
    $this->assertDatabaseCount('sliders', 0);
});

test('slider rejects invalid core and extra fields without storing files or records', function (string $field, mixed $value) {
    Storage::fake('public');
    $payload = ['title' => 'Valid', 'image_media_id' => sliderTestImage('valid.png')->id];
    data_set($payload, $field, $value);
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin')->post(route('admin.slider.store'), $payload)
        ->assertSessionHasErrors($field);
    $this->assertDatabaseCount('sliders', 0);
    Storage::disk('public')->assertCount('_media', 1);
})->with([
    'long title' => ['title', str_repeat('a', 256)],
    'invalid subtitle' => ['subtitle', ['not text']],
    'oversized extra text' => ['extra_fields.button_text', str_repeat('a', 81)],
    'unknown feature' => ['extra_fields', ['unknown_key' => 'injected']],
    'invalid removal' => ['remove_features', ['button_text' => '1']],
    'image path instead of media selection' => ['image', 'some/path.png'],
]);

test('slider rejects a selected document instead of an image', function () {
    Storage::fake('public');
    $document = sliderTestDocument();
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin')
        ->post(route('admin.slider.store'), ['title' => 'Valid', 'image_media_id' => $document->id])
        ->assertSessionHasErrors('image_media_id');
    $this->assertDatabaseCount('sliders', 0);
    Storage::disk('public')->assertCount('_media', 1);
});

test('slider endpoints enforce their operation permissions', function (string $method, string $action, string $permission, bool $granted) {
    Storage::fake('public');
    app(ModulePanelService::class)->syncPermissions();
    $this->seed(MediaPermissionSeeder::class);
    $admin = Admin::factory()->create();
    if ($granted) {
        $admin->givePermissionTo($permission);
    }
    $slider = Slider::factory()->create(['title' => 'Original']);
    $parameters = in_array($action, ['edit', 'update', 'destroy'], true) ? $slider : [];
    $payload = ['title' => 'Updated'];
    if ($granted && in_array($action, ['store', 'update'], true)) {
        $admin->givePermissionTo('media.view');
        $payload['image_media_id'] = sliderTestImage('allowed.png')->id;
    }
    $response = $this->actingAs($admin, 'admin')->{$method}(route('admin.slider.'.$action, $parameters), $payload);
    if (! $granted) {
        $response->assertForbidden();
        expect($slider->fresh()->title)->toBe('Original');
        $this->assertDatabaseCount('sliders', 1);
        Storage::disk('public')->assertEmpty();
    } elseif (in_array($action, ['store', 'update', 'destroy'], true)) {
        $response->assertRedirectToRoute('admin.slider.index');
        if ($action === 'destroy') {
            $this->assertModelMissing($slider);
        } else {
            $this->assertDatabaseHas('sliders', ['title' => 'Updated']);
            Storage::disk('public')->assertCount('_media', 1);
        }
    } else {
        $response->assertOk();
        Storage::disk('public')->assertEmpty();
    }
})->with([
    'index' => ['get', 'index', 'slider.view'],
    'data' => ['get', 'data', 'slider.view'],
    'create' => ['get', 'create', 'slider.create'],
    'store' => ['post', 'store', 'slider.create'],
    'edit' => ['get', 'edit', 'slider.update'],
    'update' => ['put', 'update', 'slider.update'],
    'destroy' => ['delete', 'destroy', 'slider.delete'],
])->with([true, false]);

test('slider blocks guests and inactive accounts', function () {
    $this->get(route('admin.slider.index'))->assertRedirectToRoute('admin.login');
    $this->actingAs(Admin::factory()->superAdmin()->inactive()->create(), 'admin')
        ->get(route('admin.slider.index'))->assertRedirectToRoute('admin.login');
});

test('disabled slider module is hidden and inaccessible even for super admins', function () {
    $disk = Storage::fake('disabled-slider');
    $disk->put('statuses.json', json_encode(['Services' => true, 'Slider' => false], JSON_THROW_ON_ERROR));
    config(['modules.activators.file.statuses-file' => $disk->path('statuses.json')]);
    app()->forgetInstance(ActivatorInterface::class);
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin');
    $this->get(route('admin.dashboard'))->assertOk()->assertDontSee('href="'.route('admin.slider.index').'"', false);
    $this->get(route('admin.slider.index'))->assertNotFound();
});

test('slider data table escapes titles and only shows permitted actions', function () {
    app(ModulePanelService::class)->syncPermissions();
    $admin = Admin::factory()->create();
    $admin->givePermissionTo(['slider.view', 'slider.update']);
    Slider::factory()->create(['title' => '<script>alert("xss")</script>']);
    $this->actingAs($admin, 'admin')->getJson(route('admin.slider.data'))
        ->assertOk()->assertJsonPath('recordsTotal', 1)
        ->assertJsonPath('data.0.title', '&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;')
        ->assertJsonPath('data.0.actions', fn (string $actions): bool => str_contains($actions, 'Düzenle') && ! str_contains($actions, '>Sil<'));
});
