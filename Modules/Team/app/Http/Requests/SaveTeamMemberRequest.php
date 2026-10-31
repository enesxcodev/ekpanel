<?php

namespace Modules\Team\Http\Requests;

use App\Models\Settings\Language;
use App\Services\Fields\FeatureValues;
use App\Services\Fields\FieldRuleBuilder;
use App\Services\Fields\ModuleFeatures;
use App\Services\Media\ContentMediaService;
use Illuminate\Foundation\Http\FormRequest;
use Modules\Team\Services\TeamLanguageService;

class SaveTeamMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin')?->can($this->route('member') ? 'team.update' : 'team.create') ?? false;
    }

    public function rules(ModuleFeatures $features, FeatureValues $values, ContentMediaService $media): array
    {
        $member = $this->route('member');
        $language = is_numeric($this->input('lang_id')) ? Language::query()->find((int) $this->input('lang_id')) : null;
        $existingExtraFields = [];
        if ($member !== null && is_numeric($this->input('lang_id'))) {
            $existingExtraFields = $member->translations()->where('lang_id', (int) $this->input('lang_id'))->value('extra_fields') ?? [];
            if (is_string($existingExtraFields)) {
                $existingExtraFields = json_decode($existingExtraFields, true) ?: [];
            }
        }

        return [
            'lang_id' => ['required', 'integer', 'exists:languages,id', function (string $attribute, mixed $value, \Closure $fail): void {
                $language = Language::query()->where('is_active', true)->find($value);
                if ($language === null || (! $this->route('member') && $language->code !== TeamLanguageService::PrimaryCode)) {
                    $fail('Yeni ekip üyesi Türkçe oluşturulmalıdır; yalnızca aktif dil seçilebilir.');
                }
            }],
            'name' => ['required', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            ...($this->route('member') && $language?->code !== 'tr' ? [] : $media->rules($media->definition())),
            ...($this->route('member') && $language?->code !== 'tr' ? [] : ['is_active' => ['required', 'boolean']]),
            ...$values->rules($features->form('Team', 'member')['fields'], $this->all(), $existingExtraFields),
        ];
    }

    public function messages(): array
    {
        return ['image.prohibited_if' => 'Yeni görsel yükleme ve mevcut görseli kaldırma işlemlerinden birini seçin.']
            + (new FieldRuleBuilder)->messages();
    }

    public function attributes(): array
    {
        return ['name' => 'İsim', 'title' => 'Ünvan', 'image_media_id' => 'Görsel', 'is_active' => 'Durum', 'remove_image' => 'Görseli kaldır']
            + app(FeatureValues::class)->labels(app(ModuleFeatures::class)->form('Team', 'member')['fields']);
    }
}
