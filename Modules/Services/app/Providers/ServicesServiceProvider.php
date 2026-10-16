<?php

namespace Modules\Services\Providers;

use Nwidart\Modules\Support\ModuleServiceProvider;

class ServicesServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Services';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'services';

    /**
     * Provider classes to register.
     *
     * @var string[]
     */
    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];
}
