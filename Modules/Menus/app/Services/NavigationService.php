<?php

namespace Modules\Menus\Services;

use Illuminate\Routing\Exceptions\UrlGenerationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use InvalidArgumentException;
use Modules\Menus\Models\Menu;
use Modules\Menus\Models\MenuItem;
use Nwidart\Modules\Facades\Module;

class NavigationService
{
    public function __construct(private MenuSources $sources) {}

    /** @return list<array{label: string, url: ?string, new_tab: bool, children: array}> */
    public function tree(string $menuKey): array
    {
        if (! Module::find('Menus')?->isEnabled()) {
            return [];
        }

        $menu = Menu::query()->where('key', $menuKey)->where('is_active', true)->first();

        if ($menu === null) {
            return [];
        }

        $items = $menu->items()->orderBy('sort_order')->orderBy('id')->get();

        return $this->branches($items->groupBy(fn (MenuItem $item): string => (string) ($item->parent_id ?? 0)));
    }

    /** @return array{url: ?string, error: ?string} */
    public function target(MenuItem $item): array
    {
        if ($item->target_type === 'group') {
            return ['url' => null, 'error' => null];
        }

        if ($item->target_type === 'content') {
            $source = $this->sources->get((string) $item->source);
            $target = $source?->find((string) $item->source_id);

            if ($target === null) {
                return ['url' => null, 'error' => 'İçerik kaynağı veya kayıt artık kullanılamıyor.'];
            }

            if ($target['error'] !== null) {
                return ['url' => null, 'error' => $target['error']];
            }

            if ($target['url'] === null) {
                return ['url' => null, 'error' => null];
            }

            $url = $this->safeUrl($target['url']);

            return ['url' => $url, 'error' => $url === null ? 'İçeriğin güvenli bir bağlantısı bulunamadı.' : null];
        }

        if ($item->target_type === 'route') {
            if (! $this->isPublicRoute((string) $item->route_name)) {
                return ['url' => null, 'error' => 'Yalnız etkin modüllerin herkese açık GET route bağlantıları kullanılabilir.'];
            }

            try {
                $url = $this->safeUrl(route($item->route_name, $item->route_parameters ?? []));
            } catch (UrlGenerationException|InvalidArgumentException) {
                return ['url' => null, 'error' => 'Route parametreleri bağlantı oluşturmak için yeterli değil.'];
            }

            return ['url' => $url, 'error' => $url === null ? 'Route güvenli bir bağlantı üretmedi.' : null];
        }

        if ($item->target_type === 'anchor') {
            $anchor = (string) $item->anchor;

            if (! preg_match('/^#?[A-Za-z][A-Za-z0-9_:.-]*$/', $anchor)) {
                return ['url' => null, 'error' => 'Bölüm kimliği harfle başlamalı ve yalnız harf, rakam, tire, alt çizgi, nokta veya iki nokta içermelidir.'];
            }

            $base = $item->url === null || $item->url === '' ? '' : $this->safeUrl($item->url);

            if ($base === null || preg_match('/^(mailto|tel):/i', $base)) {
                return ['url' => null, 'error' => 'Bölüm bağlantısı için geçerli bir sayfa adresi girin.'];
            }

            return ['url' => explode('#', $base, 2)[0].'#'.ltrim($anchor, '#'), 'error' => null];
        }

        if ($item->target_type === 'url') {
            $url = $this->safeUrl($item->url);

            return ['url' => $url, 'error' => $url === null ? 'Geçerli bir http, https, site içi, e-posta veya telefon bağlantısı girin.' : null];
        }

        return ['url' => null, 'error' => 'Geçersiz bağlantı türü.'];
    }

