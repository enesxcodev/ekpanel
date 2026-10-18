<?php

namespace App\Services\Admin\Modules;

use Nwidart\Modules\Facades\Module;
use RuntimeException;

class ModuleCompatibility
{
    public function matches(string $version, string $constraint): bool
    {
        foreach (explode('||', $constraint) as $alternative) {
            $matches = true;
            foreach (preg_split('/[ ,]+/', trim($alternative)) as $part) {
                if ($part === '*') {
                    continue;
                }
                if (! preg_match('/^(\\^|~|>=|<=|>|<|=)?(\\d+)(?:\\.(\\d+))?(?:\\.(\\d+))?$/D', $part, $match)) {
                    throw new RuntimeException('Desteklenmeyen sürüm koşulu: '.$constraint);
                }
                $operator = $match[1] ?: '=';
                $major = (int) $match[2];
                $minor = (int) ($match[3] ?? 0);
                $patch = (int) ($match[4] ?? 0);
                $minimum = "$major.$minor.$patch";
                if ($operator === '^' || $operator === '~') {
                    $maximum = $operator === '~' && isset($match[4]) ? $major.'.'.($minor + 1).'.0'
                        : ($operator === '^' && $major === 0 && isset($match[3])
                            ? ($minor === 0 && isset($match[4]) ? '0.0.'.($patch + 1) : '0.'.($minor + 1).'.0')
                            : ($major + 1).'.0.0');
                    $matches = $matches && version_compare($version, $minimum, '>=') && version_compare($version, $maximum, '<');
                } else {
                    $matches = $matches && version_compare($version, $minimum, $operator);
                }
            }
            if ($matches) {
                return true;
            }
        }

        return false;
    }

    /** @param array<string, mixed> $requires */
    public function check(array $requires): void
    {
        if (! $this->matches(PHP_VERSION, $requires['php']) || ! $this->matches(app()->version(), $requires['laravel'])) {
            throw new RuntimeException('Modülün PHP veya Laravel sürümü bu kurulumla uyumlu değil.');
        }
        foreach ($requires['modules'] ?? [] as $name => $constraint) {
            $module = Module::find($name);
            if (! $module || ! $module->isEnabled() || ! is_string($module->get('version')) || ! $this->matches($module->get('version'), $constraint)) {
                throw new RuntimeException('Gerekli modül etkin ve uyumlu olmalıdır: '.$name);
            }
        }
    }
}
