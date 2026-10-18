<?php

namespace Modules\Menus\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use JsonException;
use Modules\Menus\Models\MenuItem;
use Modules\Menus\Services\MenuSources;
use Modules\Menus\Services\NavigationService;

class SaveMenuItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin')?->can('menus.update') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $json = $this->input('route_parameters_json');
        if ($this->input('target_type') === 'route' && is_string($json)) {
            try {
                $this->merge(['route_parameters' => json_decode($json, true, 16, JSON_THROW_ON_ERROR)]);
            } catch (JsonException) {
                $this->merge(['route_parameters' => $json]);
            }
        }
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $type = $this->input('target_type');

        return [
            'icon' => ['nullable', 'string', 'max:100', 'regex:/^[a-z][a-z0-9-]*$/'],
            'label' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'integer', Rule::exists('menu_items', 'id')->where('menu_id', $this->route('menu')->id)],
            'sort_order' => ['required', 'integer', 'min:0', 'max:65535'],
            'is_active' => ['required', 'boolean'],
            'new_tab' => ['sometimes', 'boolean'],
            'target_type' => ['required', Rule::in(['group', 'url', 'route', 'anchor', 'content'])],
            'url' => [Rule::excludeIf(! in_array($type, ['url', 'anchor'], true)), Rule::requiredIf($type === 'url'), 'nullable', 'string', 'max:2048'],
            'route_name' => [Rule::excludeIf($type !== 'route'), 'required', 'string', 'max:255'],
            'route_parameters' => [Rule::excludeIf($type !== 'route'), 'nullable', 'array', 'max:20'],
            'route_parameters_json' => [Rule::excludeIf($type !== 'route'), 'nullable', 'string', 'max:8192'],
            'route_parameters.*' => ['nullable', function (string $attribute, mixed $value, \Closure $fail): void {
                if (! is_scalar($value) && $value !== null) {
                    $fail('Route parametreleri yalnızca metin, sayı veya mantıksal değer olabilir.');
                }
            }],
            'anchor' => [Rule::excludeIf($type !== 'anchor'), 'required', 'string', 'max:255', 'regex:/^[A-Za-z][A-Za-z0-9_:.-]*$/'],
            'source' => [Rule::excludeIf($type !== 'content'), 'required', 'string', 'max:255'],
            'source_id' => [Rule::excludeIf($type !== 'content'), 'required', 'string', 'max:255'],
            'dynamic_children' => ['sometimes', 'boolean'],
        ];
    }

    /** @return list<callable(Validator): void> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }
            $navigation = app(NavigationService::class);
            $data = $validator->validated();
            if (! empty($data['url']) && $navigation->safeUrl($data['url']) === null) {
                $validator->errors()->add('url', 'Geçerli bir http(s) adresi veya güvenli site içi yol girin.');
            }
            $candidate = new MenuItem($data);
            $target = $navigation->target($candidate);
            if ($this->boolean('is_active') && $target['error'] !== null) {
                $validator->errors()->add('target_type', $target['error']);
            }
            if ($this->boolean('dynamic_children')) {
                $source = $data['target_type'] === 'content' ? app(MenuSources::class)->get($data['source']) : null;
                if ($source === null || ! $source->supportsChildren()) {
                    $validator->errors()->add('dynamic_children', 'Bu kaynak otomatik alt öğe listelemeyi desteklemiyor.');
                }
            }
        }];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'label.required' => 'Menü öğesinin etiketini girin.',
            'parent_id.exists' => 'Bu menüye ait bir üst öğe seçin.',
            'route_parameters.array' => 'Route parametrelerini geçerli bir JSON nesnesi olarak girin.',
            'anchor.regex' => 'Bölüm kimliği harfle başlamalı; yalnız harf, rakam, tire, alt çizgi, nokta ve iki nokta içermelidir.',
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'icon' => 'İkon', 'label' => 'Görünen metin', 'parent_id' => 'Üst öğe', 'sort_order' => 'Sıralama',
            'is_active' => 'Durum', 'new_tab' => 'Yeni sekme', 'target_type' => 'Hedef türü',
            'url' => 'Bağlantı adresi', 'route_name' => 'Site rotası',
            'route_parameters' => 'Rota parametreleri', 'route_parameters_json' => 'Rota parametreleri',
            'anchor' => 'Bölüm kimliği', 'source' => 'İçerik kaynağı', 'source_id' => 'İçerik',
            'dynamic_children' => 'Otomatik alt öğe listeleme',
        ];
    }
}
