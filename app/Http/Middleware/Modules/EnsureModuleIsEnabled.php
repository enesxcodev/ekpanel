<?php

namespace App\Http\Middleware\Modules;

use Closure;
use Illuminate\Http\Request;
use Nwidart\Modules\Facades\Module;
use Symfony\Component\HttpFoundation\Response;

class EnsureModuleIsEnabled
{
    public function handle(Request $request, Closure $next, string $name): Response
    {
        $module = Module::find($name);
        abort_unless($module !== null && $module->isEnabled(), 404);

        return $next($request);
    }
}
