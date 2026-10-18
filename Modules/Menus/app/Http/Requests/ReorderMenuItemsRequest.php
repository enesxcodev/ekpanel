<?php

namespace Modules\Menus\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

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
            'items' => ['present', 'array', 'max:10000'],
            'items.*' => ['required', 'array:id,parent_id,sort_order'],
            'items.*.id' => ['required', 'integer', 'distinct'],
            'items.*.parent_id' => ['present', 'nullable', 'integer'],
            'items.*.sort_order' => ['required', 'integer', 'min:0', 'max:65535'],
        ];
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
