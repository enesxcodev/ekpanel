<?php

use App\Models\Admin\Admin;
use Database\Seeders\AdminPermissionSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(LazilyRefreshDatabase::class);

test('role editor renders the separate menu visibility checkbox', function () {
    $this->seed(AdminPermissionSeeder::class);
    $admin = Admin::factory()->superAdmin()->create();
    $role = Role::findByName('customer-admin', 'admin');

    $this->actingAs($admin, 'admin')->get(route('admin.roles.edit', $role))
        ->assertOk()->assertSee('Menü Show')->assertSee('value="users.menu"', false);
});

test('super admins can grant and remove menu permission independently of page permission', function (array $permissions) {
    $this->seed(AdminPermissionSeeder::class);
    $admin = Admin::factory()->superAdmin()->create();
    $role = Role::findByName('customer-admin', 'admin');
    $role->givePermissionTo(['users.menu', 'users.view']);

    $this->actingAs($admin, 'admin')->put(route('admin.roles.update', $role), ['permissions' => $permissions])
        ->assertRedirectToRoute('admin.roles.edit', $role)->assertSessionHasNoErrors();

    expect($role->fresh()->permissions->pluck('name')->sort()->values()->all())->toBe($permissions);
})->with([
    'both permissions' => [['users.menu', 'users.view']],
    'page access only' => [['users.view']],
    'menu permission only' => [['users.menu']],
    'no permissions' => [[]],
]);

test('customer admins cannot change menu permissions', function () {
    $this->seed(AdminPermissionSeeder::class);
    $admin = Admin::factory()->create();
    $role = Role::findByName('customer-admin', 'admin');

    $this->actingAs($admin, 'admin')->put(route('admin.roles.update', $role), ['permissions' => ['users.menu']])
        ->assertForbidden();

    expect($role->fresh()->permissions)->toBeEmpty();
});

test('permissions for another guard cannot be assigned to an admin role', function () {
    $this->seed(AdminPermissionSeeder::class);
    Permission::findOrCreate('foreign.menu', 'web');
    $admin = Admin::factory()->superAdmin()->create();
    $role = Role::findByName('customer-admin', 'admin');

    $this->actingAs($admin, 'admin')->put(route('admin.roles.update', $role), ['permissions' => ['foreign.menu']])
        ->assertSessionHasErrors('permissions.0');

    expect($role->fresh()->permissions)->toBeEmpty();
});

test('users sidebar menu requires both menu and page permissions', function (array $permissions, bool $visible) {
    $this->seed(AdminPermissionSeeder::class);
    $admin = Admin::factory()->create();
    $admin->givePermissionTo($permissions);

    $response = $this->actingAs($admin, 'admin')->get(route('admin.dashboard'))->assertOk();

    if ($visible) {
        $response->assertSee('Yöneticiler');
    } else {
        $response->assertDontSee('Yöneticiler');
    }
})->with([
    'page permission only' => [['users.view'], false],
    'menu permission only' => [['users.menu'], false],
    'both permissions' => [['users.menu', 'users.view'], true],
]);