    public function safeUrl(?string $url): ?string
    {
        if ($url === null || $url === '' || preg_match('/[\x00-\x20\x7f\\\\]/', $url)) {
            return null;
        }

        $decoded = rawurldecode($url);

        if (preg_match('/[\x00-\x1f\x7f\\\\]/', $decoded)) {
            return null;
        }

        if (str_starts_with($url, '/') && ! str_starts_with($decoded, '//')) {
            return $url;
        }

        if (preg_match('/^https?:\/\//i', $url) && filter_var($url, FILTER_VALIDATE_URL) !== false) {
            return $url;
        }

        if (preg_match('/^mailto:([^?]+)(?:\?[^\s]*)?$/i', $url, $matches) && filter_var($matches[1], FILTER_VALIDATE_EMAIL) !== false) {
            return $url;
        }

        return preg_match('/^tel:\+?[0-9().-]+$/i', $url) ? $url : null;
    }

    public function isPublicRoute(string $name): bool
    {
        $route = Route::getRoutes()->getByName($name);
        $adminPrefix = trim((string) config('app.route_files.admin_prefix', 'admin'), '/');

        if ($route === null || ! in_array('GET', $route->methods(), true)
            || str_starts_with($name, 'admin.')
            || ($adminPrefix !== '' && ($route->uri() === $adminPrefix || str_starts_with($route->uri(), $adminPrefix.'/')))) {
            return false;
        }

        foreach ($route->gatherMiddleware() as $middleware) {
            if (! is_string($middleware) || preg_match('/^(auth|auth\.basic|auth\.session|can|verified|password\.confirm|signed|role|permission|role_or_permission)(:|$)/', $middleware)
                || str_contains($middleware, '\\Authenticate') || str_contains($middleware, '\\Authorize')
                || str_contains($middleware, '\\EnsureAdminIsActive') || str_contains($middleware, '\\ValidateSignature')
                || str_contains($middleware, '\\RoleMiddleware') || str_contains($middleware, '\\PermissionMiddleware')
                || str_contains($middleware, '\\RoleOrPermissionMiddleware')) {
                return false;
            }

            if (str_starts_with($middleware, 'module.enabled:') && ! Module::find(substr($middleware, 15))?->isEnabled()) {
                return false;
            }
        }

        return true;
    }

    /** @return list<array{value: string, label: string}> */
    public function routeOptions(): array
    {
        $options = [];

        foreach (Route::getRoutes() as $route) {
            $name = $route->getName();

            if ($name !== null && $this->isPublicRoute($name)) {
                $options[] = ['value' => $name, 'label' => $name.' — /'.$route->uri()];
            }
        }

        return $options;
    }

    /**
     * @param  Collection<string, Collection<int, MenuItem>>  $byParent
     * @param  list<int>  $ancestors
     * @return list<array{label: string, url: ?string, new_tab: bool, children: array}>
     */
    private function branches(Collection $byParent, int $parentId = 0, array $ancestors = []): array
    {
        $nodes = [];

        foreach ($byParent->get((string) $parentId, collect()) as $item) {
            if (! $item->is_active || in_array($item->id, $ancestors, true)) {
                continue;
            }

            $target = $this->target($item);

            if ($target['error'] !== null) {
                continue;
            }

            $children = $this->branches($byParent, $item->id, [...$ancestors, $item->id]);

            if ($item->target_type === 'content' && $item->dynamic_children) {
                $source = $this->sources->get((string) $item->source);

                if ($source?->supportsChildren()) {
                    $children = [...$children, ...$this->safeChildren($source->children((string) $item->source_id))];
                }
            }

            $nodes[] = ['label' => $item->label, 'url' => $target['url'], 'new_tab' => (bool) $item->new_tab, 'children' => $children];
        }

        return $nodes;
    }

    /**
     * @param  list<array{label: string, url: string, new_tab: bool, children: array}>  $children
     * @return list<array{label: string, url: string, new_tab: bool, children: array}>
     */
    private function safeChildren(array $children): array
    {
        $nodes = [];

        foreach ($children as $child) {
            $url = $this->safeUrl($child['url']);

            if ($url !== null) {
                $nodes[] = [...$child, 'url' => $url, 'children' => $this->safeChildren($child['children'])];
            }
        }

        return $nodes;
    }
}
