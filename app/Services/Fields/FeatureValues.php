<?php

namespace App\Services\Fields;

use App\Enums\Fields\FieldValueType;
use App\Models\Admin\Admin;
use App\Models\Media\MediaFile;
use App\Models\Media\MediaUsage;
use App\Services\Media\MediaService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class FeatureValues
{
    /**
     * @param  array<string, FieldDefinition>  $definitions
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $existing
     * @return array<string, list<mixed>>
     */
    public function rules(array $definitions, array $payload = [], array $existing = []): array
    {
        $keys = array_keys($definitions);
        $fileKeys = array_keys(array_filter($definitions, fn (FieldDefinition $field): bool => $field->input_type->isFile()));
        $rules = [
            'extra_fields' => ['sometimes', $keys === [] ? 'array' : 'array:'.implode(',', $keys), ...($keys === [] ? ['size:0'] : [])],
            'remove_features' => ['sometimes', $fileKeys === [] ? 'array' : 'array:'.implode(',', $fileKeys), ...($fileKeys === [] ? ['size:0'] : [])],
        ];
        foreach ($definitions as $key => $definition) {
            $required = (bool) ($definition->validation['required'] ?? false);
            $isFile = $definition->input_type->isFile();
            $removing = filter_var($payload['remove_features'][$key] ?? false, FILTER_VALIDATE_BOOL);
            $canKeep = $isFile && $definition->value_type !== FieldValueType::Media && ! empty($existing[$key]) && ! $removing;
            $rules['extra_fields.'.$key] = (new FieldRuleBuilder)->forDefinition($definition, $required && ! $canKeep);
            if ($isFile) {
                $rules['remove_features.'.$key] = ['sometimes', 'boolean'];
            }
        }

        return $rules;
    }

    /** @param array<string, FieldDefinition> $definitions @return array<string, string> */
    public function labels(array $definitions): array
    {
        $labels = ['extra_fields' => 'Ek alanlar', 'remove_features' => 'Kaldırılacak dosyalar'];
        foreach ($definitions as $key => $definition) {
            $labels['extra_fields.'.$key] = $definition->label;
        }

        return $labels;
    }

    /**
     * @param  array<string, FieldDefinition>  $definitions
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $existing
     * @return array<string, mixed>
     */
    public function prepare(array $definitions, array $payload, array $existing, string $directory, FieldFiles $files, ?Admin $actor = null): array
    {
        $values = $existing;
        foreach ($definitions as $key => $definition) {
            $input = $payload['extra_fields'][$key] ?? null;
            $removing = filter_var($payload['remove_features'][$key] ?? false, FILTER_VALIDATE_BOOL);
            if ($definition->value_type === FieldValueType::Media) {
                $supplied = array_key_exists($key, $payload['extra_fields'] ?? []);
                $input = $removing ? null : ($supplied ? $input : ($existing[$key] ?? null));
                if ($input !== null && $input !== '' && (! is_scalar($input) || ! ctype_digit((string) $input))) {
                    throw ValidationException::withMessages(['extra_fields.'.$key => 'Geçerli bir medya seçin.']);
                }
                $mediaId = $input === null || $input === '' ? null : (int) $input;
                if ($mediaId !== null && $mediaId !== ($existing[$key] ?? null)) {
                    abort_unless($actor?->can('media.view'), 403, 'Medya kütüphanesinden seçim yapma izniniz bulunmuyor.');
                }
                if ($mediaId !== null) {
                    MediaFile::query()->where('disk', 'public')->lockForUpdate()->findOrFail($mediaId);
                }
                Validator::make(['extra_fields' => [$key => $mediaId]], ['extra_fields.'.$key => (new FieldRuleBuilder)->forDefinition($definition, (bool) ($definition->validation['required'] ?? false))], (new FieldRuleBuilder)->messages(), ['extra_fields.'.$key => $definition->label])->validate();
                $values[$key] = $mediaId;
            } elseif ($definition->input_type->isFile()) {
                if ($removing && $input instanceof UploadedFile) {
                    throw ValidationException::withMessages(['extra_fields.'.$key => 'Yeni dosya yükleme ve mevcut dosyayı kaldırma işlemlerinden birini seçin.']);
                }
                $values[$key] = $files->replace($input, $existing[$key] ?? null, $directory.'/'.$key, $removing);
            } elseif (array_key_exists($key, $payload['extra_fields'] ?? [])) {
                $values[$key] = $definition->value_type->normalize($input);
            } elseif (! array_key_exists($key, $values)) {
                $values[$key] = $definition->value_type->normalize($definition->default_value);
            }
        }

        return $values;
    }

    /**
     * @param  array<string, FieldDefinition>  $definitions
     * @param  array<string, mixed>  $values
     * @return array<string, array{value: mixed, hasValue: bool, fileUrl: ?string}>
     */
    public function fields(array $definitions, array $values): array
    {
        $fields = [];
        $mediaIds = [];
        foreach ($definitions as $key => $definition) {
            if ($definition->value_type === FieldValueType::Media && ! empty($values[$key])) {
                $mediaIds[] = $values[$key];
            }
        }
        $mediaFiles = $mediaIds === [] ? collect() : MediaFile::query()->where('disk', 'public')->whereKey($mediaIds)->get()->keyBy('id');
        foreach ($definitions as $key => $definition) {
            $value = array_key_exists($key, $values) ? $values[$key] : $definition->value_type->normalize($definition->default_value);
            $fileUrl = $definition->input_type->isFile() && $value && $definition->value_type !== FieldValueType::Media ? Storage::disk('public')->url($value) : null;
            if ($definition->value_type === FieldValueType::Media) {
                $media = $mediaFiles->get($value);
                $fileUrl = $media ? Storage::disk($media->disk)->url($media->path) : null;
            }
            $fields[$key] = ['value' => $value, 'hasValue' => $value !== null && $value !== '', 'fileUrl' => $fileUrl];
        }

        return $fields;
    }

    /** @param array<string, FieldDefinition> $definitions */
    public function syncMediaUsages(array $definitions, Model $owner, MediaService $mediaService, string $label, ?string $routeName = null): void
    {
        $values = $owner->extra_fields ?? [];
        $activeFields = [];
        foreach ($definitions as $key => $definition) {
            if ($definition->value_type !== FieldValueType::Media || empty($values[$key])) {
                continue;
            }
            $field = 'extra_fields.'.$key;
            $media = MediaFile::query()->where('disk', 'public')->lockForUpdate()->findOrFail($values[$key]);
            $mediaService->attachUsage($media, $owner, $field, $label.' / '.$definition->label, $routeName);
            $activeFields[] = $field;
        }
        $unusedIds = MediaUsage::query()->where('owner_type', $owner->getMorphClass())->where('owner_id', $owner->getKey())
            ->get(['id', 'field'])->filter(fn (MediaUsage $usage): bool => str_starts_with($usage->field, 'extra_fields.') && ! in_array($usage->field, $activeFields, true))->modelKeys();
        MediaUsage::query()->whereKey($unusedIds)->delete();
    }
}
