<?php

namespace Modules\Users\Providers;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Auth\Events\Login;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Modules\Users\Models\User;
use Nwidart\Modules\Support\ModuleServiceProvider;

class UsersServiceProvider extends ModuleServiceProvider
{
    protected string $name = 'Users';

    protected string $nameLower = 'users';

    protected array $providers = [RouteServiceProvider::class];

    public function register(): void
    {
        parent::register();
        config(['auth.providers.users' => ['driver' => 'active-users', 'model' => User::class]]);
        Auth::provider('active-users', function (Application $app, array $config): EloquentUserProvider {
            return (new EloquentUserProvider($app['hash'], $config['model']))
                ->withQuery(fn (Builder $query): Builder => $query->where('is_active', true));
        });
    }

    public function boot(): void
    {
        parent::boot();
        Event::listen(Login::class, function (Login $event): void {
            if ($event->guard !== 'web' || ! $event->user instanceof User) {
                return;
            }
            DB::transaction(function () use ($event): void {
                $attributes = [
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'logged_in_at' => now(),
                ];
                $event->user->logins()->create($attributes);
                $event->user->forceFill([
                    'last_login_ip' => $attributes['ip_address'],
                    'last_login_user_agent' => $attributes['user_agent'],
                    'last_login_at' => $attributes['logged_in_at'],
                ])->save();
            });
        });
    }
}
