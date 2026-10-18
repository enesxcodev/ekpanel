<?php

namespace App\Services\Media;

use App\Models\Admin\Admin;
use App\Models\Media\MediaFile;
use App\Models\Media\MediaUsage;
use App\Rules\SelectedFeatureMedia;
use App\Services\Fields\FieldDefinition;
use App\Services\Fields\FieldRuleBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class ContentMediaService
{
    public function __construct(private MediaService $media) {}

    /** @param list<string> $extensions */
    public function definition(string $field = 'image', string $label = 'Görsel', int $maxSize = 4096, array $extensions = ['jpg', 'jpeg', 'png', 'webp']): FieldDefinition
    {
        return FieldDefinition::fromArray([
            'key' => $field, 'label' => $label, 'input_type' => $field === 'image' ? 'image' : 'file',
            'value_type' => 'media', 'validation' => ['extensions' => $extensions, 'max_size' => $maxSize],
        ]);
    }

    /** @return array<string, list<mixed>> */
    public function rules(FieldDefinition $definition, bool $required = false): array
    {
        $field = $definition->key;

        return [
            $field => ['prohibited'],
            $field.'_media_id' => ['bail', $required ? 'required' : 'nullable', 'prohibited_if:remove_'.$field.',1', 'integer', new SelectedFeatureMedia($definition)],
            'remove_'.$field => ['sometimes', 'boolean'],
        ];
    }

    /** @param array<string, mixed> $payload @return array<string, int|string|null> */
    public function prepare(Model $owner, array $payload, FieldDefinition $definition, ?Admin $actor, bool $required = false): array
    {
        $field = $definition->key;
        $column = $field.'_media_id';
        $removing = filter_var($payload['remove_'.$field] ?? false, FILTER_VALIDATE_BOOL);
        $value = $removing ? null : (array_key_exists($column, $payload) ? $payload[$column] : $owner->getAttribute($column));
        $check = Validator::make([$column => $value], [$column => ['bail', 'nullable', 'integer']], [], [$column => $definition->label]);
        $check->validate();
        $mediaId = $value === null || $value === '' ? null : (int) $value;
        if ($mediaId !== null && $mediaId !== $owner->getAttribute($column)) {
            abort_unless($actor?->can('media.view'), 403, 'Medya kütüphanesinden seçim yapma izniniz bulunmuyor.');
        }
        $media = $mediaId === null ? null : MediaFile::query()->where('disk', 'public')->lockForUpdate()->find($mediaId);
        $keepLegacy = ! $removing && $mediaId === null && ! $owner->getAttribute($column) && $owner->getAttribute($field);
        Validator::make([$column => $mediaId], [$column => ['bail', $required && ! $keepLegacy ? 'required' : 'nullable', 'integer', new SelectedFeatureMedia($definition)]], (new FieldRuleBuilder)->messages(), [$column => $definition->label])->validate();

        return [$column => $media?->id, $field => $media?->path ?? ($keepLegacy ? $owner->getAttribute($field) : null)];
    }

    public function syncUsage(Model $owner, string $field, string $label, string $route): void
    {
        $mediaId = $owner->getAttribute($field.'_media_id');
        if ($mediaId !== null) {
            $media = MediaFile::query()->lockForUpdate()->findOrFail($mediaId);
            $this->media->attachUsage($media, $owner, $field, $label, $route);
        } else {
            MediaUsage::query()->where('owner_type', $owner->getMorphClass())->where('owner_id', $owner->getKey())->where('field', $field)->delete();
        }
    }
}
