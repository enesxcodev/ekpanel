<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Settings\SaveSettingSectionRequest;
use App\Models\Settings\SettingSection;
use App\Services\Settings\SettingConfigurationService;
use Illuminate\Http\RedirectResponse;

class SettingSectionController extends Controller
{
    public function store(SaveSettingSectionRequest $request, SettingConfigurationService $configuration): RedirectResponse
    {
        $section = $configuration->saveSection($request->validated());

        return redirect()->route('admin.settings.configuration.index', ['section' => $section->id])
            ->with('notification', ['type' => 'success', 'message' => 'Ayar bölümü oluşturuldu.']);
    }

    public function update(SaveSettingSectionRequest $request, SettingSection $section, SettingConfigurationService $configuration): RedirectResponse
    {
        $configuration->saveSection($request->validated(), $section);

        return redirect()->route('admin.settings.configuration.index', ['section' => $section->id])
            ->with('notification', ['type' => 'success', 'message' => 'Ayar bölümü güncellendi.']);
    }

    public function destroy(SettingSection $section, SettingConfigurationService $configuration): RedirectResponse
    {
        $this->authorize('manage-setting-definitions');
        $configuration->deleteSection($section);

        return redirect()->route('admin.settings.configuration.index')
            ->with('notification', ['type' => 'success', 'message' => 'Ayar bölümü silindi.']);
    }
}
