<?php

namespace App\Providers;

use App\Enums\AdminType\AdminTypeEnum;
use App\Models\Admin\Admin;
use App\Models\Settings\SettingSection;
use App\Policies\Admin\AdminPolicy;
use App\Policies\Settings\SettingSectionPolicy;
use App\Services\Admin\Modules\ModulePanelService;
use App\Services\Admin\NotificationService;
use App\Services\Fields\ModuleFeatures;
use App\Services\Forms\FormContext;
use App\Services\Settings\SeoMetadata;
use App\Services\Settings\SettingsMailConfigurator;
use App\Services\Settings\SettingsService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\RateLimiter;
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
        $this->app->singleton(FormContext::class);
        $this->app->singleton(SettingsMailConfigurator::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(ModulePanelService $modulePanel, SettingsService $settings, NotificationService $notifications): void
    {
        ViewFacade::composer(['frontend.themes.*.layouts.app', 'pages::pages.show', 'blog::public.index', 'blog::public.show'], function (View $view) use ($settings): void {
            $data = $view->getData();
            if ($view->name() === 'blog::public.index') {
                $data['seoTitle'] = 'Blog · '.($settings->get('site.title') ?: config('app.name'));
            }
            if (! isset($data['seoMetadata'])) {
                $view->with('seoMetadata', $this->app->make(SeoMetadata::class)->build($data));
            }
        });
        ViewFacade::composer('components.admin.topbar', function (View $view) use ($notifications): void {
            $admin = auth('admin')->user();
            $view->with($admin && $admin->status ? $notifications->header($admin) : ['notificationItems' => collect(), 'unreadNotificationCount' => 0]);
        });
        ViewFacade::composer('components.admin.sidebar', function (View $view) use ($modulePanel): void {
            $admin = auth('admin')->user();
            $view->with('moduleMenus', $admin ? $modulePanel->menus($admin) : []);
        });
        ViewFacade::composer(['admin.auth.login', 'components.admin.layout'], function (View $view) use ($settings): void {
            $view->with('companyName', (string) $settings->get('site.company_name', 'EkAgent'));
        });

        RateLimiter::for('form-submissions', fn (Request $request): Limit => Limit::perMinute(6)->by($request->path().':'.$request->ip()));

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
