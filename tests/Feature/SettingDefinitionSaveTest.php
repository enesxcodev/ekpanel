<?php

use App\Models\Admin\Admin;
use App\Models\Settings\SettingSection;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

test('settings creates a text definition without first visiting its form', function () {
    $section = SettingSection::factory()->create();
    $admin = Admin::factory()->superAdmin()->create();

    $this->actingAs($admin, 'admin')->post(route('admin.settings.definitions.store'), [
        'section_id' => $section->id,
        'key' => 'default_home.hero_eyebrow',
        'label' => 'Hero eyebrow',
        'input_type' => 'text',
        'value_type' => 'string',
        'sort_order' => 0,
        'default_value' => 'Hoş geldiniz',
        'validation' => ['required' => false],
    ])->assertSessionHasNoErrors()->assertRedirect();

    $this->assertDatabaseHas('setting_definitions', [
        'section_id' => $section->id,
        'key' => 'default_home.hero_eyebrow',
        'label' => 'Hero eyebrow',
        'input_type' => 'text',
        'value_type' => 'string',
    ]);
});
