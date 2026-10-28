<?php

use App\Models\Admin\Admin;
use App\Models\Media\MediaFile;
use App\Services\Fields\FeatureValues;
use App\Services\Fields\FieldFiles;
use App\Services\Fields\ModuleFeatures;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Slider\Models\Slider;
use Nwidart\Modules\Contracts\ActivatorInterface;
use Nwidart\Modules\Facades\Module;
use Nwidart\Modules\Laravel\LaravelFileRepository;

uses(LazilyRefreshDatabase::class);

/** @param array<string, array<string, mixed>|string|null> $documents */
function registerFeatureTestModules(array $documents): void
{
    $disk = Storage::fake('feature-test-modules');
    foreach ($documents as $name => $document) {
        $disk->put($name.'/module.json', json_encode(['name' => $name, 'providers' => []], JSON_THROW_ON_ERROR));
        if ($document !== null) {
            $disk->put($name.'/features.json', is_string($document) ? $document : json_encode($document, JSON_THROW_ON_ERROR));
        }
    }
    $disk->put('statuses.json', json_encode(array_fill_keys(array_keys($documents), true), JSON_THROW_ON_ERROR));
    config(['modules.activators.file.statuses-file' => $disk->path('statuses.json')]);
    app()->forgetInstance(ActivatorInterface::class);
    Module::swap(new LaravelFileRepository(app(), $disk->path('')));
}

/** @param list<array<string, mixed>> $fields @return array<string, mixed> */
function featureTestDocument(array $fields): array
{
    return ['schema_version' => 1, 'forms' => ['slider' => ['label' => 'Özel alanlar', 'fields' => $fields]]];
}

test('feature definitions are isolated by module and form and missing definitions are optional', function () {
    $text = ['key' => 'caption', 'label' => 'Hizmet etiketi', 'input_type' => 'text', 'value_type' => 'string'];
    registerFeatureTestModules([
        'Services' => ['schema_version' => 1, 'forms' => ['service' => ['fields' => [$text]], 'category' => ['fields' => []]]],
        'Team' => ['schema_version' => 1, 'forms' => ['member' => ['fields' => [[...$text, 'label' => 'Ekip etiketi']]]]],
        'Slider' => null,
    ]);
    $features = app(ModuleFeatures::class);
    expect($features->form('Services', 'service')['fields']['caption']->label)->toBe('Hizmet etiketi');
    expect($features->form('Team', 'member')['fields']['caption']->label)->toBe('Ekip etiketi');
    expect($features->form('Services', 'category')['fields'])->toBe([]);
    expect($features->form('Team', 'missing')['fields'])->toBe([]);
    expect($features->form('Slider', 'slider')['fields'])->toBe([]);
});

test('invalid feature documents fail explicitly instead of silently removing inputs', function (mixed $document) {
    registerFeatureTestModules(['Slider' => $document]);
    expect(fn () => app(ModuleFeatures::class)->form('Slider', 'slider'))->toThrow(RuntimeException::class);
})->with([
    'malformed json' => ['{broken'],
    'unsupported version' => [['schema_version' => 2, 'forms' => []]],
    'unknown top level key' => [['schema_version' => 1, 'forms' => [], 'typo' => true]],
    'duplicate keys' => [featureTestDocument([
        ['key' => 'caption', 'label' => 'First', 'input_type' => 'text', 'value_type' => 'string'],
        ['key' => 'caption', 'label' => 'Second', 'input_type' => 'text', 'value_type' => 'string'],
    ])],
    'invalid nested key' => [featureTestDocument([['key' => 'caption.title', 'label' => 'Caption', 'input_type' => 'text', 'value_type' => 'string']])],
    'incompatible types' => [featureTestDocument([['key' => 'caption', 'label' => 'Caption', 'input_type' => 'text', 'value_type' => 'integer']])],
    'invalid default' => [featureTestDocument([['key' => 'count', 'label' => 'Count', 'input_type' => 'number', 'value_type' => 'integer', 'default_value' => 'abc']])],
    'invalid bounds' => [featureTestDocument([['key' => 'caption', 'label' => 'Caption', 'input_type' => 'text', 'value_type' => 'string', 'validation' => ['min' => 10, 'max' => 5]]])],
    'zero step' => [featureTestDocument([['key' => 'count', 'label' => 'Count', 'input_type' => 'number', 'value_type' => 'integer', 'attributes' => ['step' => 0]]])],
    'select without options' => [featureTestDocument([['key' => 'color', 'label' => 'Color', 'input_type' => 'select', 'value_type' => 'string']])],
    'unsafe image extension' => [featureTestDocument([['key' => 'photo', 'label' => 'Photo', 'input_type' => 'image', 'value_type' => 'file', 'validation' => ['extensions' => ['zip']]]])],
    'password in public content' => [featureTestDocument([['key' => 'secret', 'label' => 'Secret', 'input_type' => 'password', 'value_type' => 'string']])],
]);

