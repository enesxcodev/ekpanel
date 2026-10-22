<?php

namespace App\Services\Admin\Dashboard;

use Illuminate\Support\Facades\Log;
use Nwidart\Modules\Facades\Module;
use ReflectionClass;
use RuntimeException;
use Throwable;

class DashboardWidgetRegistry
{
    /** @return array<string, array{definition: array<string, mixed>, widget: DashboardWidget}> */
    public function all(): array
    {
        $widgets = [];
        $duplicates = [];

        foreach (Module::allEnabled() as $module) {
            $panel = $module->get('panel', []);
            $source = $panel['widgets'] ?? null;
            if ($source === null) {
                continue;
            }

            try {
                $root = realpath($module->getPath());
                $path = is_string($source) ? realpath($module->getPath().'/'.$source) : false;
                if (! $root || ! $path || ! $this->within($path, $root) || pathinfo($path, PATHINFO_EXTENSION) !== 'php') {
                    throw new RuntimeException('Invalid widget source.');
                }
                $classes = require $path;
                if (! is_array($classes) || ! array_is_list($classes) || count($classes) > 200) {
                    throw new RuntimeException('Invalid widget class list.');
                }
            } catch (Throwable $exception) {
                Log::warning('Dashboard widget source rejected.', ['module' => $module->getName(), 'error' => $exception::class]);

                continue;
            }

            foreach ($classes as $class) {
                try {
                    $namespace = 'Modules\\'.$module->getName().'\\Dashboard\\Widgets\\';
                    if (! is_string($class) || ! str_starts_with($class, $namespace) || ! is_subclass_of($class, DashboardWidget::class)) {
                        throw new RuntimeException('Invalid widget contract.');
                    }
                    $reflection = new ReflectionClass($class);
                    if (! $this->within((string) $reflection->getFileName(), $root)) {
                        throw new RuntimeException('Widget class outside module.');
                    }
                    $widget = app($class);
                    $definition = $widget->definition();
                    $id = $definition['id'] ?? '';
                    $alias = (string) $module->get('alias', strtolower($module->getName()));
                    $permissions = $definition['permissions'] ?? [];
                    if (! is_string($id) || strlen($id) > 120 || ! preg_match('/^[a-z0-9]+(?:[.][a-z0-9_-]+)+$/', $id)
                        || ! str_starts_with($id, $alias.'.')
                        || ! is_string($definition['title'] ?? null) || trim($definition['title']) === ''
                        || ! is_string($definition['icon'] ?? null)
                        || ! is_bool($definition['default'] ?? null) || ! is_int($definition['order'] ?? null)
                        || ($definition['type'] ?? null) !== 'stat'
                        || ! is_array($permissions) || ! array_is_list($permissions) || $permissions === []) {
                        throw new RuntimeException('Invalid widget metadata.');
                    }
                    foreach ($permissions as $permission) {
                        if (! is_string($permission) || ! str_ends_with($permission, '.view') || ! in_array($permission, $panel['permissions'] ?? [], true)) {
                            throw new RuntimeException('Undeclared widget permission.');
                        }
                    }
                    if (isset($widgets[$id]) || isset($duplicates[$id])) {
                        unset($widgets[$id]);
                        $duplicates[$id] = true;
                        throw new RuntimeException('Duplicate widget identity.');
                    }
                    $menus = config($alias.'.menus', []);
                    $firstMenu = is_array($menus) ? reset($menus) : false;
                    $definition['module'] = $module->getName();
                    $definition['group'] = is_array($firstMenu) ? ($firstMenu['label'] ?? $module->getName()) : $module->getName();
                    $widgets[$id] = ['definition' => $definition, 'widget' => $widget];
                } catch (Throwable $exception) {
                    Log::warning('Dashboard widget registration rejected.', ['module' => $module->getName(), 'error' => $exception::class]);
                }
            }
        }

        uasort($widgets, fn (array $first, array $second): int => [$first['definition']['order'], $first['definition']['id']] <=> [$second['definition']['order'], $second['definition']['id']]);

        return $widgets;
    }

    private function within(string $path, string $root): bool
    {
        return str_starts_with(str_replace('\\', '/', $path), rtrim(str_replace('\\', '/', $root), '/').'/');
    }
}
