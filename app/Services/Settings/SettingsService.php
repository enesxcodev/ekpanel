<?php

namespace App\Services\Settings;

use App\Models\Admin\Admin;
use App\Models\Media\MediaFile;
use App\Models\Settings\SettingDefinition;
use App\Models\Settings\SettingSection;
use App\Services\NotifyService;
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
    public function __construct(private SettingRuleBuilder $ruleBuilder, private NotifyService $notifications) {}

    public function get(string $key, mixed $fallback = null): mixed
    {
        $definition = SettingDefinition::query()->with('storedValue.media')->where('key', $key)->first();
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
        return SettingDefinition::query()->where('is_sensitive', false)->with('storedValue.media')->get()
            ->mapWithKeys(fn (SettingDefinition $definition): array => [$definition->key => $definition->effectiveValue()])->all();
    }

    public function mediaUrl(string $key, ?string $fallback = null): ?string
    {
        $definition = SettingDefinition::query()->with('storedValue.media')->where('key', $key)->first();
        if ($definition === null || ! $definition->input_type->isFile()) {
            return $fallback;
        }
        $media = $definition->storedValue?->media;
        $path = $definition->effectiveValue();

        return $path ? Storage::disk($media?->disk ?? 'public')->url($path) : $fallback;
    }

    public function linkExistingLogoMedia(): int
    {
        return DB::transaction(function (): int {
            $linked = 0;
            $definitions = SettingDefinition::query()->whereIn('key', ['site.logo', 'site.footer_logo'])
                ->with('storedValue')->lockForUpdate()->get();
            foreach ($definitions as $definition) {
                if (! $definition->usesMediaLibrary() || $definition->storedValue?->media_id !== null) {
                    continue;
                }
                $path = $definition->effectiveValue();
                $media = is_string($path) ? MediaFile::query()->where('disk', 'public')->where('kind', 'image')->where('path', $path)->lockForUpdate()->first() : null;
                if ($media === null) {
                    continue;
                }
                $definition->storedValue()->updateOrCreate([], ['media_id' => $media->id, 'value' => ['value' => null]]);
                $this->forget($definition->key);
                $linked++;
            }

            return $linked;
        });
    }

    /**
     * @param  array{settings: array<int|string, mixed>, remove?: array<int|string, bool|string|int>}  $payload
     */
    public function updateSection(SettingSection $section, array $payload, Admin $actor): void
    {
        $newFiles = [];
        $oldFiles = [];
        $changedSettings = [];

        try {
            DB::transaction(function () use ($section, $payload, $actor, &$newFiles, &$oldFiles, &$changedSettings): void {
                SettingSection::query()->whereKey($section->id)->lockForUpdate()->firstOrFail();
                $definitions = $section->definitions()->with('storedValue.media')->get();
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
                    if ($this->hasChanged($definition, $input, $removing)) {
                        $changedSettings[] = $definition->label;
                    }
                    if ($definition->usesMediaLibrary()) {
                        if (! $removing && (int) $input !== $definition->storedValue?->media_id) {
                            abort_unless($actor->can('media.view'), 403, 'Medya kütüphanesinden seçim yapma izniniz bulunmuyor.');
                        }
                        $media = $removing ? null : MediaFile::query()->lockForUpdate()->findOrFail((int) $input);
                        $definition->storedValue()->updateOrCreate([], [
                            'media_id' => $media?->id, 'value' => ['value' => null], 'updated_by' => $actor->id,
                        ]);

                        continue;
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
                        'media_id' => null,
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

        if ($changedSettings !== []) {
            $this->notifications->sendForActor(
                $actor,
                Admin::query()->get(),
                'Ayarlar güncellendi',
                $section->label.' bölümünde şu ayarlar değiştirildi: '.implode(', ', array_unique($changedSettings)),
                route('admin.settings.index', ['section' => $section->id], absolute: false),
            );
        }
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

    private function hasChanged(SettingDefinition $definition, mixed $input, bool $removing): bool
    {
        if ($definition->usesMediaLibrary()) {
            return $removing
                ? $definition->effectiveValue() !== null
                : (int) $input !== $definition->storedValue?->media_id;
        }

        if ($definition->input_type->isFile()) {
            return $removing
                ? $definition->effectiveValue() !== null
                : $input instanceof UploadedFile;
        }

        return $definition->effectiveValue() !== $definition->value_type->normalize($removing ? null : $input);
    }
}
