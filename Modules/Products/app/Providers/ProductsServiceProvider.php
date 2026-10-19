<?php

namespace Modules\Products\Providers;

use App\Models\Forms\SiteForm;
use App\Services\Forms\FormContext;
use Modules\Products\Services\ProductsFrontendService;
use Nwidart\Modules\Support\ModuleServiceProvider;

class ProductsServiceProvider extends ModuleServiceProvider
{
    public function boot(): void
    {
        parent::boot();
        app(FormContext::class)->register('product', fn (int $id, SiteForm $form): ?array => app(ProductsFrontendService::class)->formContext($id, $form));
    }

    /**
     * The name of the module.
     */
    protected string $name = 'Products';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'products';

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
