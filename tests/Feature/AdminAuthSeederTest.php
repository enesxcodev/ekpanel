<?php

use App\Enums\AdminType\AdminTypeEnum;
use App\Models\Admin\Admin;
use Database\Seeders\AdminAuthSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;

uses(LazilyRefreshDatabase::class);

test('seeder creates both active admin types and prints working initial passwords', function () {
    Artisan::call('db:seed', ['--class' => AdminAuthSeeder::class, '--no-interaction' => true]);
    $output = Artisan::output();

    $this->assertDatabaseCount('admins', 2);
    foreach (['admin@example.com' => AdminTypeEnum::Admin, 'superadmin@example.com' => AdminTypeEnum::SuperAdmin] as $email => $type) {
        $admin = Admin::query()->where('email', $email)->firstOrFail();
        preg_match('/'.preg_quote($email, '/').' \| Şifre: (.+)/u', $output, $matches);
        $password = trim($matches[1]);
        expect($admin->type)->toBe($type);
        expect($admin->status)->toBeTrue();
        expect($admin->name)->not->toBeEmpty();
        expect($admin->lastname)->not->toBeEmpty();
        expect(Hash::check($password, $admin->password))->toBeTrue();
        $this->post(route('admin.login.store'), ['email' => $email, 'password' => $password])
            ->assertRedirectToRoute('admin.dashboard');
        $this->assertAuthenticatedAs($admin, 'admin');
        $this->post(route('admin.logout'));
    }
});

test('repeated seeding preserves existing account credentials and status', function () {
    $admin = Admin::factory()->inactive()->create(['email' => 'admin@example.com', 'name' => 'Existing']);
    $originalPassword = $admin->password;

    $this->seed(AdminAuthSeeder::class);
    $superAdminPassword = Admin::query()->where('email', 'superadmin@example.com')->firstOrFail()->password;
    $this->seed(AdminAuthSeeder::class);

    $this->assertDatabaseCount('admins', 2);
    expect($admin->fresh()->password)->toBe($originalPassword);
    expect($admin->fresh()->status)->toBeFalse();
    expect($admin->fresh()->name)->toBe('Existing');
    expect(Admin::query()->where('email', 'superadmin@example.com')->firstOrFail()->password)->toBe($superAdminPassword);
});

test('new admins default to the admin type and active status with a hashed password', function () {
    $admin = Admin::query()->create(['email' => 'default@example.com', 'name' => 'Default', 'lastname' => 'Admin', 'password' => 'initial-secret']);

    $admin->refresh();

    expect($admin->type)->toBe(AdminTypeEnum::Admin);
    expect($admin->status)->toBeTrue();
    expect(Hash::check('initial-secret', $admin->password))->toBeTrue();
    expect($admin->toArray())->not->toHaveKeys(['password', 'remember_token']);
});
