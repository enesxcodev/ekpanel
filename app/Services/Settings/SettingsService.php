<?php

namespace App\Services\Settings;

use App\Models\Admin\Admin;
use App\Models\Settings\SettingDefinition;
use App\Models\Settings\SettingSection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class SettingsService
{
    public function __construct(private SettingRuleBuilder $ruleBuilder) {}

    public function get(string $key, mixed $fallback = null): mixed
    {
        $definition = SettingDefinition::query()->where('key', $key)->first();
        if ($definition === null) {
            return $fallback;
        }
        if ($definition->is_sensitive) {
            return $definition->effectiveValue();
        }

        $cached = Cache::remember($this->cacheKey($key), 300, fn (): array => ['value' => $definition->effectiveValue()]);

        return $cached['value'];
    }

    /** @return array<string, mixed> */
    public function all(): array
    {
        return SettingDefinition::query()->where('is_sensitive', false)->with('storedValue')->get()
            ->mapWithKeys(fn (SettingDefinition $definition): array => [$definition->key => $definition->effectiveValue()])->all();
    }

    /**
     * @param  array{settings: array<int|string, mixed>, remove?: array<int|string, bool|string|int>}  $payload
     */
    public function updateSection(SettingSection $section, array $payload, Admin $actor): void
    {
        $newFiles = [];
        $oldFiles = [];

        try {
            DB::transaction(function () use ($section, $payload, $actor, &$newFiles, &$oldFiles): void {
                SettingSection::query()->whereKey($section->id)->lockForUpdate()->firstOrFail();
                $definitions = $section->definitions()->with('storedValue')->get();
                $labels = $definitions->mapWithKeys(fn (SettingDefinition $definition): array => ['settings.'.$definition->id => $definition->label])->all();
                Validator::make($payload, $this->ruleBuilder->forSection($definitions, $payload), $this->ruleBuilder->messages(), $labels)->validate();

                foreach ($definitions as $definition) {
                    $input = $payload['settings'][$definition->id] ?? null;
                    $removing = filter_var($payload['remove'][$definition->id] ?? false, FILTER_VALIDATE_BOOL);
                    $isFile = $definition->input_type->isFile();

                    if (($isFile || $definition->is_sensitive) && ($input === null || $input === '') && ! $removing) {
                        continue;
                    }
                    if ($removing && $input !== null && $input !== '') {
                        throw ValidationException::withMessages(['settings.'.$definition->id => 'Yeni değer girme ve mevcut değeri kaldırma işlemlerinden birini seçin.']);
                    }
                    if ($isFile) {
                        $previous = $definition->effectiveValue();
                        if ($previous !== null) {
                            $oldFiles[] = $previous;
                        }
                        $input = $input instanceof UploadedFile ? $input->store('settings/'.$definition->id, 'public') : null;
                        if ($input === false) {
                            throw new RuntimeException('Dosya kaydedilemedi.');
                        }
                        if ($input !== null) {
                            $newFiles[] = $input;
                        }
                    }
                    $definition->storedValue()->updateOrCreate([], [
                        'value' => ['value' => $definition->value_type->normalize($removing ? null : $input)],
                        'updated_by' => $actor->id,
                    ]);
                }
            });
        } catch (Throwable $exception) {
            foreach ($newFiles as $path) {
                Storage::disk('public')->delete($path);
            }
            throw $exception;
        }

        foreach ($oldFiles as $path) {
            Storage::disk('public')->delete($path);
        }
        $this->forgetSection($section);
    }

    public function forget(string $key): void
    {
        Cache::forget($this->cacheKey($key));
    }

    public function forgetSection(SettingSection $section): void
    {
        foreach ($section->definitions()->pluck('key') as $key) {
            $this->forget($key);
        }
    }

    private function cacheKey(string $key): string
    {
        return 'settings.value.'.$key;
    }
}
