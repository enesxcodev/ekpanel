<?php

use App\Models\Admin\Admin;
use App\Models\Settings\SettingDefinition;
use App\Models\Settings\SettingSection;
use App\Services\Settings\SettingsService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(LazilyRefreshDatabase::class);

test('settings renders configured inputs and saves typed values without losing defaults', function () {
    $admin = Admin::factory()->superAdmin()->create();
    $section = SettingSection::factory()->create();
    $text = SettingDefinition::factory()->for($section, 'section')->required()->create(['label' => 'Site başlığı', 'default_value' => 'Varsayılan']);
    $boolean = SettingDefinition::factory()->for($section, 'section')->create(['input_type' => 'boolean', 'value_type' => 'boolean']);
    $number = SettingDefinition::factory()->for($section, 'section')->create(['input_type' => 'number', 'value_type' => 'integer']);
    $select = SettingDefinition::factory()->for($section, 'section')->create(['input_type' => 'select', 'options' => [['label' => 'Koyu', 'value' => 'dark']]]);
    $this->actingAs($admin, 'admin')->get(route('admin.settings.index'))->assertOk()
        ->assertSee('Varsayılan')->assertSee('name="settings['.$text->id.']"', false)->assertSee('Koyu');
    expect(app(SettingsService::class)->get($text->key))->toBe('Varsayılan');

    $this->put(route('admin.settings.update', $section), ['settings' => [$text->id => 'Yeni başlık', $boolean->id => '0', $number->id => '0', $select->id => 'dark']])
        ->assertSessionHasNoErrors()->assertRedirectToRoute('admin.settings.index', ['section' => $section->id]);

    expect($text->fresh()->effectiveValue())->toBe('Yeni başlık');
    expect($boolean->fresh()->effectiveValue())->toBeFalse();
    expect($number->fresh()->effectiveValue())->toBe(0);
    expect($select->fresh()->effectiveValue())->toBe('dark');
    expect(app(SettingsService::class)->get($text->key))->toBe('Yeni başlık');
});

test('settings rejects invalid selections and unknown field ids without saving', function () {
    $section = SettingSection::factory()->create();
    $definition = SettingDefinition::factory()->for($section, 'section')->create(['label' => 'Renk', 'input_type' => 'select', 'options' => [['label' => 'Koyu', 'value' => 'dark']]]);

    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin')
        ->put(route('admin.settings.update', $section), ['settings' => [$definition->id => 'invalid', 99999 => 'injected']])
        ->assertSessionHasErrors(['settings', 'settings.'.$definition->id => 'Renk için tanımlı bir seçenek seçin.']);

    $this->assertDatabaseCount('setting_values', 0);
});

test('settings keeps existing passwords when blank and redacts passwords after validation errors', function () {
    $section = SettingSection::factory()->create();
    $password = SettingDefinition::factory()->for($section, 'section')->password()->create();
    $text = SettingDefinition::factory()->for($section, 'section')->required()->create();
    $password->storedValue()->create(['value' => ['value' => 'secret-password']]);
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin');

    $this->put(route('admin.settings.update', $section), ['settings' => [$password->id => '', $text->id => 'Valid']])->assertSessionHasNoErrors();
    expect($password->fresh()->effectiveValue())->toBe('secret-password');
    $this->get(route('admin.settings.index'))->assertOk()->assertDontSee('secret-password');
    $this->put(route('admin.settings.update', $section), ['settings' => [$password->id => 'new-secret', $text->id => '']])
        ->assertSessionHasErrors('settings.'.$text->id)
        ->assertSessionMissing('_old_input.settings.'.$password->id);
    expect($password->fresh()->effectiveValue())->toBe('secret-password');
});

