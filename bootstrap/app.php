<?php

use App\Http\Middleware\Modules\EnsureModuleIsEnabled;
use App\Http\Middleware\Settings\ApplyMailSettings;
use App\Http\Middleware\TrackPublicPageView;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function (): void {
            Route::middleware('web')->group(base_path(config('app.route_files.admin')));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trimStrings(except: ['settings.*']);
        $middleware->convertEmptyStringsToNull(except: [
            fn (Request $request): bool => $request->is(trim((string) config('app.route_files.admin_prefix'), '/').'/settings/sections/*/values'),
        ]);
        $middleware->web(append: [ApplyMailSettings::class, TrackPublicPageView::class]);
        $trustedProxies = array_values(array_filter(array_map(
            'trim',
            explode(',', (string) env('TRUSTED_PROXIES', '')),
        )));
        if ($trustedProxies !== []) {
            $middleware->trustProxies(at: $trustedProxies, headers: Request::HEADER_X_FORWARDED_FOR);
        }
        $middleware->alias(['module.enabled' => EnsureModuleIsEnabled::class]);
        $middleware->redirectGuestsTo(fn (Request $request): string => route('admin.login'));
        $middleware->redirectUsersTo(fn (Request $request): string => route('admin.dashboard'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
