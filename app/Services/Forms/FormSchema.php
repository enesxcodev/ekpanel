<?php

namespace App\Services\Forms;

use App\Models\Forms\FormField;
use App\Models\Forms\SiteForm;
use App\Rules\SafeMediaUpload;
use App\Services\Fields\FieldRuleBuilder;
use DOMDocument;
use DOMElement;
use Illuminate\Support\Str;

class FormSchema
{
    public function __construct(private FieldRuleBuilder $rules) {}

    /** @return array{rules: array<string, mixed>, labels: array<string, string>} */
    public function validation(SiteForm $form): array
    {
        $rules = ['answers' => ['present', 'array']];
        $labels = [];
        foreach ($form->fields as $field) {
            $key = 'answers.'.$field->key;
            $rules[$key] = $this->rules->forDefinition($field->definition(), $field->required);
            if ($field->input_type === 'file') {
                $rules[$key][] = new SafeMediaUpload;
            }
            if ($field->input_type === 'boolean' && $field->required) {
                $rules[$key] = ['required', 'accepted'];
            }
            if ($field->input_type === 'tel') {
                $rules[$key][] = 'regex:/^[+0-9() .-]{5,40}$/';
            }
            $labels[$key] = $field->label;
        }

        return compact('rules', 'labels');
    }

    /** @return list<array<string, mixed>> */
    public function editorFields(SiteForm $form): array
    {
        return $form->fields->map(fn (FormField $field): array => [
            'key' => $field->key, 'label' => $field->label, 'input_type' => $field->input_type, 'required' => $field->required,
            'placeholder' => $field->placeholder ?? '', 'description' => $field->description ?? '',
            'width' => $field->width, 'css_class' => $field->css_class ?? '', 'input_class' => $field->input_class ?? '',
            'role' => $field->role ?? '', 'options' => $field->options ?? [],
            'max_length' => $field->validation['max'] ?? null,
            'max_size' => $field->validation['max_size'] ?? 5120,
            'extensions' => implode(',', $field->validation['extensions'] ?? ['pdf', 'docx']),
        ])->all();
    }

    /** @return array{fields: list<array<string,mixed>>, warnings: list<string>} */
    public function import(string $html): array
    {
        $document = new DOMDocument;
        $previous = libxml_use_internal_errors(true);
        try {
            $document->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
        $labels = [];
        foreach ($document->getElementsByTagName('label') as $label) {
            if ($label->getAttribute('for') !== '') {
                $labels[$label->getAttribute('for')] = trim($label->textContent);
            }
        }
        $fields = [];
        $keys = [];
        $warnings = [];
        $roles = [];
        $xpath = new \DOMXPath($document);
        foreach ($xpath->query('//input | //textarea | //select') as $element) {
            if (! $element instanceof DOMElement) {
                continue;
            }
            $type = strtolower($element->tagName === 'input' ? ($element->getAttribute('type') ?: 'text') : $element->tagName);
            if (in_array($type, ['hidden', 'submit', 'button', 'reset', 'password'], true) || $element->hasAttribute('disabled')) {
                continue;
            }
            if ($element->hasAttribute('multiple') || count($fields) >= 50) {
                $warnings[] = 'Çoklu seçim alanı veya 50 alan sınırını aşan öğe içeri alınmadı.';

                continue;
            }
            $raw = $element->getAttribute('name') ?: $element->getAttribute('id');
            $key = str_replace('-', '_', Str::slug($raw));
            $key = substr(preg_replace('/[^a-z0-9_]/', '', $key), 0, 80);
            if ($key === '' || ! ctype_alpha($key[0])) {
                $key = 'field_'.(count($fields) + 1);
            }
            if (in_array($key, $keys, true)) {
                $warnings[] = 'Tekrarlanan alan atlandı: '.$key;

                continue;
            }
            $keys[] = $key;
            $type = $type === 'checkbox' ? 'boolean' : $type;
            if (! in_array($type, ['text', 'email', 'tel', 'textarea', 'number', 'select', 'radio', 'boolean', 'file'], true)) {
                $type = 'text';
            }
            if ($type === 'radio') {
                $warnings[] = 'Radio gruplarını alan düzenleyicisinde seçenekleriyle tanımlayın.';

                continue;
            }
            $label = $labels[$element->getAttribute('id')] ?? $element->getAttribute('placeholder') ?: $raw ?: $key;
            $options = [];
            foreach ($element->getElementsByTagName('option') as $option) {
                $text = trim($option->textContent);
                $value = $option->getAttribute('value') ?: Str::slug($text);
                if ($text !== '' && $value !== '') {
                    $options[] = ['label' => mb_substr($text, 0, 150), 'value' => substr(Str::slug($value), 0, 150)];
                }
            }
            $role = in_array($type, ['email', 'tel'], true) ? ($type === 'email' ? 'email' : 'phone') : (in_array($key, ['name', 'full_name', 'ad_soyad'], true) ? 'name' : '');
            if (in_array($role, $roles, true)) {
                $role = '';
            }
            if ($role) {
                $roles[] = $role;
            }
            $fields[] = [
                'key' => $key, 'label' => mb_substr($label, 0, 150), 'input_type' => $type, 'required' => $element->hasAttribute('required'),
                'placeholder' => mb_substr($element->getAttribute('placeholder'), 0, 255), 'description' => '', 'width' => 'full',
                'css_class' => '', 'input_class' => preg_replace('/[^a-zA-Z0-9_ :\\-]/', '', $element->getAttribute('class')),
                'role' => $role, 'options' => $options, 'max_length' => null, 'max_size' => 5120, 'extensions' => 'pdf,docx',
            ];
        }

        return ['fields' => $fields, 'warnings' => array_values(array_unique($warnings))];
    }
}
