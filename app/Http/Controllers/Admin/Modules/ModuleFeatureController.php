<?php

namespace App\Http\Controllers\Admin\Modules;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Modules\SaveModuleFeaturesRequest;
use App\Services\Fields\ModuleFeatures;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Nwidart\Modules\Facades\Module;
use RuntimeException;

class ModuleFeatureController extends Controller
{
    public function show(string $module, ModuleFeatures $features): View
    {
        $installedModule = Module::find($module);
        abort_unless($installedModule, 404);
        $error = null;
        $revision = $features->revision($module);
        try {
            $document = $features->document($module);
        } catch (RuntimeException $exception) {
            $document = null;
            $error = $exception->getMessage();
        }

        return view('admin.modules.features', compact('installedModule', 'document', 'revision', 'error'));
    }

    public function update(SaveModuleFeaturesRequest $request, string $module, ModuleFeatures $features): RedirectResponse
    {
        abort_unless(Module::find($module), 404);
        $features->save($module, $request->validated('document'), $request->validated('revision'));

        return redirect()->route('admin.modules.features.show', $module)
            ->with('notification', ['type' => 'success', 'message' => 'Modül ek alanları kaydedildi.']);
    }
}
