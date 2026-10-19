<?php

namespace Modules\Users\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Route::middleware('web')->group(module_path('Users', 'routes/admin.php'));
        Route::middleware(['web', 'module.enabled:Users'])
            ->prefix('users')->name('users.')
            ->group(module_path('Users', 'routes/web.php'));
    }
}
