<?php

namespace App\Http\Controllers\Admin\Modules;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Modules\InstallModulesRequest;
use App\Services\Admin\Modules\ModuleCatalog;
use App\Services\Admin\Modules\ModuleInstallations;
use App\Services\Admin\Modules\ModuleManager;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Nwidart\Modules\Facades\Module;
use RuntimeException;
use Throwable;

class ModuleController extends Controller
{
    public function index(ModuleManager $manager, ModuleInstallations $installations): View
    {
        return view('admin.modules.index', ['modules' => Module::all(), 'history' => $manager->history(), 'installation' => $installations->latest()]);
    }

    public function installation(string $installation, ModuleInstallations $installations): JsonResponse
    {
        $status = $installations->find($installation);
        abort_unless($status, 404);

        return response()->json($status)->header('Cache-Control', 'no-store');
    }

    public function create(Request $request, ModuleCatalog $catalog): View
    {
        $error = null;
        try {
            $modules = $catalog->all();
        } catch (Throwable $exception) {
            $modules = [];
            $error = $exception->getMessage();
        }
        $search = mb_substr((string) $request->query('search', ''), 0, 150);
        if ($search !== '') {
            $modules = array_values(array_filter($modules, fn (array $module): bool => str_contains(
                mb_strtolower($module['name'].' '.$module['description']), mb_strtolower($search),
            )));
        }

        return view('admin.modules.create', compact('modules', 'error', 'search'));
    }

    public function show(string $id, ModuleCatalog $catalog): View
    {
        try {
            $entry = collect($catalog->all())->firstWhere('id', $id);
        } catch (Throwable $exception) {
            abort(503, 'Modül kataloğu şu anda okunamıyor.');
        }
        abort_unless($entry, 404);

        return view('admin.modules.show', ['entry' => $entry]);
    }

    public function store(InstallModulesRequest $request, ModuleCatalog $catalog, ModuleInstallations $installations): RedirectResponse
    {
        return $this->respond(function () use ($request, $catalog, $installations): void {
            $entries = collect($catalog->all())->keyBy('id');
            $selected = $request->validated('modules');
            foreach ($selected as $id) {
                if (! $entries->has($id)) {
                    throw new RuntimeException('Seçilen modül katalogda bulunamadı: '.$id);
                }
            }
            $installations->enqueue(array_map(fn (string $id): array => $entries[$id], $selected), $request->user('admin')->id);
        }, 'Kurulum sıraya alındı. Aşamaları bu ekrandan izleyebilirsiniz.');
    }

    public function enable(Request $request, string $module, ModuleInstallations $installations): RedirectResponse
    {
        return $this->respond(function () use ($installations, $module, $request): void {
            $installations->enqueueEnable($module, $request->user('admin')->id);
        }, 'Etkinleştirme sıraya alındı. İlerlemeyi bu ekrandan izleyebilirsiniz.');
    }

    public function disable(Request $request, string $module, ModuleManager $manager): RedirectResponse
    {
        return $this->respond(function () use ($manager, $module, $request): void {
            app(ModuleInstallations::class)->assertIdle();
            $manager->disable($module, $request->user('admin')->id);
        }, 'Modül devre dışı bırakıldı.');
    }

    public function destroy(Request $request, string $module, ModuleManager $manager): RedirectResponse
    {
        return $this->respond(function () use ($manager, $module, $request): void {
            app(ModuleInstallations::class)->assertIdle();
            $manager->remove($module, $request->user('admin')->id);
        }, 'Modül kaldırıldı. İçerik verileri ve kod yedeği korundu.');
    }

    private function respond(callable $operation, string $message): RedirectResponse
    {
        try {
            $operation();
        } catch (Throwable $exception) {
            report($exception);

            return redirect()->route('admin.modules.index')->with('notification', ['type' => 'error', 'message' => $exception->getMessage()]);
        }

        return redirect()->route('admin.modules.index')->with('notification', ['type' => 'success', 'message' => $message]);
    }
}
