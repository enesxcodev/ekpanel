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
        ->assertOk()->assertSee('Menü Show')->assertSee('value="admins.menu"', false);
});

test('super admins can grant and remove menu permission independently of page permission', function (array $permissions) {
    $this->seed(AdminPermissionSeeder::class);
    $admin = Admin::factory()->superAdmin()->create();
    $role = Role::findByName('customer-admin', 'admin');
    $role->givePermissionTo(['admins.menu', 'admins.view']);

    $this->actingAs($admin, 'admin')->put(route('admin.roles.update', $role), ['permissions' => $permissions])
        ->assertRedirectToRoute('admin.roles.edit', $role)->assertSessionHasNoErrors();

    expect($role->fresh()->permissions->pluck('name')->sort()->values()->all())->toBe($permissions);
})->with([
    'both permissions' => [['admins.menu', 'admins.view']],
    'page access only' => [['admins.view']],
    'menu permission only' => [['admins.menu']],
    'no permissions' => [[]],
]);

test('customer admins cannot change menu permissions', function () {
    $this->seed(AdminPermissionSeeder::class);
    $admin = Admin::factory()->create();
    $role = Role::findByName('customer-admin', 'admin');
    $originalPermissions = $role->permissions->pluck('id')->sort()->values()->all();

    $this->actingAs($admin, 'admin')->put(route('admin.roles.update', $role), ['permissions' => ['admins.menu']])
        ->assertForbidden();

    expect($role->fresh()->permissions->pluck('id')->sort()->values()->all())->toBe($originalPermissions);
});

test('permissions for another guard cannot be assigned to an admin role', function () {
    $this->seed(AdminPermissionSeeder::class);
    Permission::findOrCreate('foreign.menu', 'web');
    $admin = Admin::factory()->superAdmin()->create();
    $role = Role::findByName('customer-admin', 'admin');
    $originalPermissions = $role->permissions->pluck('id')->sort()->values()->all();

    $this->actingAs($admin, 'admin')->put(route('admin.roles.update', $role), ['permissions' => ['foreign.menu']])
        ->assertSessionHasErrors('permissions.0');

    expect($role->fresh()->permissions->pluck('id')->sort()->values()->all())->toBe($originalPermissions);
});

test('admins sidebar menu requires both menu and page permissions', function (array $permissions, bool $visible) {
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
    'no permissions' => [[], false],
    'page permission only' => [['admins.view'], false],
    'menu permission only' => [['admins.menu'], false],
    'both permissions' => [['admins.menu', 'admins.view'], true],
    'web user permissions do not grant admin menu access' => [['users.menu', 'users.view'], false],
]);
