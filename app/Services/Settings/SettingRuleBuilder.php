<?php

namespace App\Services\Settings;

use App\Models\Settings\SettingDefinition;
use App\Services\Fields\FieldRuleBuilder;
use Illuminate\Support\Collection;

class SettingRuleBuilder
{
    /** @return list<mixed> */
    public function forDefinition(SettingDefinition $definition, bool $required): array
    {
        return (new FieldRuleBuilder)->forDefinition($definition->fieldDefinition(), $required);
    }

    /**
     * @param  Collection<int, SettingDefinition>  $definitions
     * @param  array{settings?: array<int|string, mixed>, remove?: array<int|string, mixed>}  $payload
     * @return array<string, list<mixed>>
     */
    public function forSection(Collection $definitions, array $payload): array
    {
        $ids = $definitions->modelKeys();
        $removableIds = $definitions->filter(fn (SettingDefinition $definition): bool => $definition->input_type->isFile() || $definition->is_sensitive)->modelKeys();
        $rules = [
            'settings' => ['present', $ids === [] ? 'array' : 'array:'.implode(',', $ids), ...($ids === [] ? ['size:0'] : [])],
            'remove' => ['sometimes', $removableIds === [] ? 'array' : 'array:'.implode(',', $removableIds), ...($removableIds === [] ? ['size:0'] : [])],
        ];
        $mailEnabledDefinition = $definitions->firstWhere('key', 'mail.enabled');
        $mailEnabled = $mailEnabledDefinition !== null && filter_var($payload['settings'][$mailEnabledDefinition->id] ?? false, FILTER_VALIDATE_BOOL);

        foreach ($definitions as $definition) {
            $required = (bool) ($definition->validation['required'] ?? false);
            if ($mailEnabled && in_array($definition->key, ['mail.host', 'mail.port', 'mail.encryption', 'mail.username', 'mail.password', 'mail.from_address', 'mail.from_name'], true)) {
                $required = true;
            }
            $canKeep = $definition->input_type->isFile() || $definition->is_sensitive;
            $removing = filter_var($payload['remove'][$definition->id] ?? false, FILTER_VALIDATE_BOOL);
            $hasExisting = $definition->hasValue();
            $rules['settings.'.$definition->id] = $this->forDefinition($definition, $required && ! ($canKeep && $hasExisting && ! $removing));

            if ($canKeep) {
                $rules['remove.'.$definition->id] = ['sometimes', 'boolean'];
            }
        }

        return $rules;
    }

    /** @return array<string, string|array<string, string>> */
    public function messages(): array
    {
        return (new FieldRuleBuilder)->messages();
    }
}
