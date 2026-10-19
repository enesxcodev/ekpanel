<?php

namespace App\Services\Settings;

use App\Models\Admin\Admin;
use App\Models\Media\MediaFile;
use App\Models\Settings\SettingDefinition;
use App\Models\Settings\SettingSection;
use App\Services\NotifyService;
use Illuminate\Contracts\Cache\Repository;
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
        $record = $this->snapshot()[$key] ?? null;
        if ($record === null) {
            return $fallback;
        }
        if ($record['is_sensitive']) {
            return SettingDefinition::query()->with('storedValue')->where('key', $key)->first()?->effectiveValue();
        }

        return $record['value'];
    }

    /** @return array<string, mixed> */
    public function all(): array
    {
        $values = [];
        foreach ($this->snapshot() as $key => $record) {
            if (! $record['is_sensitive']) {
                $values[$key] = $record['value'];
            }
        }

        return $values;
    }

    public function mediaUrl(string $key, ?string $fallback = null): ?string
    {
        $record = $this->snapshot()[$key] ?? null;
        if ($record === null || $record['is_sensitive'] || ! $record['is_file']) {
            return $fallback;
        }
        $path = $record['value'];

        return $path ? Storage::disk($record['disk'])->url($path) : $fallback;
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

    /** Invalidating one setting clears the shared non-sensitive snapshot. */
    public function forget(string $key): void
    {
        $this->cache()->forget('settings.snapshot.v1');
    }

    public function forgetSection(SettingSection $section): void
    {
        $this->forget($section->key);
    }

    private function cache(): Repository
    {
        return Cache::memo(config('cache.settings_store', 'file'));
    }

    /** @return array<string, array{is_sensitive: bool, value?: mixed, is_file?: bool, disk?: string}> */
    private function snapshot(): array
    {
        return $this->cache()->remember('settings.snapshot.v1', 300, function (): array {
            $records = [];
            $definitions = SettingDefinition::query()->where('is_sensitive', false)->with('storedValue.media')->get();
            foreach ($definitions as $definition) {
                $records[$definition->key] = [
                    'is_sensitive' => false,
                    'value' => $definition->effectiveValue(),
                    'is_file' => $definition->input_type->isFile(),
                    'disk' => $definition->storedValue?->media?->disk ?? 'public',
                ];
            }
            foreach (SettingDefinition::query()->where('is_sensitive', true)->pluck('key') as $key) {
                $records[$key] = ['is_sensitive' => true];
            }

            return $records;
        });
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