test('settings uploads preserves replaces and removes images', function () {
    Storage::fake('public');
    $section = SettingSection::factory()->create();
    $image = SettingDefinition::factory()->for($section, 'section')->image()->create();
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin');
    $url = route('admin.settings.update', $section);
    $this->put($url, ['settings' => [$image->id => UploadedFile::fake()->image('first.png')]])->assertSessionHasNoErrors();
    $first = $image->fresh()->effectiveValue();
    Storage::disk('public')->assertExists($first);
    $this->put($url, ['settings' => [$image->id => null]])->assertSessionHasNoErrors();
    expect($image->fresh()->effectiveValue())->toBe($first);
    $this->put($url, ['settings' => [$image->id => UploadedFile::fake()->image('second.png')]])->assertSessionHasNoErrors();
    $second = $image->fresh()->effectiveValue();
    Storage::disk('public')->assertMissing($first);
    Storage::disk('public')->assertExists($second);
    $this->put($url, ['settings' => [$image->id => null], 'remove' => [$image->id => '1']])->assertSessionHasNoErrors();
    expect($image->fresh()->effectiveValue())->toBeNull();
    Storage::disk('public')->assertMissing($second);
});

test('settings mail enabled still requires its connection fields', function () {
    $section = SettingSection::factory()->create();
    $enabled = SettingDefinition::factory()->for($section, 'section')->create(['key' => 'mail.enabled', 'input_type' => 'boolean', 'value_type' => 'boolean']);
    $host = SettingDefinition::factory()->for($section, 'section')->create(['key' => 'mail.host', 'label' => 'Sunucu']);

    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin')
        ->put(route('admin.settings.update', $section), ['settings' => [$enabled->id => '1', $host->id => '']])
        ->assertSessionHasErrors(['settings.'.$host->id => 'Sunucu alanını doldurun.']);
    $this->assertDatabaseCount('setting_values', 0);
});

test('settings definition creation editing and deletion retain their existing workflow', function () {
    $section = SettingSection::factory()->create();
    $payload = ['section_id' => $section->id, 'key' => 'custom.caption', 'label' => 'Başlık', 'input_type' => 'text', 'value_type' => 'string', 'sort_order' => 0, 'default_value' => 'İlk', 'validation' => ['required' => false, 'max' => 80]];
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin');
    $this->get(route('admin.settings.definitions.create'))->assertOk()->assertSee('name="input_type"', false);
    $this->post(route('admin.settings.definitions.store'), $payload)->assertSessionHasNoErrors()->assertRedirect();
    $definition = SettingDefinition::query()->where('key', 'custom.caption')->sole();
    $this->get(route('admin.settings.definitions.edit', $definition))->assertOk()->assertSee('Başlık');
    $this->put(route('admin.settings.definitions.update', $definition), [...$payload, 'label' => 'Yeni etiket'])->assertSessionHasNoErrors();
    expect($definition->fresh()->label)->toBe('Yeni etiket');
    $this->put(route('admin.settings.definitions.update', $definition), [...$payload, 'key' => 'custom.changed'])->assertSessionHasErrors('key');
    $this->delete(route('admin.settings.definitions.destroy', $definition))->assertRedirect();
    $this->assertModelMissing($definition);
});

test('customer admins cannot configure settings or update unauthorized sections', function () {
    $section = SettingSection::factory()->create();
    $this->actingAs(Admin::factory()->create(), 'admin');
    $this->get(route('admin.settings.definitions.create'))->assertForbidden();
    $this->put(route('admin.settings.update', $section), ['settings' => []])->assertForbidden();
});

