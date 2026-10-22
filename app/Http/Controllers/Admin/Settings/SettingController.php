<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Settings\UpdateSettingsRequest;
use App\Models\Settings\SettingDefinition;
use App\Models\Settings\SettingSection;
use App\Services\Media\MediaService;
use App\Services\Settings\SettingsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Nwidart\Modules\Facades\Module;

class SettingController extends Controller
{
    public function index(MediaService $mediaService): View
    {
        $this->authorize('settings.view');
        $admin = auth('admin')->user();
        $sections = SettingSection::query()->orderBy('sort_order')->orderBy('id')->get()
            ->reject(fn (SettingSection $section): bool => $section->key === 'store' && ! (Module::find('Store')?->isEnabled() ?? false))
            ->filter(fn (SettingSection $section): bool => $admin->can('view', $section))->values();
        $sections->load('definitions.storedValue.media');
        $fields = [];
        $sectionGroups = [];
        foreach ($sections as $section) {
            $sectionGroups[$section->id] = $section->definitions
                ->groupBy(fn (SettingDefinition $definition): string => $definition->getAttribute('attributes')['group'] ?? '')
                ->map(function (Collection $definitions, string $label): array {
                    $attributes = $definitions->first()->getAttribute('attributes') ?? [];
                    $columns = $label !== '' && in_array((int) ($attributes['columns'] ?? 1), [1, 2, 4], true) ? (int) ($attributes['columns'] ?? 1) : 1;

                    return [
                        'label' => $label,
                        'order' => $label !== '' ? ($attributes['group_order'] ?? 0) : 100000,
                        'fields' => $definitions->map(function (SettingDefinition $definition) use ($columns): array {
                            $width = (int) ($definition->getAttribute('attributes')['width'] ?? 0);
                            $width = in_array($width, [3, 6, 12], true) ? $width : (int) (12 / $columns);

                            return ['definition' => $definition, 'class' => match ($width) {
                                3 => 'col-12 col-md-6 col-xl-3',
                                6 => 'col-12 col-md-6',
                                default => 'col-12',
                            }];
                        }),
                    ];
                })->sortBy('order')->values();
            foreach ($section->definitions as $definition) {
                $value = $definition->is_sensitive ? null : $definition->effectiveValue();
                $fields[$definition->id] = [
                    'value' => $value, 'hasValue' => $definition->hasValue(),
                    'mediaId' => $definition->storedValue?->media_id,
                    'fileUrl' => $definition->input_type->isFile() && $value ? Storage::disk('public')->url($value) : null,
                ];
            }
        }

        return view('admin.settings.index', ['sections' => $sections, 'sectionGroups' => $sectionGroups, 'fields' => $fields, 'mediaConfig' => $mediaService->configuration($admin)]);
    }

    public function update(UpdateSettingsRequest $request, SettingSection $section, SettingsService $settings): RedirectResponse
    {
        abort_if($section->key === 'store' && ! (Module::find('Store')?->isEnabled() ?? false), 404);
        try {
            $settings->updateSection($section, $request->validated(), $request->user('admin'));
        } finally {
            $request->redactSensitiveInput();
        }

        return redirect()->route('admin.settings.index', ['section' => $section->id])
            ->with('notification', ['type' => 'success', 'message' => $section->label.' ayarları kaydedildi.']);
    }
}
