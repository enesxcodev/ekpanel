<?php

namespace Modules\Menus\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\View\View as ViewInstance;
use Modules\Menus\Services\NavigationService;
use Nwidart\Modules\Facades\Module;
use Nwidart\Modules\Support\ModuleServiceProvider;

class MenusServiceProvider extends ModuleServiceProvider
{
    protected string $name = 'Menus';

    protected string $nameLower = 'menus';

    /** @var list<class-string> */
    protected array $providers = [RouteServiceProvider::class];

    /**
     * Preserve menus.menus; the module loader collapses duplicate name segments.
     */
    protected function registerConfig(): void
    {
        $configPath = module_path($this->name, 'config/config.php');

        $this->mergeConfigFrom($configPath, 'menus');
        $this->mergeConfigFrom(module_path($this->name, 'config/menus.php'), 'menus.menus');
        $this->publishes([$configPath => config_path('menus.php')], 'config');
    }

    public function boot(): void
    {
        parent::boot();

        View::composer(['welcome', 'pages::pages.show'], function (ViewInstance $view): void {
            $items = Module::isEnabled('Menus') && Schema::hasTable('menus')
                ? app(NavigationService::class)->tree((string) config('menus.main_menu', 'main'))
                : [];
            $view->with('navigationItems', $items);
        });
    }
}
