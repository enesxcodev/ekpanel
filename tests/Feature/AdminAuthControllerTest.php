<?php

use App\Enums\AdminType\AdminTypeEnum;
use App\Models\Admin\Admin;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Routing\RouteCollection;
use Illuminate\Support\Facades\Route;

uses(LazilyRefreshDatabase::class);

test('guests can see the login form without password management links', function () {
    $this->get(route('admin.login'))
        ->assertOk()
        ->assertSee('Yönetim paneline giriş')
        ->assertSee('name="email"', false)
        ->assertSee('name="password"', false)
        ->assertDontSee('Şifremi unuttum')
        ->assertDontSee('Şifremi değiştir');
});

test('guests are redirected from the dashboard to admin login', function () {
    $this->get(route('admin.dashboard'))->assertRedirectToRoute('admin.login');
});

test('web users cannot access the admin dashboard', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'web')->get(route('admin.dashboard'))->assertRedirectToRoute('admin.login');
});

test('active admins of either type log in with the admin guard and reach the dashboard', function (AdminTypeEnum $type) {
    $admin = Admin::factory()->create(['type' => $type]);
    $this->withSession(['_token' => 'original-token']);
    $previousSessionId = session()->getId();

    $this->post(route('admin.login.store'), ['email' => $admin->email, 'password' => 'password'])
        ->assertRedirectToRoute('admin.dashboard');

    $this->assertAuthenticatedAs($admin, 'admin');
    $this->assertGuest('web');
    expect(session()->getId())->not->toBe($previousSessionId);
    $this->get(route('admin.dashboard'))->assertOk()->assertSee($admin->name)->assertSee($admin->lastname);
})->with([AdminTypeEnum::Admin, AdminTypeEnum::SuperAdmin]);

test('admin login rejects incorrect credentials and inactive accounts', function (string $scenario) {
    $admin = match ($scenario) {
        'inactive' => Admin::factory()->inactive()->create(),
        default => Admin::factory()->create(),
    };
    $email = $scenario === 'unknown' ? 'unknown@example.com' : $admin->email;
    $password = $scenario === 'wrong password' ? 'wrong-password' : 'password';

    $this->from(route('admin.login'))->post(route('admin.login.store'), ['email' => $email, 'password' => $password])
        ->assertRedirectToRoute('admin.login')
        ->assertSessionHasErrors(['email' => 'E-posta adresi veya şifre hatalı.'])
        ->assertSessionMissing('_old_input.password');

    $this->assertGuest('admin');
})->with(['wrong password', 'unknown', 'inactive']);

test('web credentials cannot authenticate an admin', function () {
    $user = User::factory()->create();

    $this->post(route('admin.login.store'), ['email' => $user->email, 'password' => 'password'])
        ->assertSessionHasErrors('email');

    $this->assertGuest('admin');
});

test('login requires email and password', function () {
    $this->post(route('admin.login.store'), [])
        ->assertSessionHasErrors(['email' => 'E-posta adresinizi girin.', 'password' => 'Şifrenizi girin.']);

    $this->assertGuest('admin');
});

test('login rejects invalid input', function (array $input, string $field, string $message) {
    $this->post(route('admin.login.store'), $input)->assertSessionHasErrors([$field => $message]);

    $this->assertGuest('admin');
})->with([
    'invalid email' => [['email' => 'invalid', 'password' => 'password'], 'email', 'Geçerli bir e-posta adresi girin.'],
    'email array' => [['email' => ['admin@example.com'], 'password' => 'password'], 'email', 'Geçerli bir e-posta adresi girin.'],
    'long email' => [['email' => str_repeat('a', 244).'@example.com', 'password' => 'password'], 'email', 'E-posta adresi en fazla 255 karakter olabilir.'],
    'password array' => [['email' => 'admin@example.com', 'password' => ['password']], 'password', 'Geçerli bir şifre girin.'],
]);

