<?php

namespace App\Http\Requests\Admin\Settings;

use App\Models\Settings\SettingDefinition;
use App\Services\Settings\SettingRuleBuilder;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;

class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin')?->can('update', $this->route('section')) ?? false;
    }

    public function rules(SettingRuleBuilder $builder): array
    {
        $this->route('section')->load('definitions.storedValue.media');

        return $builder->forSection($this->route('section')->definitions, $this->all());
    }

    public function messages(): array
    {
        return (new SettingRuleBuilder)->messages();
    }

    public function attributes(): array
    {
        return $this->route('section')->definitions
            ->mapWithKeys(fn (SettingDefinition $definition): array => ['settings.'.$definition->id => $definition->label])
            ->all() + ['settings' => 'Ayarlar', 'remove' => 'Kaldırılacak değerler'];
    }

    public function redactSensitiveInput(): void
    {
        $input = $this->input();
        foreach ($this->route('section')->definitions as $definition) {
            if ($definition->is_sensitive) {
                Arr::forget($input, 'settings.'.$definition->id);
            }
        }
        $this->replace($input);
        app('request')->replace($input);
    }

    protected function failedValidation(Validator $validator): void
    {
        $this->redactSensitiveInput();

        parent::failedValidation($validator);
    }
}
