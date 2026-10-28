<?php

namespace App\Services\Fields;

use App\Enums\Fields\FieldInputType;
use App\Enums\Fields\FieldValueType;
use App\Services\Media\MediaFileInspector;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use JsonException;
use Nwidart\Modules\Facades\Module;
use RuntimeException;
use Throwable;

class ModuleFeatures
{
    /** @var array<string, array<string, mixed>> */
    private array $documents = [];

    /** @return array{schema_version: int, forms: array<string, mixed>} */
    public function document(string $moduleName): array
    {
        $path = Module::findOrFail($moduleName)->getPath().'/features.json';

        return is_file($path) ? $this->read($path) : ['schema_version' => 1, 'forms' => []];
    }

    public function revision(string $moduleName): string
    {
        $path = Module::findOrFail($moduleName)->getPath().'/features.json';

        return hash('sha256', is_file($path) ? File::get($path) : '');
    }

    /** @param array<string, mixed> $document */
    public function save(string $moduleName, array $document, string $revision): void
    {
        $path = Module::findOrFail($moduleName)->getPath().'/features.json';
        $this->validateDocument($document);

        Cache::lock('module-features:'.hash('sha256', $path), 10)->block(5, function () use ($moduleName, $document, $revision, $path): void {
            if (! hash_equals($this->revision($moduleName), $revision)) {
                throw ValidationException::withMessages(['revision' => 'Alanlar başka bir işlemde değiştirildi. Sayfayı yenileyip tekrar deneyin.']);
            }
            if (is_link($path) || ! is_writable(dirname($path)) || (is_file($path) && ! is_writable($path))) {
                throw ValidationException::withMessages(['document' => 'Modülün alan tanımları dosyasına yazılamıyor.']);
            }
            $document['forms'] = (object) $document['forms'];
            try {
                File::replace($path, json_encode($document, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR).PHP_EOL);
            } catch (Throwable $exception) {
                report($exception);

                throw ValidationException::withMessages(['document' => 'Alan tanımları kaydedilemedi. Dosya yazma izinlerini kontrol edip tekrar deneyin.']);
            }
            unset($this->documents[$path]);
        });
    }

    /** @return array{label: string, fields: array<string, FieldDefinition>} */
    public function form(string $moduleName, string $form): array
    {
        $module = Module::findOrFail($moduleName);
        abort_unless($module->isEnabled(), 404);
        $path = $module->getPath().'/features.json';
        if (! is_file($path)) {
            return ['label' => 'Ek alanlar', 'fields' => []];
        }

        if (! array_key_exists($path, $this->documents)) {
            $this->documents[$path] = $this->read($path);
        }
        $definition = $this->documents[$path]['forms'][$form] ?? [];
        $fields = [];
        foreach ($definition['fields'] ?? [] as $field) {
            $fields[$field['key']] = FieldDefinition::fromArray($field);
        }

        return ['label' => $definition['label'] ?? 'Ek alanlar', 'fields' => $fields];
    }

