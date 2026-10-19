<?php

namespace App\Services\Frontend;

use Illuminate\Contracts\Container\Container;

class ModuleFrontendServiceResolver
{
    public function __construct(private Container $container) {}

    /**
     * @template T of object
     *
     * @param  class-string<T>  $serviceClass
     * @return T|null
     */
    public function resolve(string $serviceClass): ?object
    {
        return class_exists($serviceClass) ? $this->container->make($serviceClass) : null;
    }
}