test('login blocks the sixth failed attempt and allows a retry after one minute', function () {
    $this->freezeTime();
    $admin = Admin::factory()->create();
    $credentials = ['email' => $admin->email, 'password' => 'wrong'];
    foreach (range(1, 5) as $attempt) {
        $this->post(route('admin.login.store'), $credentials)->assertSessionHasErrors('email');
    }

    $this->post(route('admin.login.store'), [...$credentials, 'password' => 'password'])
        ->assertSessionHasErrors(['email' => 'Çok fazla giriş denemesi yaptınız. 60 saniye sonra tekrar deneyin.']);
    $this->assertGuest('admin');

    $this->travel(61)->seconds();
    $this->post(route('admin.login.store'), [...$credentials, 'password' => 'password'])
        ->assertRedirectToRoute('admin.dashboard');
    $this->assertAuthenticatedAs($admin, 'admin');
});

test('successful login clears previous failed attempts', function () {
    $admin = Admin::factory()->create();
    foreach (range(1, 4) as $attempt) {
        $this->post(route('admin.login.store'), ['email' => $admin->email, 'password' => 'wrong']);
    }
    $this->post(route('admin.login.store'), ['email' => $admin->email, 'password' => 'password'])
        ->assertRedirectToRoute('admin.dashboard');
    $this->post(route('admin.logout'));
    foreach (range(1, 4) as $attempt) {
        $this->post(route('admin.login.store'), ['email' => $admin->email, 'password' => 'wrong']);
    }

    $this->post(route('admin.login.store'), ['email' => $admin->email, 'password' => 'password'])
        ->assertRedirectToRoute('admin.dashboard');
});

test('authenticated admins are redirected away from the login form', function () {
    $admin = Admin::factory()->create();

    $this->actingAs($admin, 'admin')->get(route('admin.login'))->assertRedirectToRoute('admin.dashboard');
    $this->post(route('admin.login.store'), [])->assertRedirectToRoute('admin.dashboard');
});

test('deactivated admins lose access to an existing session', function () {
    $admin = Admin::factory()->create();
    $this->actingAs($admin, 'admin');
    $admin->update(['status' => false]);

    $this->get(route('admin.dashboard'))->assertRedirectToRoute('admin.login');

    $this->assertGuest('admin');
});

test('logout invalidates the admin session and refreshes the csrf token', function () {
    $admin = Admin::factory()->create();
    $this->actingAs($admin, 'admin')->withSession(['_token' => 'old-token', 'private-data' => 'secret']);

    $this->post(route('admin.logout'))->assertRedirectToRoute('admin.login')->assertSessionMissing('private-data');

    $this->assertGuest('admin');
    expect(session()->token())->not->toBe('old-token');
    $this->get(route('admin.dashboard'))->assertRedirectToRoute('admin.login');
});

test('dashboard escapes admin names', function () {
    $admin = Admin::factory()->create(['name' => '<script>name</script>', 'lastname' => '<script>lastname</script>']);

    $this->actingAs($admin, 'admin')->get(route('admin.dashboard'))->assertOk()
        ->assertSee($admin->name)->assertSee($admin->lastname)
        ->assertDontSee($admin->name, false)->assertDontSee($admin->lastname, false);
});

test('admin routes respect a custom prefix', function () {
    $admin = Admin::factory()->create();
    config()->set('app.route_files.admin_prefix', 'yonetim');
    $this->app['router']->setRoutes(new RouteCollection);
    Route::middleware('web')->group(base_path('routes/admin.php'));
    $this->app['router']->getRoutes()->refreshNameLookups();
    $this->app['router']->getRoutes()->refreshActionLookups();
    $this->app['url']->setRoutes($this->app['router']->getRoutes());

    $this->get('/yonetim')->assertRedirect('/yonetim/login');
    $this->get('/yonetim/login')->assertOk()->assertSee('action="'.url('/yonetim/login').'"', false);
    $this->post('/yonetim/login', ['email' => $admin->email, 'password' => 'password'])->assertRedirect('/yonetim');
    $this->get('/yonetim')->assertOk();
    $this->post('/yonetim/logout')->assertRedirect('/yonetim/login');
});