    /** @return array<string, mixed> */
    private function read(string $path): array
    {
        try {
            $document = json_decode(file_get_contents($path), true, 64, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException('Geçersiz alan tanımı: '.$path, previous: $exception);
        }

        return $this->validateDocument($document, $path);
    }

    /** @return array<string, mixed> */
    public function validateDocument(mixed $document, string $path = 'features.json'): array
    {
        $validator = Validator::make(['document' => $document], [
            'document' => ['required', 'array:schema_version,forms'],
            'document.schema_version' => ['required', 'integer', Rule::in([1])],
            'document.forms' => ['present', 'array'],
            'document.forms.*' => ['required', 'array:label,fields'],
            'document.forms.*.label' => ['sometimes', 'string', 'max:150'],
            'document.forms.*.fields' => ['present', 'list', 'max:100'],
            'document.forms.*.fields.*' => ['required', 'array:key,label,description,input_type,value_type,options,attributes,validation,default_value'],
            'document.forms.*.fields.*.key' => ['required', 'string', 'max:100', 'regex:/^[a-z][a-z0-9_]*$/'],
            'document.forms.*.fields.*.label' => ['required', 'string', 'max:150'],
            'document.forms.*.fields.*.description' => ['nullable', 'string', 'max:1000'],
            'document.forms.*.fields.*.input_type' => ['required', Rule::in(array_column(array_filter(FieldInputType::cases(), fn (FieldInputType $type): bool => $type !== FieldInputType::Password), 'value'))],
            'document.forms.*.fields.*.value_type' => ['required', Rule::enum(FieldValueType::class)],
            'document.forms.*.fields.*.options' => ['sometimes', 'list', 'max:100'],
            'document.forms.*.fields.*.options.*' => ['required', 'array:label,value'],
            'document.forms.*.fields.*.options.*.label' => ['required', 'string', 'max:150'],
            'document.forms.*.fields.*.options.*.value' => ['required', 'string', 'max:150', 'regex:/^[a-zA-Z0-9_-]+$/'],
            'document.forms.*.fields.*.attributes' => ['sometimes', 'array:placeholder,rows,step,layout,variant'],
            'document.forms.*.fields.*.attributes.placeholder' => ['nullable', 'string', 'max:255'],
            'document.forms.*.fields.*.attributes.rows' => ['sometimes', 'integer', 'between:2,20'],
            'document.forms.*.fields.*.attributes.step' => ['sometimes', 'numeric', 'gt:0', 'max:1000000'],
            'document.forms.*.fields.*.attributes.layout' => ['sometimes', Rule::in(['vertical', 'horizontal'])],
            'document.forms.*.fields.*.attributes.variant' => ['sometimes', Rule::in(['standard', 'buttons'])],
            'document.forms.*.fields.*.validation' => ['sometimes', 'array:required,min,max,max_size,extensions,min_width,min_height,max_width,max_height'],
            'document.forms.*.fields.*.validation.required' => ['sometimes', 'boolean'],
            'document.forms.*.fields.*.validation.min' => ['sometimes', 'numeric', 'between:-1000000000,1000000000'],
            'document.forms.*.fields.*.validation.max' => ['sometimes', 'numeric', 'between:-1000000000,1000000000'],
            'document.forms.*.fields.*.validation.max_size' => ['sometimes', 'integer', 'between:1,51200'],
            'document.forms.*.fields.*.validation.extensions' => ['sometimes', 'list', 'min:1', 'max:15'],
            'document.forms.*.fields.*.validation.extensions.*' => ['required', Rule::in(array_unique([...MediaFileInspector::EXTENSIONS, 'gif', 'csv', 'xlsx', 'zip']))],
            'document.forms.*.fields.*.validation.min_width' => ['sometimes', 'integer', 'between:1,10000'],
            'document.forms.*.fields.*.validation.max_width' => ['sometimes', 'integer', 'between:1,10000'],
            'document.forms.*.fields.*.validation.min_height' => ['sometimes', 'integer', 'between:1,10000'],
            'document.forms.*.fields.*.validation.max_height' => ['sometimes', 'integer', 'between:1,10000'],
        ]);
        if ($validator->fails()) {
            throw new RuntimeException('Geçersiz alan tanımı: '.$path.' — '.$validator->errors()->first());
        }

        foreach ($document['forms'] as $name => $form) {
            if (! preg_match('/^[a-z][a-z0-9_]*$/', (string) $name)) {
                throw new RuntimeException('Geçersiz form anahtarı: '.$path);
            }
            $keys = [];
            foreach ($form['fields'] as $data) {
                $field = FieldDefinition::fromArray($data);
                $invalid = in_array($field->key, $keys, true) || ! in_array($field->value_type, $field->input_type->valueTypes(), true);
                $keys[] = $field->key;
                $validation = $field->validation;
                if ($field->value_type === FieldValueType::Media) {
                    $invalid = $invalid || strlen('extra_fields.'.$field->key) > 80 || array_diff($validation['extensions'] ?? [], MediaFileInspector::EXTENSIONS) !== [];
                }
                foreach ([['min', 'max'], ['min_width', 'max_width'], ['min_height', 'max_height']] as [$min, $max]) {
                    $invalid = $invalid || (isset($validation[$min], $validation[$max]) && $validation[$min] > $validation[$max]);
                }
                if ($field->input_type->hasOptions()) {
                    $values = array_column($field->options, 'value');
                    $invalid = $invalid || $values === [] || count($values) !== count(array_unique($values));
                }
                if ($field->input_type === FieldInputType::Range) {
                    $invalid = $invalid || ! isset($validation['min'], $validation['max']);
                }
                if ($field->input_type === FieldInputType::Image) {
                    $invalid = $invalid || array_diff($validation['extensions'] ?? [], ['jpg', 'jpeg', 'png', 'webp', 'gif']) !== [];
                }
                if ($field->input_type->isFile()) {
                    $invalid = $invalid || $field->default_value !== null;
                } else {
                    $invalid = $invalid || Validator::make(['value' => $field->default_value], ['value' => (new FieldRuleBuilder)->forDefinition($field, false)])->fails();
                }
                if ($invalid) {
                    throw new RuntimeException('Uyumsuz veya yinelenen alan tanımı: '.$path.' — '.$name.'.'.$field->key);
                }
            }
        }

        return $document;
    }
}
