<?php

namespace App\Http\Requests\Admin\Settings;

use App\Enums\Fields\FieldValueType as SettingValueType;
use App\Enums\Settings\SettingInputType;
use App\Models\Settings\SettingDefinition;
use App\Services\Settings\SettingRuleBuilder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator as ValidatorFacade;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class SaveSettingDefinitionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return ($this->user('admin')?->can('manage-setting-definitions') ?? false)
            && ! $this->route('definition')?->is_system;
    }

    public function rules(): array
    {
        $definition = $this->route('definition');
        $inputType = SettingInputType::tryFrom(is_string($this->input('input_type')) ? $this->input('input_type') : '');
        $valueTypes = array_map(fn (SettingValueType $type): string => $type->value, $inputType?->valueTypes() ?? []);
        $immutable = fn (mixed $value): array => $definition ? [Rule::in([$value])] : [];

        return [
            'section_id' => ['required', 'integer', Rule::exists('setting_sections', 'id')],
            'key' => ['required', 'string', 'max:150', 'regex:/^[a-z][a-z0-9_]*(\.[a-z][a-z0-9_]*)+$/', Rule::unique('setting_definitions', 'key')->ignore($definition?->id), ...$immutable($definition?->key)],
            'label' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'input_type' => ['required', Rule::enum(SettingInputType::class), ...$immutable($definition?->input_type->value)],
            'value_type' => ['required', Rule::in($valueTypes), ...$immutable($definition?->value_type->value)],
            'sort_order' => ['required', 'integer', 'min:0', 'max:100000'],
            'default_value' => ['nullable', 'string', 'max:10000'],
            'options' => [$inputType?->hasOptions() ? 'required' : 'nullable', 'array', 'max:100'],
            'options.*' => ['required', 'array:label,value'],
            'options.*.label' => ['required', 'string', 'max:150'],
            'options.*.value' => ['required', 'string', 'max:150', 'distinct:strict', 'regex:/^[a-zA-Z0-9_-]+$/'],
            'attributes' => ['sometimes', 'array:placeholder,rows,step,layout,variant,group,group_order,columns,width'],
            'attributes.group' => ['nullable', 'string', 'max:150'],
            'attributes.group_order' => ['nullable', 'integer', 'between:0,100000'],
            'attributes.columns' => ['nullable', Rule::in([1, 2, 4])],
            'attributes.width' => ['nullable', Rule::in([0, 3, 6, 12])],
            'attributes.placeholder' => ['nullable', 'string', 'max:255'],
            'attributes.rows' => ['nullable', 'integer', 'between:2,20'],
            'attributes.step' => ['nullable', 'numeric', 'gt:0', 'max:1000000'],
            'attributes.layout' => ['nullable', Rule::in(['vertical', 'horizontal'])],
            'attributes.variant' => ['nullable', Rule::in(['standard', 'buttons'])],
            'validation' => ['required', 'array:required,min,max,max_size,extensions,min_width,min_height,max_width,max_height'],
            'validation.required' => ['required', 'boolean'],
            'validation.min' => ['nullable', 'numeric', 'between:-1000000000,1000000000'],
            'validation.max' => ['nullable', 'numeric', 'between:-1000000000,1000000000'],
            'validation.max_size' => ['nullable', 'integer', 'between:1,10240'],
            'validation.extensions' => ['nullable', 'array', 'max:10'],
            'validation.extensions.*' => ['required', 'distinct', Rule::in($inputType === SettingInputType::Image ? ['jpg', 'jpeg', 'png', 'webp', 'gif'] : ['pdf', 'txt', 'csv', 'jpg', 'jpeg', 'png', 'webp', 'gif', 'docx', 'xlsx', 'zip'])],
            'validation.min_width' => ['nullable', 'integer', 'between:1,10000'],
            'validation.min_height' => ['nullable', 'integer', 'between:1,10000'],
            'validation.max_width' => ['nullable', 'integer', 'between:1,10000'],
            'validation.max_height' => ['nullable', 'integer', 'between:1,10000'],
        ];
    }

    public function after(SettingRuleBuilder $builder): array
    {
        return [function (Validator $validator) use ($builder): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $candidate = new SettingDefinition($validator->validated());
            $this->validateBounds($validator, $candidate);
            if ($validator->errors()->isEmpty()) {
                $this->validateValues($validator, $candidate, $builder);
            }
        }];
    }

    private function validateBounds(Validator $validator, SettingDefinition $candidate): void
    {
        $validation = $candidate->validation;
        foreach ([['min', 'max'], ['min_width', 'max_width'], ['min_height', 'max_height']] as [$min, $max]) {
            if (isset($validation[$min], $validation[$max]) && $validation[$min] > $validation[$max]) {
                $validator->errors()->add('validation.'.$max, 'Maksimum değer minimum değerden küçük olamaz.');
            }
        }

        if ($candidate->input_type === SettingInputType::Range && ! isset($validation['min'], $validation['max'])) {
            $validator->errors()->add('validation.min', 'Aralık alanı için minimum ve maksimum değerleri girin.');
        }
        if ($candidate->input_type->isFile() && empty($validation['extensions'])) {
            $validator->errors()->add('validation.extensions', 'En az bir dosya türü seçin.');
        }
        if ($candidate->value_type === SettingValueType::String) {
            foreach (['min', 'max'] as $bound) {
                if (isset($validation[$bound]) && ($validation[$bound] < 0 || floor((float) $validation[$bound]) != $validation[$bound])) {
                    $validator->errors()->add('validation.'.$bound, 'Karakter sınırı sıfır veya pozitif bir tam sayı olmalıdır.');
                }
            }
        }
        if ($candidate->value_type === SettingValueType::Integer) {
            foreach (['min', 'max'] as $bound) {
                if (isset($validation[$bound]) && floor((float) $validation[$bound]) != $validation[$bound]) {
                    $validator->errors()->add('validation.'.$bound, 'Tam sayı alanlarının sınırları tam sayı olmalıdır.');
                }
            }
            $step = $candidate->attributes['step'] ?? null;
            if ($step !== null && floor((float) $step) != $step) {
                $validator->errors()->add('attributes.step', 'Tam sayı alanlarının adımı tam sayı olmalıdır.');
            }
        }
    }

    private function validateValues(Validator $validator, SettingDefinition $candidate, SettingRuleBuilder $builder): void
    {
        $type = $candidate->input_type;
        $checks = [];
        if ($type->isFile() || $type === SettingInputType::Password) {
            if ($candidate->default_value !== null) {
                $validator->errors()->add('default_value', 'Dosya ve şifre alanlarına varsayılan değer atanamaz.');
            }
        } else {
            $checks['default_value'] = $candidate->default_value;
        }

        $existing = $this->route('definition');
        if ($existing?->hasValue()) {
            $currentValue = $existing->effectiveValue();
            if ($type->isFile()) {
                if (! Storage::disk('public')->exists($currentValue)) {
                    $validator->errors()->add('validation', 'Kayıtlı dosya bulunamadı. Önce ayarlardan dosyayı yenileyin veya kaldırın.');

                    return;
                }
                $currentValue = new UploadedFile(Storage::disk('public')->path($currentValue), basename($currentValue), test: true);
            }
            $checks['current_value'] = $currentValue;
        }

        foreach ($checks as $key => $value) {
            $isCurrentValue = $key === 'current_value';
            $check = ValidatorFacade::make(
                [$key => $value],
                [$key => $builder->forDefinition($candidate, false)],
                $builder->messages(),
                [$key => $isCurrentValue ? 'Kayıtlı değer' : 'Varsayılan değer'],
            );
            if ($check->fails()) {
                $validator->errors()->add(
                    $isCurrentValue ? 'validation' : 'default_value',
                    $check->errors()->first().($isCurrentValue ? ' Önce mevcut ayar değerini güncelleyin.' : ''),
                );
            }
        }
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute alanını doldurun.', 'string' => ':attribute metin olmalıdır.',
            'key.regex' => 'Anahtar site.title gibi noktayla ayrılmış küçük harf, sayı ve alt çizgiden oluşmalıdır.',
            'key.unique' => 'Bu ayar anahtarı zaten kullanılıyor.', 'key.in' => 'Ayar anahtarı oluşturulduktan sonra değiştirilemez.',
            'input_type.in' => 'Mevcut alanın input türü değiştirilemez.', 'value_type.in' => 'Seçilen veri türü alanla uyumlu olmalı; mevcut alanın veri türü değiştirilemez.',
            'options.*.value.distinct' => 'Seçenek değerleri benzersiz olmalıdır.',
            'options.*.value.regex' => 'Seçenek değeri yalnızca harf, sayı, alt çizgi ve tire içerebilir.',
            'attributes.array' => 'Yalnızca desteklenen görünüm seçeneklerini kullanın.',
            'validation.array' => 'Yalnızca desteklenen doğrulama seçeneklerini kullanın.',
        ];
    }

    public function attributes(): array
    {
        return ['section_id' => 'Bölüm', 'label' => 'Etiket', 'key' => 'Anahtar', 'input_type' => 'Alan türü', 'value_type' => 'Veri türü', 'options' => 'Seçenekler', 'validation.required' => 'Zorunluluk', 'default_value' => 'Varsayılan değer', 'attributes.group' => 'Grup başlığı', 'attributes.group_order' => 'Grup sırası', 'attributes.columns' => 'Grubun sütun sayısı', 'attributes.width' => 'Alanın genişliği'];
    }
}
