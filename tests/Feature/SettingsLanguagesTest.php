<?php

use App\Models\Admin\Admin;
use App\Models\Settings\Language;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Spatie\Permission\Models\Permission;

uses(LazilyRefreshDatabase::class);

test('languages tab renders active and inactive languages without settings sections', function () {
    Language::factory()->create(['code' => 'tr']);
    Language::factory()->inactive()->create(['code' => 'de']);
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin')
        ->get(route('admin.settings.index', ['section' => 'languages']))
        ->assertOk()->assertSee('Türkçe')->assertSee('Almanca')->assertSee('Pasif')
        ->assertSee('data-flag="gb"', false)->assertDontSee('option value="tr"', false)
        ->assertSee('show active" id="settings-panel-languages"', false);
});

test('language can be added disabled and enabled with only intended fields changed', function () {
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin');
    $this->post(route('admin.settings.languages.store'), ['code' => 'tr', 'is_active' => false])
        ->assertSessionHasNoErrors()->assertRedirectToRoute('admin.settings.index', ['section' => 'languages'])
        ->assertSessionHas('notification.message', 'Dil eklendi.');
    $this->assertDatabaseHas('languages', ['code' => 'tr', 'is_active' => true]);
    $language = Language::query()->sole();
    $this->patch(route('admin.settings.languages.update', $language), ['is_active' => '0', 'code' => 'de'])
        ->assertSessionHasNoErrors()->assertRedirectToRoute('admin.settings.index', ['section' => 'languages']);
    $this->assertDatabaseHas('languages', ['id' => $language->id, 'code' => 'tr', 'is_active' => false]);
    $this->patch(route('admin.settings.languages.update', $language), ['is_active' => '1'])->assertSessionHasNoErrors();
    expect($language->fresh()->is_active)->toBeTrue();
});

test('language selection rejects missing and unsupported codes', function (array $payload, string $message) {
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin')
        ->post(route('admin.settings.languages.store'), $payload)->assertSessionHasErrors(['code' => $message]);
    $this->assertDatabaseCount('languages', 0);
})->with([
    'missing' => [[], 'Bir dil seçin.'],
    'unknown' => [['code' => 'invalid'], 'Geçerli bir dil seçin.'],
    'array' => [['code' => ['tr']], 'Geçerli bir dil seçin.'],
]);

test('an inactive language cannot be added twice', function () {
    Language::factory()->inactive()->create(['code' => 'tr']);
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin')
        ->post(route('admin.settings.languages.store'), ['code' => 'tr'])
        ->assertSessionHasErrors(['code' => 'Bu dil zaten eklenmiş. Listeden durumunu değiştirebilirsiniz.']);
    $this->assertDatabaseCount('languages', 1);
    $this->assertDatabaseHas('languages', ['code' => 'tr', 'is_active' => false]);
});

test('invalid status is rejected without changing the language', function (array $payload, string $message) {
    $language = Language::factory()->create();
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin')
        ->patch(route('admin.settings.languages.update', $language), $payload)
        ->assertSessionHasErrors(['is_active' => $message]);
    expect($language->fresh()->is_active)->toBeTrue();
})->with([
    'missing' => [[], 'Dil durumunu belirtin.'],
    'invalid' => [['is_active' => 'invalid'], 'Geçerli bir dil durumu seçin.'],
]);

test('view only settings access cannot add or change languages', function () {
    $admin = Admin::factory()->create();
    $admin->givePermissionTo(Permission::findOrCreate('settings.view', 'admin'));
    $language = Language::factory()->create();
    $this->actingAs($admin, 'admin')->get(route('admin.settings.index'))->assertOk()->assertSee('Dil düzenleme izniniz bulunmuyor.');
    $this->post(route('admin.settings.languages.store'), ['code' => 'en'])->assertForbidden();
    $this->patch(route('admin.settings.languages.update', $language), ['is_active' => false])->assertForbidden();
    $this->assertDatabaseCount('languages', 1);
    expect($language->fresh()->is_active)->toBeTrue();
});

test('unauthenticated language writes redirect to admin login', function () {
    $language = Language::factory()->create();
    $this->post(route('admin.settings.languages.store'), ['code' => 'tr'])->assertRedirectToRoute('admin.login');
    $this->patch(route('admin.settings.languages.update', $language), ['is_active' => false])->assertRedirectToRoute('admin.login');
    expect($language->fresh()->is_active)->toBeTrue();
});
