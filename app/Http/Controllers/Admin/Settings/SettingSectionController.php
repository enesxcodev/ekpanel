<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Settings\SaveSettingSectionRequest;
use App\Models\Settings\SettingSection;
use App\Services\NotifyService;
use App\Services\Settings\SettingConfigurationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SettingSectionController extends Controller
{
    public function store(SaveSettingSectionRequest $request, SettingConfigurationService $configuration, NotifyService $notifications): RedirectResponse
    {
        $section = $configuration->saveSection($request->validated());
        $notifications->sendToAdminsForActor(
            $request->user('admin'),
            'Ayar bölümü eklendi',
            'Yeni bir ayar bölümü eklendi.',
            route('admin.settings.configuration.index', ['section' => $section->id], absolute: false),
        );

        return redirect()->route('admin.settings.configuration.index', ['section' => $section->id])
            ->with('notification', ['type' => 'success', 'message' => 'Ayar bölümü oluşturuldu.']);
    }

    public function update(SaveSettingSectionRequest $request, SettingSection $section, SettingConfigurationService $configuration, NotifyService $notifications): RedirectResponse
    {
        $section = $configuration->saveSection($request->validated(), $section);
        $notifications->sendToAdminsForActor(
            $request->user('admin'),
            'Ayar bölümü güncellendi',
            'Bir ayar bölümü güncellendi.',
            route('admin.settings.configuration.index', ['section' => $section->id], absolute: false),
        );

        return redirect()->route('admin.settings.configuration.index', ['section' => $section->id])
            ->with('notification', ['type' => 'success', 'message' => 'Ayar bölümü güncellendi.']);
    }

    public function destroy(SettingSection $section, SettingConfigurationService $configuration, Request $request, NotifyService $notifications): RedirectResponse
    {
        $this->authorize('manage-setting-definitions');
        $configuration->deleteSection($section);
        $notifications->sendToAdminsForActor(
            $request->user('admin'),
            'Ayar bölümü silindi',
            'Bir ayar bölümü silindi.',
            route('admin.settings.configuration.index', absolute: false),
        );

        return redirect()->route('admin.settings.configuration.index')
            ->with('notification', ['type' => 'success', 'message' => 'Ayar bölümü silindi.']);
    }
}
