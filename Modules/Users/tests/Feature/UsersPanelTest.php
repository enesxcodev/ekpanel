<?php

use App\Models\Admin\Admin;
use App\Notifications\AdminNotification;
use App\Services\Admin\Modules\ModulePanelService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Modules\Users\Models\User;
use Nwidart\Modules\Facades\Module;

uses(LazilyRefreshDatabase::class);

test('shows users sidebar and shared table without a create action', function () {
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin');

    $response = $this->get(route('admin.web-users.index'))->assertOk()
        ->assertSee('Kullanıcılar')->assertSee('id="web-users-table"', false)
        ->assertSee('href="'.route('admin.web-users.index').'"', false)
        ->assertDontSee('Kullanıcı ekle');
    expect(substr_count($response->getContent(), 'href="'.route('admin.web-users.index').'"'))->toBe(1);
});

test('lists active and inactive web users while excluding deleted accounts and credentials', function () {
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin');
    $active = User::factory()->create(['name' => '<script>alert(1)</script>']);
    $inactive = User::factory()->inactive()->create();
    User::factory()->create()->delete();

    $response = $this->getJson(route('admin.web-users.data', ['draw' => 1, 'start' => 0, 'length' => 10]))
        ->assertOk()->assertJsonPath('recordsTotal', 2);

    $rows = collect($response->json('data'))->keyBy('id');
    expect($rows->get($active->id)['name'])->toContain('&lt;script&gt;');
    expect($rows->get($active->id))->not->toHaveKeys(['password', 'remember_token']);
    expect($rows->get($inactive->id)['is_active'])->toContain('Pasif');
    expect($rows->get($active->id)['actions'])->toContain(route('admin.web-users.edit', $active));
    expect($rows->get($active->id)['actions'])->toContain(route('admin.web-users.destroy', $active));
});

test('edits and soft deletes users without super admin notifications', function () {
    Notification::fake();
    $admin = Admin::factory()->superAdmin()->create();
    $this->actingAs($admin, 'admin');
    $user = User::factory()->create();
    $user->logins()->create(['logged_in_at' => now()]);
    $oldPassword = $user->password;
    $this->get(route('admin.web-users.edit', $user))->assertOk()
        ->assertSee('name="surname"', false)->assertDontSee($oldPassword);

    $this->put(route('admin.web-users.update', $user), [
        'name' => 'Yeni', 'surname' => 'Kullanıcı', 'email' => 'new@example.com', 'username' => null,
        'password' => '', 'is_active' => '0',
    ])->assertSessionHasNoErrors()->assertRedirectToRoute('admin.web-users.index');

    $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Yeni', 'surname' => 'Kullanıcı', 'email' => 'new@example.com', 'is_active' => false, 'email_verified_at' => null]);
    expect($user->fresh()->password)->toBe($oldPassword);

    $this->delete(route('admin.web-users.destroy', $user))->assertRedirectToRoute('admin.web-users.index');
    $this->assertSoftDeleted($user);
    $this->assertDatabaseCount('user_logins', 1);
    Notification::assertNothingSent();
});

test('notifies admins with edit and list links after authorized regular admin changes', function () {
    Notification::fake();
    app(ModulePanelService::class)->syncPermissions();
    $actor = Admin::factory()->create();
    $actor->givePermissionTo(['users.view', 'users.update', 'users.delete']);
    $recipient = Admin::factory()->superAdmin()->create();
    $this->actingAs($actor, 'admin');
    $user = User::factory()->create();

    $this->put(route('admin.web-users.update', $user), [
        'name' => $user->name, 'surname' => $user->surname, 'email' => $user->email, 'is_active' => true,
        'password' => 'New-Secure123!',
    ])->assertSessionHasNoErrors()->assertRedirectToRoute('admin.web-users.index');

    expect(Hash::check('New-Secure123!', $user->fresh()->password))->toBeTrue();
    Notification::assertSentTo($recipient, AdminNotification::class, fn (AdminNotification $notification): bool => $notification->title === 'Kullanıcı güncellendi' && $notification->link === route('admin.web-users.edit', $user, absolute: false));
    $this->delete(route('admin.web-users.destroy', $user))->assertRedirectToRoute('admin.web-users.index');
    Notification::assertSentTo($recipient, AdminNotification::class, fn (AdminNotification $notification): bool => $notification->title === 'Kullanıcı silindi' && $notification->link === route('admin.web-users.index', absolute: false));
});

test('refuses every panel operation without the corresponding admin permission', function () {
    app(ModulePanelService::class)->syncPermissions();
    $this->actingAs(Admin::factory()->create(), 'admin');
    $user = User::factory()->create();
    $originalName = $user->name;

    $this->get(route('admin.web-users.index'))->assertForbidden();
    $this->getJson(route('admin.web-users.data'))->assertForbidden();
    $this->get(route('admin.web-users.edit', $user))->assertForbidden();
    $this->put(route('admin.web-users.update', $user), ['name' => 'Blocked'])->assertForbidden();
    $this->delete(route('admin.web-users.destroy', $user))->assertForbidden();

    expect($user->fresh()->name)->toBe($originalName);
    $this->assertNotSoftDeleted($user);
    expect(collect(app(ModulePanelService::class)->menus(auth('admin')->user()))->contains('route', 'admin.web-users.index'))->toBeFalse();
});

test('requires both menu and view permission to show the users sidebar', function () {
    app(ModulePanelService::class)->syncPermissions();
    $admin = Admin::factory()->create();
    $admin->givePermissionTo('users.view');
    expect(collect(app(ModulePanelService::class)->menus($admin))->contains('route', 'admin.web-users.index'))->toBeFalse();

    $admin->givePermissionTo('users.menu');

    expect(collect(app(ModulePanelService::class)->menus($admin))->contains('route', 'admin.web-users.index'))->toBeTrue();
});

test('rejects invalid edits without changing the user', function () {
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin');
    $user = User::factory()->create();
    $originalEmail = $user->email;

    $this->put(route('admin.web-users.update', $user), ['name' => 'Changed', 'email' => 'invalid'])
        ->assertSessionHasErrors(['surname', 'email', 'is_active']);

    expect($user->fresh()->email)->toBe($originalEmail);
    expect($user->fresh()->name)->toBe($user->name);
});

test('protects the panel from web accounts and guests', function () {
    $this->get(route('admin.web-users.index'))->assertRedirectToRoute('admin.login');
    $this->actingAs(User::factory()->create(), 'web')->get(route('admin.web-users.index'))->assertRedirectToRoute('admin.login');
});

test('blocks inactive admins and disabled modules', function () {
    $this->actingAs(Admin::factory()->inactive()->create(), 'admin');
    $this->get(route('admin.web-users.index'))->assertRedirectToRoute('admin.login');
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin');
    $module = Mockery::mock(Nwidart\Modules\Module::class);
    $module->shouldReceive('isEnabled')->andReturnFalse();
    Module::partialMock()->shouldReceive('find')->with('Users')->andReturn($module);

    $this->get(route('admin.web-users.index'))->assertNotFound();
});
