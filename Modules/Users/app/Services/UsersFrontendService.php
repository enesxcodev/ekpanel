<?php

namespace Modules\Users\Services;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;
use Modules\Users\Models\User;
use Nwidart\Modules\Facades\Module;

class UsersFrontendService
{
    public function current(): ?User
    {
        if (! $this->available()) {
            return null;
        }
        $user = Auth::guard('web')->user();

        return $user instanceof User
            ? User::query()->where('is_active', true)->find($user->id)
            : null;
    }

    public function login(Request $request): User
    {
        $this->ensureAvailable();
        $data = Validator::make($request->only(['email', 'password', 'remember']), [
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string'],
            'remember' => ['sometimes', 'boolean'],
        ])->validate();
        $key = 'users:login:'.hash('sha256', Str::lower($data['email']).'|'.$request->ip());
        $this->ensureNotThrottled($key, 5);
        if (! Auth::guard('web')->attempt(['email' => $data['email'], 'password' => $data['password']], (bool) ($data['remember'] ?? false))) {
            RateLimiter::hit($key, 60);
            throw ValidationException::withMessages(['email' => 'E-posta veya şifre hatalı.']);
        }
        RateLimiter::clear($key);
        $request->session()->regenerate();

        return $this->authenticated();
    }

    public function logout(Request $request): void
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    public function sendEmailVerification(): bool
    {
        $user = $this->authenticated();
        if ($user->hasVerifiedEmail()) {
            return false;
        }
        $key = 'users:verify:'.$user->id;
        $this->ensureNotThrottled($key, 1);
        $user->sendEmailVerificationNotification();
        RateLimiter::hit($key, 60);

        return true;
    }

    public function verifyEmail(Request $request): bool
    {
        $user = $this->authenticated();
        abort_unless($request->hasValidSignature()
            && hash_equals((string) $user->id, (string) $request->route('id'))
            && hash_equals(sha1($user->getEmailForVerification()), (string) $request->route('hash')), 403);
        if ($user->hasVerifiedEmail()) {
            return false;
        }
        $user->markEmailAsVerified();
        event(new Verified($user));

        return true;
    }

    public function sendPasswordResetLink(string $email, Request $request): string
    {
        $this->ensureAvailable();
        Validator::make(['email' => $email], ['email' => ['required', 'email', 'max:255']])->validate();
        $key = 'users:password:'.hash('sha256', $request->ip().'|'.Str::lower($email));
        $this->ensureNotThrottled($key, 3);
        RateLimiter::hit($key, 60);
        Password::broker('users')->sendResetLink(['email' => $email]);

        return Password::ResetLinkSent;
    }

    /** @param array<string, mixed> $credentials */
    public function resetPassword(array $credentials): string
    {
        $this->ensureAvailable();
        $data = Validator::make($credentials, [
            'email' => ['required', 'email', 'max:255'],
            'token' => ['required', 'string'],
            'password' => ['required', 'confirmed', 'string', PasswordRule::min(12)->mixedCase()->numbers()->symbols()],
        ])->validate();

        return Password::broker('users')->reset($data, function (User $user, string $password): void {
            $user->forceFill(['password' => $password, 'remember_token' => Str::random(60)])->save();
            event(new PasswordReset($user));
        });
    }

    private function authenticated(): User
    {
        $user = $this->current();
        if ($user === null) {
            throw new AuthenticationException(guards: ['web']);
        }

        return $user;
    }

    private function available(): bool
    {
        return Module::find('Users')?->isEnabled() && Schema::hasTable('user_logins');
    }

    private function ensureAvailable(): void
    {
        abort_unless($this->available(), 404);
    }

    private function ensureNotThrottled(string $key, int $attempts): void
    {
        if (RateLimiter::tooManyAttempts($key, $attempts)) {
            throw ValidationException::withMessages(['email' => 'Çok fazla deneme. Lütfen daha sonra tekrar deneyin.']);
        }
    }
}
