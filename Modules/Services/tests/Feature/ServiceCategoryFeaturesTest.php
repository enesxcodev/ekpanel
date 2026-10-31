<?php

use App\Models\Admin\Admin;
use App\Models\Settings\Language;
use Database\Seeders\AdminPermissionSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Modules\Services\Models\ServiceCategory;

uses(LazilyRefreshDatabase::class);

test('service category create and edit forms render shared category fields and preserve their values', function () {
    $category = ServiceCategory::factory()->create(['extra_fields' => ['short_description' => 'Üretimden önce satılan hizmetler', 'badge_text' => 'Öne çıkan']]);
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin');

    $this->get(route('admin.services.categories.create'))->assertOk()
        ->assertSeeInOrder(['name="name"', 'name="extra_fields[short_description]"', 'name="extra_fields[badge_text]"', 'name="extra_fields[homepage_display]"'], false);
    $this->get(route('admin.services.categories.edit', $category))->assertOk()
        ->assertSee('Üretimden önce satılan hizmetler')->assertSee('Öne çıkan');
});

test('service category creation stores feature values and updates without erasing omitted values', function () {
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin');
    $this->post(route('admin.services.categories.store'), [
        'lang_id' => Language::query()->where('code', 'tr')->value('id'),
        'name' => 'Danışmanlık',
        'extra_fields' => ['short_description' => 'Şirketlere özel danışmanlık', 'badge_text' => 'Popüler'],
    ])->assertRedirect()->assertSessionHasNoErrors();
    $category = ServiceCategory::query()->sole();
    expect($category->name)->toBe('Danışmanlık');
    expect($category->extra_fields)->toBe(['short_description' => 'Şirketlere özel danışmanlık', 'badge_text' => 'Popüler', 'homepage_display' => 'cards']);

    $this->put(route('admin.services.categories.update', $category), [
        'lang_id' => Language::query()->where('code', 'tr')->value('id'),
        'name' => 'Kurumsal danışmanlık', 'extra_fields' => ['badge_text' => null],
    ])->assertRedirect()->assertSessionHasNoErrors();
    expect($category->fresh()->name)->toBe('Kurumsal danışmanlık');
    expect($category->fresh()->extra_fields)->toBe(['short_description' => 'Şirketlere özel danışmanlık', 'badge_text' => null, 'homepage_display' => 'cards']);
});

test('service category rejects unknown extra fields and keeps the submitted record unchanged', function () {
    $category = ServiceCategory::factory()->create(['name' => 'Original', 'extra_fields' => ['badge_text' => 'Mevcut']]);

    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin')
        ->put(route('admin.services.categories.update', $category), [
            'lang_id' => Language::query()->where('code', 'tr')->value('id'),
            'name' => 'Tampered', 'extra_fields' => ['unknown_key' => 'Injected'],
        ])->assertSessionHasErrors('extra_fields');

    expect($category->fresh()->name)->toBe('Original');
    expect($category->fresh()->extra_fields)->toBe(['badge_text' => 'Mevcut']);
});

test('customer admins need category create and update permissions for feature forms and writes', function (string $method, string $action, bool $granted) {
    $this->seed(AdminPermissionSeeder::class);
    $admin = Admin::factory()->create();
    if ($granted) {
        $admin->givePermissionTo($action === 'store' ? 'services.categories.create' : 'services.categories.update');
    }
    $category = ServiceCategory::factory()->create(['name' => 'Original']);
    $url = route('admin.services.categories.'.$action, $action === 'store' ? [] : $category);

    $response = $this->actingAs($admin, 'admin')->{$method}($url, ['lang_id' => Language::query()->where('code', 'tr')->value('id'), 'name' => 'Updated', 'extra_fields' => ['badge_text' => 'New']]);
    if (! $granted) {
        $response->assertForbidden();
        expect($category->fresh()->name)->toBe('Original');
    } else {
        $response->assertRedirect();
    }
})->with([
    'store' => ['post', 'store'],
    'update' => ['put', 'update'],
])->with([true, false]);

test('service categories created before feature fields were added keep empty feature data', function () {
    $category = ServiceCategory::factory()->create();

    expect($category->fresh()->extra_fields)->toBeNull();
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin')
        ->get(route('admin.services.categories.edit', $category))->assertOk()
        ->assertSee('name="extra_fields[badge_text]"', false);
});
