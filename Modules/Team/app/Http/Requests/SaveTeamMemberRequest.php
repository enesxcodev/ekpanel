<?php

namespace Modules\Team\Http\Requests;

use App\Services\Fields\FeatureValues;
use App\Services\Fields\FieldRuleBuilder;
use App\Services\Fields\ModuleFeatures;
use Illuminate\Foundation\Http\FormRequest;

class SaveTeamMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin')?->can($this->route('member') ? 'team.update' : 'team.create') ?? false;
    }

    public function rules(ModuleFeatures $features, FeatureValues $values): array
    {
        $member = $this->route('member');

        return [
            'name' => ['required', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'prohibited_if:remove_image,1', 'image', 'mimes:jpg,jpeg,png,webp', 'extensions:jpg,jpeg,png,webp', 'max:4096'],
            'remove_image' => ['sometimes', 'boolean'],
            'is_active' => ['required', 'boolean'],
            ...$values->rules($features->form('Team', 'member')['fields'], $this->all(), $member?->extra_fields ?? []),
        ];
    }

    public function messages(): array
    {
        return ['image.prohibited_if' => 'Yeni görsel yükleme ve mevcut görseli kaldırma işlemlerinden birini seçin.']
            + (new FieldRuleBuilder)->messages();
    }

    public function attributes(): array
    {
        return ['name' => 'İsim', 'title' => 'Ünvan', 'image' => 'Görsel', 'is_active' => 'Durum', 'remove_image' => 'Görseli kaldır']
            + app(FeatureValues::class)->labels(app(ModuleFeatures::class)->form('Team', 'member')['fields']);
    }
}
