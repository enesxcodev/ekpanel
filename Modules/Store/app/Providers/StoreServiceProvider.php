<?php

namespace Modules\Store\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Modules\Store\Services\StoreOrderService;
use Nwidart\Modules\Support\ModuleServiceProvider;

class StoreServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Store';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'store';

    protected function registerTranslations(): void
    {
        $this->loadTranslationsFrom(module_path($this->name, 'resources/lang'), $this->nameLower);
    }

    /**
     * Command classes to register.
     *
     * @var string[]
     */
    // protected array $commands = [];

    /**
     * Provider classes to register.
     *
     * @var string[]
     */
    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    /**
     * Define module schedules.
     */
    protected function configureSchedules(Schedule $schedule): void
    {
        $schedule->call(fn (): int => app(StoreOrderService::class)->expire())
            ->name('store:expire-reservations')->everyMinute()->withoutOverlapping();
    }
}
