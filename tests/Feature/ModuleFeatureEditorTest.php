<?php

use App\Models\Admin\Admin;
use App\Services\Fields\ModuleFeatures;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Modules\Slider\Models\Slider;
use Spatie\Permission\Models\Permission;

require_once __DIR__.'/../Fixtures/module-distribution.php';

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
});

/** @return array<string, mixed> */
function editableModuleDocument(): array
{
    return ['schema_version' => 1, 'forms' => [
        'slider' => ['label' => 'Slider alanları', 'fields' => [
            ['key' => 'caption', 'label' => 'Buton yazısı', 'input_type' => 'text', 'value_type' => 'string', 'default_value' => 'İncele', 'validation' => ['max' => 80], 'attributes' => ['placeholder' => 'Buton metni']],
            ['key' => 'photo', 'label' => 'Ek görsel', 'input_type' => 'image', 'value_type' => 'media', 'validation' => ['max_size' => 4096, 'extensions' => ['jpg', 'png'], 'min_width' => 100], 'default_value' => null],
            ['key' => 'active', 'label' => 'Göster', 'input_type' => 'boolean', 'value_type' => 'boolean', 'default_value' => false],
            ['key' => 'count', 'label' => 'Sayı', 'input_type' => 'range', 'value_type' => 'integer', 'default_value' => 0, 'validation' => ['min' => 0, 'max' => 10], 'attributes' => ['step' => 1]],
            ['key' => 'style', 'label' => 'Görünüm', 'input_type' => 'radio', 'value_type' => 'string', 'options' => [['value' => 'light', 'label' => 'Açık']], 'attributes' => ['layout' => 'horizontal', 'variant' => 'buttons']],
        ]],
        'category' => ['label' => 'Kategoriler', 'fields' => []],
    ]];
}

test('installed cards link to a local visual editor for enabled and disabled modules', function (bool $enabled) {
    prepareDistribution();
    putDistributionModule('Slider', $enabled, ['description' => '<script>unsafe</script>']);
    $disk = Storage::disk('module-distribution');
    $disk->put('Modules/Slider/features.json', json_encode(editableModuleDocument()));
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin');

    $this->get(route('admin.modules.index'))->assertOk()->assertSee(route('admin.modules.features.show', 'Slider'), false);
    $this->get(route('admin.modules.features.show', 'Slider'))->assertOk()
        ->assertSee('data-builder-mode="features"', false)->assertSee('data-feature-form', false)
        ->assertSee('data-field-list', false)->assertSee('data-form-preview', false)
        ->assertSee('Buton yazısı')->assertDontSee('<script>unsafe</script>', false);
})->with([true, false]);

test('saving an edited document preserves all form groups types and nested settings', function () {
    prepareDistribution();
    putDistributionModule('Slider');
    $disk = Storage::disk('module-distribution');
    $original = editableModuleDocument();
    $disk->put('Modules/Slider/features.json', json_encode($original));
    $features = app(ModuleFeatures::class);
    app()->instance(ModuleFeatures::class, $features);
    expect($features->form('Slider', 'slider')['fields']['caption']->label)->toBe('Buton yazısı');
    $edited = $original;
    $edited['forms']['slider']['fields'][0]['label'] = 'Yeni etiket';

    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin')
        ->put(route('admin.modules.features.update', 'Slider'), ['document' => json_encode($edited), 'revision' => $features->revision('Slider')])
        ->assertSessionHasNoErrors()->assertRedirectToRoute('admin.modules.features.show', 'Slider');

    expect(json_decode($disk->get('Modules/Slider/features.json'), true))->toBe($edited);
    expect($features->form('Slider', 'slider')['fields']['caption']->label)->toBe('Yeni etiket');
});

test('new definitions appear on module create and edit forms and are validated and saved', function () {
    $slider = Slider::factory()->create(['extra_fields' => ['retired' => 'Korunacak']]);
    prepareDistribution();
    putDistributionModule('Slider');
    $features = app(ModuleFeatures::class);
    $document = ['schema_version' => 1, 'forms' => ['slider' => ['label' => 'Ek alanlar', 'fields' => [
        ['key' => 'caption', 'label' => 'Yeni başlık', 'input_type' => 'text', 'value_type' => 'string', 'validation' => ['required' => true, 'max' => 10]],
    ]]]];
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin');

    $this->put(route('admin.modules.features.update', 'Slider'), ['document' => $document, 'revision' => $features->revision('Slider')])->assertSessionHasNoErrors();
    $this->get(route('admin.slider.create'))->assertOk()->assertSee('name="extra_fields[caption]"', false);
    $this->get(route('admin.slider.edit', $slider))->assertOk()->assertSee('Yeni başlık');
    $this->put(route('admin.slider.update', $slider), ['title' => 'Slider'])->assertSessionHasErrors('extra_fields.caption');
    $this->put(route('admin.slider.update', $slider), ['title' => 'Slider', 'extra_fields' => ['caption' => 'Yeni']])->assertSessionHasNoErrors();

    expect($slider->fresh()->extra_fields)->toBe(['retired' => 'Korunacak', 'caption' => 'Yeni']);
});

