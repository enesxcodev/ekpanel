<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Settings\UpdateSettingsRequest;
use App\Models\Settings\SettingSection;
use App\Services\Settings\SettingsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index(): View
    {
        $this->authorize('settings.view');
        $admin = auth('admin')->user();
        $sections = SettingSection::query()->orderBy('sort_order')->orderBy('id')->get()
            ->filter(fn (SettingSection $section): bool => $admin->can('view', $section))->values();
        $sections->load('definitions.storedValue');
        $fields = [];
        foreach ($sections as $section) {
            foreach ($section->definitions as $definition) {
                $value = $definition->is_sensitive ? null : $definition->effectiveValue();
                $fields[$definition->id] = [
                    'value' => $value, 'hasValue' => $definition->hasValue(),
                    'fileUrl' => $definition->input_type->isFile() && $value ? Storage::disk('public')->url($value) : null,
                ];
            }
        }

        return view('admin.settings.index', compact('sections', 'fields'));
    }

    public function update(UpdateSettingsRequest $request, SettingSection $section, SettingsService $settings): RedirectResponse
    {
        try {
            $settings->updateSection($section, $request->validated(), $request->user('admin'));
        } finally {
            $request->redactSensitiveInput();
        }

        return redirect()->route('admin.settings.index', ['section' => $section->id])
            ->with('notification', ['type' => 'success', 'message' => $section->label.' ayarları kaydedildi.']);
    }
}
