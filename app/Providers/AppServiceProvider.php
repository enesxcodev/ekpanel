<?php

namespace App\Providers;

use App\Enums\AdminType\AdminTypeEnum;
use App\Models\Admin\Admin;
use App\Models\Settings\SettingSection;
use App\Policies\Admin\AdminPolicy;
use App\Policies\Settings\SettingSectionPolicy;
use App\Services\Admin\Modules\ModulePanelService;
use App\Services\Fields\ModuleFeatures;
use App\Services\Settings\SettingsMailConfigurator;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\View as ViewFacade;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->scoped(ModuleFeatures::class);
        $this->app->singleton(SettingsMailConfigurator::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(ModulePanelService $modulePanel): void
    {
        ViewFacade::composer('components.admin.sidebar', function (View $view) use ($modulePanel): void {
            $admin = auth('admin')->user();
            $view->with('moduleMenus', $admin ? $modulePanel->menus($admin) : []);
        });

        Gate::policy(Admin::class, AdminPolicy::class);
        Gate::policy(SettingSection::class, SettingSectionPolicy::class);
        Gate::before(function (Authenticatable $user, string $ability): ?bool {
            return $user instanceof Admin && $user->status && $user->type === AdminTypeEnum::SuperAdmin ? true : null;
        });
        Gate::define('manage-admin-roles', fn (Admin $admin): bool => $admin->type === AdminTypeEnum::SuperAdmin);
        Gate::define('manage-setting-definitions', fn (Admin $admin): bool => $admin->type === AdminTypeEnum::SuperAdmin);

        Queue::before(function (): void {
            $this->app->make(SettingsMailConfigurator::class)->apply();
        });
    }
}
