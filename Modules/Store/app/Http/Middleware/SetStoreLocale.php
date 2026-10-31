<?php

namespace Modules\Store\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Store\Services\StoreLanguageService;
use Symfony\Component\HttpFoundation\Response;

class SetStoreLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $language = app(StoreLanguageService::class)->forPublic((string) ($request->route('lang') ?? $request->query('lang', StoreLanguageService::PrimaryCode)));
        $request->attributes->set('store_language_id', $language->id);
        $request->attributes->set('store_language', $language->code);
        app()->setLocale($language->code);

        return $next($request);
    }
}
