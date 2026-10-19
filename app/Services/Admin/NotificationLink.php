<?php

namespace App\Services\Admin;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class NotificationLink
{
    public function safePath(?string $link): ?string
    {
        if ($link === null || $link === '' || preg_match('/[\x00-\x20\x7f\\\\]/', $link)) {
            return null;
        }

        $parts = parse_url($link);
        if ($parts === false || isset($parts['user']) || isset($parts['pass'])) {
            return null;
        }
        if (isset($parts['host']) || isset($parts['scheme'])) {
            $origin = parse_url((string) config('app.url'));
            if (! isset($parts['scheme'], $parts['host']) || ! in_array(strtolower($parts['scheme']), ['http', 'https'], true)
                || strtolower($parts['scheme']) !== strtolower($origin['scheme'] ?? '')
                || strtolower($parts['host']) !== strtolower($origin['host'] ?? '')
                || ($parts['port'] ?? ($parts['scheme'] === 'https' ? 443 : 80)) !== ($origin['port'] ?? (($origin['scheme'] ?? '') === 'https' ? 443 : 80))) {
                return null;
            }
        }

        $path = $parts['path'] ?? '/';
        $decoded = rawurldecode($path);
        if (! str_starts_with($path, '/') || str_starts_with($decoded, '//')
            || preg_match('/[\x00-\x20\x7f\\\\%]/', $decoded)
            || preg_match('~(?:^|/)\.{1,2}(?:/|$)~', $decoded)) {
            return null;
        }

        return $path.(isset($parts['query']) ? '?'.$parts['query'] : '').(isset($parts['fragment']) ? '#'.$parts['fragment'] : '');
    }

    public function destination(?string $link): ?string
    {
        $path = $this->safePath($link);
        if ($path === null) {
            return null;
        }

        try {
            $route = clone Route::getRoutes()->match(Request::create(rtrim((string) config('app.url'), '/').$path, 'GET'));
            Route::substituteBindings($route);
            Route::substituteImplicitBindings($route);
        } catch (NotFoundHttpException|MethodNotAllowedHttpException|ModelNotFoundException|BindingResolutionException|\ReflectionException) {
            return null;
        } catch (QueryException $exception) {
            report($exception);

            return null;
        }

        return $route->isFallback ? null : $path;
    }
}
