<?php

namespace Modules\Menus\Services;

use Modules\Menus\Interfaces\MenuSource;
use Nwidart\Modules\Facades\Module;

class MenuSources
{
    /** @return array<string, MenuSource> */
    public function all(): array
    {
        $sources = [];

        foreach (Module::allEnabled() as $module) {
            foreach (config(strtolower($module->getName()).'.navigation_sources', []) as $key => $class) {
                if (! is_string($key) || ! is_string($class) || ! is_a($class, MenuSource::class, true)) {
                    continue;
                }

                $sources[$key] = app($class);
            }
        }

        return $sources;
    }

    public function get(string $key): ?MenuSource
    {
        return $this->all()[$key] ?? null;
    }
}
