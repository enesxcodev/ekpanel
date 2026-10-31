<?php

namespace Modules\Menus\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Settings\Language;
use App\Services\NotifyService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Menus\DataTables\MenusDataTable;
use Modules\Menus\Http\Requests\ReorderMenuItemsRequest;
use Modules\Menus\Http\Requests\SaveMenuRequest;
use Modules\Menus\Interfaces\LocalizedMenuSource;
use Modules\Menus\Models\Menu;
use Modules\Menus\Services\MenuService;
use Modules\Menus\Services\MenusLanguageService;
use Modules\Menus\Services\MenuSources;
use Modules\Menus\Services\NavigationService;

class MenusController extends Controller
{
    public function index(Request $request, MenuSources $sources, NavigationService $navigation, MenusLanguageService $languages): View
    {
        $this->authorize('menus.view');
        $activeLanguages = $languages->active();
        $selectedLanguage = $languages->selectedAdmin($request);
        $languageId = $selectedLanguage?->id;
        $sourceOptions = [];
        if ($request->user('admin')->can('menus.update') && $selectedLanguage !== null) {
            foreach ($sources->all() as $key => $source) {
                $sourceOptions[] = [
                    'key' => $key,
                    'label' => $source->label(),
                    'options' => $source instanceof LocalizedMenuSource ? $source->optionsForLanguage($selectedLanguage->code) : $source->options(),
                    'supports_children' => $source->supportsChildren(),
                ];
            }
        }

        $menus = Menu::query()
            ->with(['translations' => fn ($query) => $query->where('lang_id', $languageId)])
            ->withCount(['items as selected_items_count' => fn ($query) => $query->where('lang_id', $languageId)])
            ->orderBy('key')
            ->get()
            ->map(fn (Menu $menu): array => $this->menuSummary($menu, $selectedLanguage))
            ->all();
        $configuration = [
            'menus' => $menus,
            'sources' => $sourceOptions,
            'routes' => $selectedLanguage !== null && $request->user('admin')->can('menus.update') ? $navigation->routeOptions() : [],
            'storeUrl' => route('admin.menus.store', ['lang' => $selectedLanguage?->code]),
            'language' => $selectedLanguage ? ['id' => $selectedLanguage->id, 'code' => $selectedLanguage->code] : null,
            'initialMenu' => $request->integer('menu'),
            'initialItem' => $request->integer('item'),
            'newMenu' => $request->boolean('new'),
            'permissions' => collect(['create', 'update', 'delete'])->mapWithKeys(fn (string $ability): array => [$ability => $request->user('admin')->can('menus.'.$ability)])->all(),
        ];

        return view('menus::workspace', compact('configuration', 'activeLanguages', 'selectedLanguage'));
    }

    public function data(MenusDataTable $dataTable): JsonResponse
    {
        $this->authorize('menus.view');

        return $dataTable->handle();
    }

    public function create(Request $request): RedirectResponse
    {
        $this->authorize('menus.create');

        return redirect()->route('admin.menus.index', ['new' => 1, 'lang' => $request->query('lang', MenusLanguageService::PrimaryCode)]);
    }

    public function store(SaveMenuRequest $request, MenuService $service, NotifyService $notifications): JsonResponse|RedirectResponse
    {
        $data = $request->validated();
        $menu = $service->save($data);
        $language = Language::query()->findOrFail($data['lang_id']);
        $notifications->sendToAdminsForActor(
            $request->user('admin'),
            'Menü eklendi',
            'Bir menü eklendi.',
            route('admin.menus.index', ['menu' => $menu->id, 'lang' => $language->code], absolute: false),
        );
        if ($request->expectsJson()) {
            return response()->json(['menu' => $this->summaryFor($menu, $language), 'message' => 'Menü başarıyla eklendi.'], 201);
        }

        return redirect()->route('admin.menus.index', ['menu' => $menu->id, 'lang' => $language->code])->with('notification', ['type' => 'success', 'message' => 'Menü başarıyla eklendi.']);
    }

    public function edit(Request $request, Menu $menu): RedirectResponse
    {
        $this->authorize('menus.update');

        return redirect()->route('admin.menus.index', ['menu' => $menu->id, 'lang' => $request->query('lang', MenusLanguageService::PrimaryCode)]);
    }

