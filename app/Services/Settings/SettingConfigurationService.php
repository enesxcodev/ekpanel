<?php

namespace App\Services\Settings;

use App\Enums\Settings\SettingInputType;
use App\Enums\Settings\SettingValueType;
use App\Models\Settings\SettingDefinition;
use App\Models\Settings\SettingSection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class SettingConfigurationService
{
    public function __construct(private SettingsService $settings) {}

    /** @param array{key: string, label: string, sort_order: int|string} $data */
    public function saveSection(array $data, ?SettingSection $section = null): SettingSection
    {
        $section ??= new SettingSection;
        $section->fill($data)->save();
        $this->syncPermissions();

        return $section;
    }

    public function deleteSection(SettingSection $section): void
    {
        DB::transaction(function () use ($section): void {
            SettingSection::query()->whereKey($section->id)->lockForUpdate()->firstOrFail();
            if ($section->definitions()->exists()) {
                throw ValidationException::withMessages(['section' => 'Bölümü silmeden önce özel alanlarını taşıyın veya kaldırın. Sistem alanları bulunan bölümler silinemez.']);
            }
            $section->delete();
            Permission::query()->where('guard_name', 'admin')->whereIn('name', [
                'settings.sections.'.$section->key.'.view', 'settings.sections.'.$section->key.'.update',
            ])->delete();
        });
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /** @param array<string, mixed> $data */
    public function saveDefinition(array $data, ?SettingDefinition $definition = null): SettingDefinition
    {
        $inputType = SettingInputType::from($data['input_type']);
        $valueType = SettingValueType::from($data['value_type']);
        $data['is_sensitive'] = $inputType === SettingInputType::Password;
        $data['default_value'] = $valueType->normalize($data['default_value'] ?? null);
        $data['options'] = $inputType->hasOptions() ? array_values($data['options']) : [];
        $data['attributes'] = $this->supportedAttributes($inputType, $data['attributes'] ?? []);
        $data['validation'] = $this->supportedValidation($inputType, $data['validation']);
        $data = Arr::except($data, ['is_system']);

        $definition = DB::transaction(function () use ($definition, $data): SettingDefinition {
            SettingSection::query()->whereKey($data['section_id'])->lockForUpdate()->firstOrFail();
            $definition ??= new SettingDefinition;
            $definition->fill($data)->save();

            return $definition;
        });
        $this->settings->forget($definition->key);

        return $definition;
    }

    public function deleteDefinition(SettingDefinition $definition): void
    {
        abort_if($definition->is_system, 403);
        $path = $definition->input_type->isFile() ? $definition->effectiveValue() : null;
        $definition->delete();
        $this->settings->forget($definition->key);
        if ($path !== null) {
            Storage::disk('public')->delete($path);
        }
    }

    public function syncPermissions(): void
    {
        foreach (['settings.menu', 'settings.view', 'settings.update', 'settings.manage-definitions'] as $name) {
            Permission::findOrCreate($name, 'admin');
        }
        foreach (SettingSection::query()->pluck('key') as $key) {
            foreach (['view', 'update'] as $action) {
                Permission::findOrCreate('settings.sections.'.$key.'.'.$action, 'admin');
            }
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /** @param array<string, mixed> $attributes @return array<string, mixed> */
    private function supportedAttributes(SettingInputType $type, array $attributes): array
    {
        $allowed = match ($type) {
            SettingInputType::Textarea => ['placeholder', 'rows'],
            SettingInputType::Number, SettingInputType::Range => ['step'],
            SettingInputType::Radio => ['layout', 'variant'],
            SettingInputType::Text, SettingInputType::Email, SettingInputType::Password => ['placeholder'],
            default => [],
        };

        return array_filter(Arr::only($attributes, $allowed), fn (mixed $value): bool => $value !== null && $value !== '');
    }

    /** @param array<string, mixed> $validation @return array<string, mixed> */
    private function supportedValidation(SettingInputType $type, array $validation): array
    {
        $allowed = match (true) {
            $type === SettingInputType::Image => ['required', 'max_size', 'extensions', 'min_width', 'min_height', 'max_width', 'max_height'],
            $type === SettingInputType::File => ['required', 'max_size', 'extensions'],
            $type === SettingInputType::Boolean => ['required'],
            default => ['required', 'min', 'max'],
        };
        $validation = array_filter(Arr::only($validation, $allowed), fn (mixed $value): bool => $value !== null && $value !== '');
        $validation['required'] = (bool) $validation['required'];

        return $validation;
    }
}
