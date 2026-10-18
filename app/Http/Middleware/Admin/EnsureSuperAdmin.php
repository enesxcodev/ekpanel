<?php

namespace App\Http\Middleware\Admin;

use App\Enums\AdminType\AdminTypeEnum;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user('admin')?->status
            && $request->user('admin')?->type === AdminTypeEnum::SuperAdmin, 403);

        return $next($request);
    }
}
