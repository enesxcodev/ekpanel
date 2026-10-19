<?php

use App\Models\Admin\Admin;
use Illuminate\Auth\Events\PasswordReset as PasswordResetEvent;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;
use Modules\Users\Models\User;
use Modules\Users\Notifications\ResetPassword;
use Modules\Users\Notifications\VerifyEmail;
use Modules\Users\Services\UserService;
use Modules\Users\Services\UsersFrontendService;
use Nwidart\Modules\Facades\Module;

uses(LazilyRefreshDatabase::class);

test('logs in with email and records login metadata without exposing credentials', function () {
    $this->freezeSecond();
    $user = User::factory()->unverified()->create();
    $this->withSession(['sentinel' => 'kept']);
    $sessionId = session()->getId();

    $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.8', 'HTTP_USER_AGENT' => 'Users test browser'])
        ->postJson(route('users.login'), ['email' => $user->email, 'password' => 'password', 'remember' => true])
        ->assertOk()->assertJsonPath('user.id', $user->id)
        ->assertJsonMissingPath('user.password')->assertJsonMissingPath('user.remember_token');

    $this->assertAuthenticatedAs($user, 'web');
    $this->assertGuest('admin');
    expect(session()->getId())->not->toBe($sessionId);
    $this->assertDatabaseHas('user_logins', ['user_id' => $user->id, 'ip_address' => '203.0.113.8', 'user_agent' => 'Users test browser', 'logged_in_at' => now()]);
    expect($user->fresh()->last_login_at->equalTo(now()))->toBeTrue();
    expect($user->fresh()->last_login_ip)->toBe('203.0.113.8');
    expect($user->fresh()->last_login_user_agent)->toBe('Users test browser');
});

test('rejects invalid inactive deleted and username login credentials', function (string $case) {
    $user = User::factory()->create(['username' => 'someone']);
    if ($case === 'inactive') {
        $user->update(['is_active' => false]);
    }
    if ($case === 'deleted') {
        $user->delete();
    }

    $this->postJson(route('users.login'), [
        'email' => $case === 'username' ? 'someone' : $user->email,
        'password' => $case === 'wrong-password' ? 'wrong' : 'password',
    ])->assertUnprocessable()->assertJsonValidationErrors('email');

    $this->assertGuest('web');
    $this->assertDatabaseCount('user_logins', 0);
})->with(['wrong-password', 'inactive', 'deleted', 'username']);

test('throttles repeated login failures even when the next password is correct', function () {
    $user = User::factory()->create();
    foreach (range(1, 5) as $attempt) {
        $this->postJson(route('users.login'), ['email' => $user->email, 'password' => 'wrong'])->assertUnprocessable();
    }

    $this->postJson(route('users.login'), ['email' => $user->email, 'password' => 'password'])
        ->assertUnprocessable()->assertJsonPath('errors.email.0', 'Çok fazla deneme. Lütfen daha sonra tekrar deneyin.');

    $this->assertGuest('web');
    $this->assertDatabaseCount('user_logins', 0);
});

test('active provider rejects deactivated accounts for session and remember retrieval', function () {
    $user = User::factory()->create();
    $token = $user->remember_token;
    $user->update(['is_active' => false]);
    $provider = Auth::guard('web')->getProvider();

    expect($provider->retrieveById($user->id))->toBeNull();
    expect($provider->retrieveByToken($user->id, $token))->toBeNull();
});

test('returns only the active current web account and logs out its session', function () {
    $user = User::factory()->create();
    $this->actingAs($user, 'web');
    $this->getJson(route('users.current'))->assertOk()->assertJsonPath('user.id', $user->id);
    $user->update(['is_active' => false]);
    $this->getJson(route('users.current'))->assertOk()->assertJsonPath('user', null);

    $this->postJson(route('users.logout'))->assertOk()->assertJsonPath('status', 'logged-out');

    $this->assertGuest('web');
});

