<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Enums\Settings\SettingInputType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Settings\SaveSettingDefinitionRequest;
use App\Models\Settings\SettingDefinition;
use App\Models\Settings\SettingSection;
use App\Services\Settings\SettingConfigurationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class SettingDefinitionController extends Controller
{
    public function create(): View
    {
        $this->authorize('manage-setting-definitions');

        return $this->form(new SettingDefinition);
    }

    public function store(SaveSettingDefinitionRequest $request, SettingConfigurationService $configuration): RedirectResponse
    {
        $definition = $configuration->saveDefinition($request->validated());

        return redirect()->route('admin.settings.configuration.index', ['section' => $definition->section_id])
            ->with('notification', ['type' => 'success', 'message' => 'Ayar alanı oluşturuldu.']);
    }

    public function edit(SettingDefinition $definition): View
    {
        $this->authorize('manage-setting-definitions');
        abort_if($definition->is_system, 403);

        return $this->form($definition);
    }

    public function update(SaveSettingDefinitionRequest $request, SettingDefinition $definition, SettingConfigurationService $configuration): RedirectResponse
    {
        $configuration->saveDefinition($request->validated(), $definition);

        return redirect()->route('admin.settings.configuration.index', ['section' => $definition->section_id])
            ->with('notification', ['type' => 'success', 'message' => 'Ayar alanı güncellendi.']);
    }

    public function destroy(SettingDefinition $definition, SettingConfigurationService $configuration): RedirectResponse
    {
        $this->authorize('manage-setting-definitions');
        $sectionId = $definition->section_id;
        $configuration->deleteDefinition($definition);

        return redirect()->route('admin.settings.configuration.index', ['section' => $sectionId])
            ->with('notification', ['type' => 'success', 'message' => 'Ayar alanı silindi.']);
    }

    private function form(SettingDefinition $definition): View
    {
        return view('admin.settings.configuration.definition', [
            'definition' => $definition,
            'sections' => SettingSection::query()->orderBy('sort_order')->orderBy('id')->get(),
            'inputTypes' => SettingInputType::cases(),
        ]);
    }
}