test('slider uses typed feature defaults and preserves false zero null and retired values', function () {
    $admin = Admin::factory()->superAdmin()->create();
    registerFeatureTestModules(['Slider' => featureTestDocument([
        ['key' => 'enabled', 'label' => 'Aktif', 'input_type' => 'boolean', 'value_type' => 'boolean', 'default_value' => true],
        ['key' => 'count', 'label' => 'Sayı', 'input_type' => 'number', 'value_type' => 'integer', 'default_value' => 7],
        ['key' => 'caption', 'label' => 'Etiket', 'input_type' => 'text', 'value_type' => 'string', 'default_value' => 'Varsayılan'],
    ])]);
    $slider = Slider::factory()->create(['extra_fields' => ['enabled' => false, 'count' => 0, 'caption' => null, 'retired' => 'Korunacak']]);
    $features = app(ModuleFeatures::class)->form('Slider', 'slider')['fields'];
    expect(app(FeatureValues::class)->fields($features, [])['count']['value'])->toBe(7);
    expect(app(FeatureValues::class)->fields($features, $slider->extra_fields)['caption']['value'])->toBeNull();
    $this->actingAs($admin, 'admin')->put(route('admin.slider.update', $slider), ['title' => 'Updated', 'extra_fields' => ['enabled' => '0', 'count' => '0', 'caption' => null]])
        ->assertSessionHasNoErrors()->assertRedirectToRoute('admin.slider.index');
    expect($slider->fresh()->extra_fields)->toBe(['enabled' => false, 'count' => 0, 'caption' => null, 'retired' => 'Korunacak']);
});

test('required feature inputs are validated even when the extra fields payload is absent', function () {
    Storage::fake('public');
    $admin = Admin::factory()->superAdmin()->create();
    registerFeatureTestModules(['Slider' => featureTestDocument([
        ['key' => 'caption', 'label' => 'Etiket', 'input_type' => 'text', 'value_type' => 'string', 'validation' => ['required' => true]],
    ])]);
    $this->actingAs($admin, 'admin')->post(route('admin.slider.store'), ['title' => 'Valid', 'image' => UploadedFile::fake()->image('valid.png')])
        ->assertSessionHasErrors(['extra_fields.caption' => 'Etiket alanını doldurun.']);
    $this->assertDatabaseCount('sliders', 0);
    Storage::disk('public')->assertEmpty();
});

test('feature images are uploaded kept replaced and removed through the same slider form', function () {
    Storage::fake('public');
    $admin = Admin::factory()->superAdmin()->create();
    registerFeatureTestModules(['Slider' => featureTestDocument([
        ['key' => 'mobile_image', 'label' => 'Mobil görsel', 'input_type' => 'image', 'value_type' => 'file', 'validation' => ['extensions' => ['png'], 'max_size' => 1000]],
    ])]);
    $imagePath = UploadedFile::fake()->image('main.png')->store('_media', 'public');
    $image = MediaFile::factory()->create(['path' => $imagePath, 'kind' => 'image', 'mime_type' => 'image/png', 'extension' => 'png', 'size' => Storage::disk('public')->size($imagePath)]);
    $this->actingAs($admin, 'admin')->post(route('admin.slider.store'), ['title' => 'Valid', 'image_media_id' => $image->id, 'extra_fields' => ['mobile_image' => UploadedFile::fake()->image('mobile.png')]])
        ->assertSessionHasNoErrors();
    $slider = Slider::query()->sole();
    $first = $slider->extra_fields['mobile_image'];
    Storage::disk('public')->assertExists([$first, $slider->image]);
    $this->get(route('admin.slider.edit', $slider))->assertOk()->assertSee('name="extra_fields[mobile_image]"', false)->assertSee('name="remove_features[mobile_image]"', false);
    $url = route('admin.slider.update', $slider);
    $this->put($url, ['title' => 'Keep', 'image_media_id' => $image->id])->assertSessionHasNoErrors();
    expect($slider->fresh()->extra_fields['mobile_image'])->toBe($first);
    $this->put($url, ['title' => 'Replace', 'image_media_id' => $image->id, 'extra_fields' => ['mobile_image' => UploadedFile::fake()->image('replacement.png')]])->assertSessionHasNoErrors();
    $second = $slider->fresh()->extra_fields['mobile_image'];
    Storage::disk('public')->assertMissing($first);
    Storage::disk('public')->assertExists($second);
    $this->put($url, ['title' => 'Remove', 'image_media_id' => $image->id, 'remove_features' => ['mobile_image' => '1']])->assertSessionHasNoErrors();
    expect($slider->fresh()->extra_fields['mobile_image'])->toBeNull();
    Storage::disk('public')->assertMissing($second);
    Storage::disk('public')->assertExists($slider->image);
});

test('a required stored feature image cannot be removed without replacement', function () {
    Storage::fake('public');
    Storage::disk('public')->put('existing.png', 'existing');
    $slider = Slider::factory()->create(['extra_fields' => ['photo' => 'existing.png']]);
    $admin = Admin::factory()->superAdmin()->create();
    registerFeatureTestModules(['Slider' => featureTestDocument([
        ['key' => 'photo', 'label' => 'Fotoğraf', 'input_type' => 'image', 'value_type' => 'file', 'validation' => ['required' => true]],
    ])]);
    $this->actingAs($admin, 'admin')->put(route('admin.slider.update', $slider), ['title' => 'Changed', 'remove_features' => ['photo' => '1']])
        ->assertSessionHasErrors(['extra_fields.photo' => 'Fotoğraf alanını doldurun.']);
    expect($slider->fresh()->extra_fields['photo'])->toBe('existing.png');
    Storage::disk('public')->assertExists('existing.png');
});

test('failed persistence rolls back records and new uploads while keeping old files', function () {
    Storage::fake('public');
    Storage::disk('public')->put('old.png', 'original');
    expect(fn () => app(FieldFiles::class)->transaction(function (FieldFiles $files): void {
        $path = $files->replace(UploadedFile::fake()->image('new.png'), 'old.png', 'features');
        Slider::factory()->create(['image' => $path]);
        throw new RuntimeException('Persistence failed');
    }))->toThrow(RuntimeException::class, 'Persistence failed');
    $this->assertDatabaseCount('sliders', 0);
    Storage::disk('public')->assertExists('old.png');
    Storage::disk('public')->assertDirectoryEmpty('features');
});