test('sends a module verification link and verifies only the authenticated owner', function () {
    Notification::fake();
    Event::fake([Verified::class]);
    $user = User::factory()->unverified()->create();
    $other = User::factory()->unverified()->create();
    $this->actingAs($user, 'web');

    $this->postJson(route('users.verification.send'))->assertOk()->assertJsonPath('sent', true);
    Notification::assertSentTo($user, VerifyEmail::class, function (VerifyEmail $notification) use ($user): bool {
        return str_contains($notification->toMail($user)->actionUrl, '/users/email/verify/'.$user->id.'/');
    });
    $link = URL::temporarySignedRoute('users.verification.verify', now()->addMinutes(60), ['id' => $other->id, 'hash' => sha1($other->email)]);
    $this->getJson($link)->assertForbidden();
    expect($other->fresh()->hasVerifiedEmail())->toBeFalse();

    $link = URL::temporarySignedRoute('users.verification.verify', now()->addMinutes(60), ['id' => $user->id, 'hash' => sha1($user->email)]);
    $this->getJson($link)->assertOk()->assertJsonPath('status', 'email-verified');

    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
    Event::assertDispatched(Verified::class, fn (Verified $event): bool => $event->user->is($user));
    $this->postJson(route('users.verification.send'))->assertOk()->assertJsonPath('sent', false);
});

test('rejects expired or tampered email verification links', function (string $case) {
    $user = User::factory()->unverified()->create();
    $this->actingAs($user, 'web');
    $link = URL::temporarySignedRoute('users.verification.verify', $case === 'expired' ? now()->subMinute() : now()->addHour(), ['id' => $user->id, 'hash' => sha1($user->email)]);
    if ($case === 'tampered') {
        $link .= '&extra=tampered';
    }

    $this->getJson($link)->assertForbidden();

    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
})->with(['expired', 'tampered']);

test('requires a web account to send verification even with an admin session', function () {
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin');

    $this->postJson(route('users.verification.send'))->assertUnauthorized();
});

test('throttles verification emails', function () {
    Notification::fake();
    $user = User::factory()->unverified()->create();
    $this->actingAs($user, 'web');
    $this->postJson(route('users.verification.send'))->assertOk();

    $this->postJson(route('users.verification.send'))->assertUnprocessable();

    Notification::assertSentToTimes($user, VerifyEmail::class, 1);
});

test('sends reset notification with module link and resets password once', function () {
    Notification::fake();
    Event::fake([PasswordResetEvent::class]);
    $user = User::factory()->create();
    $oldRememberToken = $user->remember_token;
    $this->postJson(route('users.password.email'), ['email' => $user->email])->assertOk()->assertJsonPath('status', Password::ResetLinkSent);
    $token = '';
    Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use ($user, &$token): bool {
        $token = $notification->token;

        return str_contains($notification->toMail($user)->actionUrl, '/users/reset-password/');
    });
    $payload = ['email' => $user->email, 'token' => $token, 'password' => 'New-Secure123!', 'password_confirmation' => 'New-Secure123!'];

    $this->postJson(route('users.password.update'), $payload)->assertOk()->assertJsonPath('status', Password::PasswordReset);

    expect(Hash::check('New-Secure123!', $user->fresh()->password))->toBeTrue();
    expect($user->fresh()->remember_token)->not->toBe($oldRememberToken);
    $this->assertDatabaseMissing('password_reset_tokens', ['email' => $user->email]);
    Event::assertDispatched(PasswordResetEvent::class, fn (PasswordResetEvent $event): bool => $event->user->is($user));
    $this->postJson(route('users.password.update'), $payload)->assertUnprocessable();
});

test('returns the same reset request response for missing inactive and deleted accounts', function (string $case) {
    Notification::fake();
    $user = User::factory()->create();
    if ($case === 'inactive') {
        $user->update(['is_active' => false]);
    }
    if ($case === 'deleted') {
        $user->delete();
    }

    $this->postJson(route('users.password.email'), ['email' => $case === 'missing' ? 'absent@example.com' : $user->email])
        ->assertOk()->assertJsonPath('status', Password::ResetLinkSent);

    Notification::assertNothingSent();
    $this->assertDatabaseCount('password_reset_tokens', 0);
})->with(['missing', 'inactive', 'deleted']);

test('rejects invalid expired weak and mismatched reset attempts without changing the password', function (string $case) {
    $this->freezeTime();
    $user = User::factory()->create();
    $oldHash = $user->password;
    $token = Password::broker('users')->createToken($user);
    $password = $case === 'weak' ? 'short' : 'New-Secure123!';
    if ($case === 'expired') {
        $this->travel(61)->minutes();
    }
    $payload = [
        'email' => $user->email,
        'token' => $case === 'invalid' ? 'invalid' : $token,
        'password' => $password,
        'password_confirmation' => $case === 'mismatch' ? 'Other-Secure123!' : $password,
    ];

    $this->postJson(route('users.password.update'), $payload)->assertUnprocessable();

    expect($user->fresh()->password)->toBe($oldHash);
})->with(['invalid', 'expired', 'weak', 'mismatch']);