test('empty definitions are written as an object and disabled module definitions remain editable', function () {
    prepareDistribution();
    putDistributionModule('Sample', false);
    $features = app(ModuleFeatures::class);

    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin')
        ->put(route('admin.modules.features.update', 'Sample'), ['document' => ['schema_version' => 1, 'forms' => []], 'revision' => $features->revision('Sample')])
        ->assertSessionHasNoErrors();

    $json = json_decode(Storage::disk('module-distribution')->get('Modules/Sample/features.json'));
    expect($json->forms)->toBeInstanceOf(stdClass::class);
});

test('invalid definitions never overwrite the existing file', function (mixed $document) {
    prepareDistribution();
    putDistributionModule();
    $disk = Storage::disk('module-distribution');
    $original = json_encode(editableModuleDocument());
    $disk->put('Modules/Sample/features.json', $original);

    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin')
        ->from(route('admin.modules.features.show', 'Sample'))
        ->put(route('admin.modules.features.update', 'Sample'), ['document' => $document, 'revision' => app(ModuleFeatures::class)->revision('Sample')])
        ->assertSessionHasErrors('document');

    expect($disk->get('Modules/Sample/features.json'))->toBe($original);
})->with([
    'malformed JSON' => ['{broken'],
    'unsupported version' => [['schema_version' => 2, 'forms' => []]],
    'unknown property' => [['schema_version' => 1, 'forms' => [], 'unexpected' => true]],
    'duplicate fields' => [array_replace_recursive(editableModuleDocument(), ['forms' => ['slider' => ['fields' => [1 => ['key' => 'caption']]]]])],
    'incompatible value type' => [array_replace_recursive(editableModuleDocument(), ['forms' => ['slider' => ['fields' => [0 => ['value_type' => 'integer']]]]])],
    'invalid default' => [array_replace_recursive(editableModuleDocument(), ['forms' => ['slider' => ['fields' => [3 => ['default_value' => 'abc']]]]])],
    'invalid form key' => [['schema_version' => 1, 'forms' => ['../outside' => ['fields' => []]]]],
]);

test('stale editors cannot overwrite more recent file changes', function () {
    prepareDistribution();
    putDistributionModule();
    $disk = Storage::disk('module-distribution');
    $features = app(ModuleFeatures::class);
    $revision = $features->revision('Sample');
    $original = json_encode(editableModuleDocument());
    $disk->put('Modules/Sample/features.json', $original);

    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin')
        ->put(route('admin.modules.features.update', 'Sample'), ['document' => ['schema_version' => 1, 'forms' => []], 'revision' => $revision])
        ->assertSessionHasErrors('revision');

    expect($disk->get('Modules/Sample/features.json'))->toBe($original);
});

test('a malformed source file is reported without offering an empty replacement editor', function () {
    prepareDistribution();
    putDistributionModule();
    Storage::disk('module-distribution')->put('Modules/Sample/features.json', '{broken');

    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin')
        ->get(route('admin.modules.features.show', 'Sample'))->assertOk()
        ->assertSee('Alan tanımları okunamadı')->assertDontSee('data-form-builder', false);
});

test('module definition endpoints reject customer admins even with matching permissions', function (string $method, string $route) {
    prepareDistribution();
    putDistributionModule();
    $admin = Admin::factory()->create();
    $admin->givePermissionTo(Permission::findOrCreate('manage-modules', 'admin'));

    $this->actingAs($admin, 'admin')->{$method}(route($route, 'Sample'))->assertForbidden();
})->with([['get', 'admin.modules.features.show'], ['put', 'admin.modules.features.update']]);

test('module definition endpoints require authentication', function (string $method, string $route) {
    $this->{$method}(route($route, 'Sample'))->assertRedirectToRoute('admin.login');
})->with([['get', 'admin.modules.features.show'], ['put', 'admin.modules.features.update']]);

test('unknown installed modules return 404 without writing files', function () {
    prepareDistribution();
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin');

    $this->get(route('admin.modules.features.show', 'Missing'))->assertNotFound();
    $this->put(route('admin.modules.features.update', 'Missing'), ['document' => ['schema_version' => 1, 'forms' => []], 'revision' => str_repeat('0', 64)])->assertNotFound();
});

test('file write failures show a validation error and keep the previous definitions', function () {
    prepareDistribution();
    putDistributionModule();
    $disk = Storage::disk('module-distribution');
    $original = json_encode(editableModuleDocument());
    $disk->put('Modules/Sample/features.json', $original);
    $revision = app(ModuleFeatures::class)->revision('Sample');
    Exceptions::fake();
    File::partialMock()->shouldReceive('replace')->once()->andThrow(new RuntimeException('Write failed'));

    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin')
        ->put(route('admin.modules.features.update', 'Sample'), ['document' => ['schema_version' => 1, 'forms' => []], 'revision' => $revision])
        ->assertSessionHasErrors('document');

    expect($disk->get('Modules/Sample/features.json'))->toBe($original);
    Exceptions::assertReported(RuntimeException::class);
});