test('settings keeps rendering and validating every shared scalar input type', function (string $type, string $valueType, mixed $valid, mixed $invalid, array $attributes, array $validation, array $options, string $message) {
    $section = SettingSection::factory()->create();
    $definition = SettingDefinition::factory()->for($section, 'section')->create([
        'label' => 'Alan', 'input_type' => $type, 'value_type' => $valueType,
        'attributes' => $attributes, 'validation' => $validation, 'options' => $options,
    ]);
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin');
    $this->get(route('admin.settings.index'))->assertOk()->assertSee('name="settings['.$definition->id.']"', false);
    $this->put(route('admin.settings.update', $section), ['settings' => [$definition->id => $valid]])->assertSessionHasNoErrors();
    expect($definition->fresh()->effectiveValue())->toBe($valid);
    $this->put(route('admin.settings.update', $section), ['settings' => [$definition->id => $invalid]])
        ->assertSessionHasErrors(['settings.'.$definition->id => $message]);
    expect($definition->fresh()->effectiveValue())->toBe($valid);
})->with([
    'textarea length' => ['textarea', 'string', 'Metin', str_repeat('x', 11), ['rows' => 3], ['max' => 10], [], 'Alan en fazla 10 karakter olabilir.'],
    'email format' => ['email', 'string', 'test@example.com', 'invalid', [], [], [], 'Alan geçerli bir e-posta adresi olmalıdır.'],
    'float steps' => ['number', 'float', 1.5, 1.6, ['step' => 0.5], ['min' => 0, 'max' => 10], [], 'Değer, minimum değerden itibaren 0.5 adımına uygun olmalıdır.'],
    'range offset steps' => ['range', 'integer', 3, 4, ['step' => 2], ['min' => 1, 'max' => 9], [], 'Değer, minimum değerden itibaren 2 adımına uygun olmalıdır.'],
    'radio options' => ['radio', 'string', 'dark', 'invalid', ['layout' => 'horizontal', 'variant' => 'buttons'], [], [['label' => 'Koyu', 'value' => 'dark']], 'Alan için tanımlı bir seçenek seçin.'],
]);

test('settings password storage is encrypted and can be explicitly cleared', function () {
    $section = SettingSection::factory()->create();
    $password = SettingDefinition::factory()->for($section, 'section')->password()->create();
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin');
    $this->put(route('admin.settings.update', $section), ['settings' => [$password->id => 'example-secret']])->assertSessionHasNoErrors();
    expect($password->fresh()->effectiveValue())->toBe('example-secret');
    expect($password->fresh()->storedValue->getRawOriginal('value'))->not->toContain('example-secret');
    expect(app(SettingsService::class)->all())->not->toHaveKey($password->key);
    $this->put(route('admin.settings.update', $section), ['settings' => [$password->id => null], 'remove' => [$password->id => '1']])->assertSessionHasNoErrors();
    expect($password->fresh()->effectiveValue())->toBeNull();
});

test('settings keeps file uploads and image dimension validation', function () {
    Storage::fake('public');
    $section = SettingSection::factory()->create();
    $file = SettingDefinition::factory()->for($section, 'section')->create(['input_type' => 'file', 'value_type' => 'file', 'validation' => ['extensions' => ['pdf'], 'max_size' => 100]]);
    $image = SettingDefinition::factory()->for($section, 'section')->image()->create(['label' => 'Logo', 'validation' => ['extensions' => ['png'], 'min_width' => 100]]);
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin');
    $this->put(route('admin.settings.update', $section), ['settings' => [$file->id => UploadedFile::fake()->create('guide.pdf', 10, 'application/pdf')]])->assertSessionHasNoErrors();
    Storage::disk('public')->assertExists($file->fresh()->effectiveValue());
    $this->get(route('admin.settings.index'))->assertOk()->assertSee('Mevcut dosyayı görüntüle');
    $this->put(route('admin.settings.update', $section), ['settings' => [$image->id => UploadedFile::fake()->image('tiny.png', 10, 10)]])
        ->assertSessionHasErrors(['settings.'.$image->id => 'Logo görsel ölçüleri tanımlanan sınırlara uymalıdır.']);
    expect($image->fresh()->storedValue)->toBeNull();
    Storage::disk('public')->assertExists($file->fresh()->effectiveValue());
    expect(Storage::disk('public')->allFiles('settings'))->toHaveCount(1);
});

test('system setting definitions cannot be edited or deleted', function () {
    $definition = SettingDefinition::factory()->create(['is_system' => true]);
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin');
    $this->get(route('admin.settings.definitions.edit', $definition))->assertForbidden();
    $this->delete(route('admin.settings.definitions.destroy', $definition))->assertForbidden();
    $this->assertModelExists($definition);
});