test('saves profile fields through shared file storage and resets verification when email changes', function () {
    Storage::fake('public');
    $service = app(UserService::class);
    $user = $service->save([
        'name' => 'Ada', 'surname' => 'Yılmaz', 'email' => 'ada@example.com',
        'password' => 'Strong-Password123!', 'username' => null, 'is_active' => true,
        'profile_image' => UploadedFile::fake()->image('profile.png'),
        'email_verified_at' => now(), 'last_login_ip' => '203.0.113.2',
    ]);
    expect(Hash::check('Strong-Password123!', $user->password))->toBeTrue();
    expect($user->email_verified_at)->toBeNull();
    expect($user->last_login_ip)->toBeNull();
    Storage::disk('public')->assertExists($user->profile_image);
    $oldPath = $user->profile_image;
    $user->markEmailAsVerified();

    $updated = $service->save(['name' => 'Ada', 'surname' => 'Yılmaz', 'email' => 'new@example.com', 'is_active' => true, 'remove_profile_image' => true], $user);

    expect($updated->email_verified_at)->toBeNull();
    expect($updated->profile_image)->toBeNull();
    expect(Hash::check('Strong-Password123!', $updated->password))->toBeTrue();
    Storage::disk('public')->assertMissing($oldPath);
});

test('rejects duplicate email username and unsafe profile uploads', function (string $case) {
    $existing = User::factory()->create(['username' => 'taken']);
    $data = ['name' => 'Ada', 'surname' => 'Yılmaz', 'email' => 'new@example.com', 'password' => 'Strong-Password123!', 'is_active' => true];
    $data = [...$data, ...match ($case) {
        'email' => ['email' => $existing->email],
        'username' => ['username' => 'taken'],
        'profile_image' => ['profile_image' => UploadedFile::fake()->create('script.php', 1, 'text/x-php')],
    }];

    expect(fn () => app(UserService::class)->save($data))->toThrow(ValidationException::class);

    $this->assertDatabaseCount('users', 1);
})->with(['email', 'username', 'profile_image']);

test('soft deletes users while preserving login history', function () {
    $user = User::factory()->create();
    $login = $user->logins()->create(['ip_address' => '203.0.113.7', 'logged_in_at' => now()]);

    app(UserService::class)->delete($user);

    $this->assertSoftDeleted($user);
    $this->assertModelExists($login);
    expect($login->fresh()->user->id)->toBe($user->id);
    expect(Auth::guard('web')->getProvider()->retrieveById($user->id))->toBeNull();
});

test('disabled module returns no current user and blocks login before writes', function () {
    $user = User::factory()->create();
    $this->actingAs($user, 'web');
    $module = Mockery::mock(Nwidart\Modules\Module::class);
    $module->shouldReceive('isEnabled')->andReturnFalse();
    Module::partialMock()->shouldReceive('find')->with('Users')->andReturn($module);

    expect(app(UsersFrontendService::class)->current())->toBeNull();
    $this->postJson(route('users.login'), ['email' => $user->email, 'password' => 'password'])->assertNotFound();

    $this->assertDatabaseCount('user_logins', 0);
});

test('preserves each successful login while updating the latest metadata', function () {
    $user = User::factory()->create();
    $payload = ['email' => $user->email, 'password' => 'password'];
    $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.1'])->postJson(route('users.login'), $payload)->assertOk();
    $this->postJson(route('users.logout'))->assertOk();

    $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.2'])->postJson(route('users.login'), $payload)->assertOk();

    expect($user->logins()->pluck('ip_address')->all())->toBe(['203.0.113.2', '203.0.113.1']);
    expect($user->fresh()->last_login_ip)->toBe('203.0.113.2');
});

test('rejects reset tokens issued before account deactivation or deletion', function (string $case) {
    $user = User::factory()->create();
    $token = Password::broker('users')->createToken($user);
    $oldHash = $user->password;
    if ($case === 'inactive') {
        $user->update(['is_active' => false]);
    } else {
        $user->delete();
    }

    $this->postJson(route('users.password.update'), [
        'email' => $user->email, 'token' => $token,
        'password' => 'New-Secure123!', 'password_confirmation' => 'New-Secure123!',
    ])->assertUnprocessable();

    expect(User::withTrashed()->findOrFail($user->id)->password)->toBe($oldHash);
})->with(['inactive', 'deleted']);

test('validates missing login credentials before recording history', function () {
    $this->postJson(route('users.login'), [])->assertUnprocessable()->assertJsonValidationErrors(['email', 'password']);

    $this->assertDatabaseCount('user_logins', 0);
});
