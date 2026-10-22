<?php

namespace Modules\Konum\Providers;

use Modules\Konum\Console\ImportKonum;
use Modules\Konum\Console\VerifyKonum;
use Nwidart\Modules\Support\ModuleServiceProvider;

class KonumServiceProvider extends ModuleServiceProvider
{
    protected string $name = 'Konum';

    protected string $nameLower = 'konum';

    protected array $commands = [ImportKonum::class, VerifyKonum::class];

    protected array $providers = [RouteServiceProvider::class];
}