    public function update(SaveMenuRequest $request, Menu $menu, MenuService $service, NotifyService $notifications): JsonResponse|RedirectResponse
    {
        $data = $request->validated();
        $service->save($data, $menu);
        $language = Language::query()->findOrFail($data['lang_id']);
        $notifications->sendToAdminsForActor(
            $request->user('admin'),
            'Menü güncellendi',
            'Bir menü güncellendi.',
            route('admin.menus.index', ['menu' => $menu->id, 'lang' => $language->code], absolute: false),
        );
        if ($request->expectsJson()) {
            return response()->json(['menu' => $this->summaryFor($menu, $language), 'message' => 'Menü başarıyla güncellendi.']);
        }

        return redirect()->route('admin.menus.index', ['menu' => $menu->id, 'lang' => $language->code])->with('notification', ['type' => 'success', 'message' => 'Menü başarıyla güncellendi.']);
    }

    public function destroy(Request $request, Menu $menu, NotifyService $notifications): JsonResponse|RedirectResponse
    {
        $this->authorize('menus.delete');
        $language = app(MenusLanguageService::class)->selectedAdmin($request);
        $menu->delete();
        $notifications->sendToAdminsForActor(
            $request->user('admin'),
            'Menü silindi',
            'Bir menü ve bağlı öğeleri silindi.',
            route('admin.menus.index', ['lang' => $language?->code], absolute: false),
        );
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Menü ve öğeleri başarıyla silindi.']);
        }

        return redirect()->route('admin.menus.index', ['lang' => $language?->code])->with('notification', ['type' => 'success', 'message' => 'Menü ve öğeleri başarıyla silindi.']);
    }

    public function workspace(Request $request, Menu $menu, MenuService $service, MenusLanguageService $languages): JsonResponse
    {
        $this->authorize('menus.view');
        $language = $languages->selectedAdmin($request);
        abort_if($language === null, 404);
        $items = [];
        $append = function (array $nodes) use (&$append, &$items, $language): void {
            foreach ($nodes as $node) {
                $items[] = [...$node['item']->only(['id', 'lang_id', 'parent_id', 'label', 'icon', 'target_type', 'url', 'route_name', 'route_parameters', 'anchor', 'source', 'source_id', 'dynamic_children', 'is_active', 'new_tab', 'sort_order']),
                    'target' => $node['target'],
                    'update_url' => route('admin.menus.items.update', ['menu' => $node['item']->menu_id, 'item' => $node['item']->id, 'lang' => $language->code]),
                    'delete_url' => route('admin.menus.items.destroy', ['menu' => $node['item']->menu_id, 'item' => $node['item']->id, 'lang' => $language->code]),
                ];
                $append($node['children']);
            }
        };
        $append($service->adminTree($menu, $language->id));

        return response()->json([
            'menu' => $this->summaryFor($menu, $language),
            'items' => $items,
            'urls' => [
                'update' => route('admin.menus.update', ['menu' => $menu->id, 'lang' => $language->code]),
                'delete' => route('admin.menus.destroy', ['menu' => $menu->id, 'lang' => $language->code]),
                'add' => route('admin.menus.items.store', ['menu' => $menu->id, 'lang' => $language->code]),
                'reorder' => route('admin.menus.reorder', ['menu' => $menu->id, 'lang' => $language->code]),
            ],
        ]);
    }

    public function reorder(ReorderMenuItemsRequest $request, Menu $menu, MenuService $service): JsonResponse
    {
        $data = $request->validated();
        $service->reorder($menu, $data['items'], (int) $data['lang_id']);

        return response()->json(['message' => 'Menü sıralaması kaydedildi.']);
    }

    /** @return array<string, mixed> */
    private function menuSummary(Menu $menu, ?Language $language): array
    {
        $translation = $menu->translations->first();

        return [...$menu->only(['id', 'key', 'icon', 'is_active']),
            'name' => $translation?->name ?? $menu->key,
            'translation_exists' => $translation !== null,
            'items_count' => (int) ($menu->selected_items_count ?? 0),
            'workspace_url' => route('admin.menus.workspace', ['menu' => $menu->id, 'lang' => $language?->code]),
        ];
    }

    /** @return array<string, mixed> */
    private function summaryFor(Menu $menu, Language $language): array
    {
        $menu->load(['translations' => fn ($query) => $query->where('lang_id', $language->id)])
            ->loadCount(['items as selected_items_count' => fn ($query) => $query->where('lang_id', $language->id)]);

        return $this->menuSummary($menu, $language);
    }
}
