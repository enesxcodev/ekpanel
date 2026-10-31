<?php

namespace Modules\Menus\Http\Requests;

use App\Models\Settings\Language;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ReorderMenuItemsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user('admin')?->can('menus.update') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'lang_id' => ['required', 'integer', Rule::exists('languages', 'id')->where('is_active', true)],
            'items' => ['present', 'array', 'max:10000'],
            'items.*' => ['required', 'array:id,parent_id,sort_order'],
            'items.*.id' => ['required', 'integer', 'distinct'],
            'items.*.parent_id' => ['present', 'nullable', 'integer'],
            'items.*.sort_order' => ['required', 'integer', 'min:0', 'max:65535'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (! $this->exists('lang_id')) {
            $this->merge(['lang_id' => Language::query()->where('code', 'tr')->where('is_active', true)->value('id')]);
        }
    }

    /** @return list<callable(Validator): void> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            $code = $this->query('lang');
            if ($validator->errors()->isNotEmpty() || ! is_string($code) || $code === '') {
                return;
            }

            $language = Language::query()->where('code', $code)->where('is_active', true)->first();
            if ($language === null || (int) $this->input('lang_id') !== $language->id) {
                $validator->errors()->add('lang_id', 'Form dili ile istek dili eşleşmiyor. Sayfayı yenileyip tekrar deneyin.');
            }
        }];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'items.present' => 'Güncel menü yapısını gönderin.',
            'items.array' => 'Geçerli bir menü yapısı gönderin.',
            'items.*.array' => 'Öğelerde yalnız kimlik, üst öğe ve sıra bilgileri bulunabilir.',
            'items.*.id.distinct' => 'Aynı öğe menü yapısında birden fazla kez bulunamaz.',
            'items.*.id.required' => 'Her öğenin kimliği bulunmalıdır.',
            'items.*.parent_id.present' => 'Her öğe için üst öğe bilgisi gönderin.',
            'items.*.sort_order.required' => 'Her öğenin sırasını gönderin.',
            'items.*.sort_order.min' => 'Sıra sıfırdan küçük olamaz.',
            'items.*.sort_order.max' => 'Sıra 65535 değerini aşamaz.',
        ];
    }
}
