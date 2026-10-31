<?php

namespace Modules\Branches\Http\Requests;

use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Modules\Branches\Services\BranchLocationService;

class SaveBranchRequest extends FormRequest
{
    public function authorize(): bool
    {
        $permission = $this->route('branch') === null ? 'branches.create' : 'branches.update';

        return $this->user('admin')?->can($permission) ?? false;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'lang_id' => ['required', 'integer', Rule::exists('languages', 'id')->where('is_active', true)],
            'branch_category_id' => ['required', 'integer', Rule::exists('branch_categories', 'id')->where(function (Builder $query): void {
                $query->where('is_active', true);
                $branch = $this->route('branch');
                if ($branch !== null) {
                    $query->orWhere('id', $branch->branch_category_id);
                }
            })],
            'name' => ['required', 'string', 'max:255'],
            'city_id' => ['nullable', 'integer', 'min:1'],
            'town_id' => ['nullable', 'integer', 'min:1'],
            'district_id' => ['nullable', 'integer', 'min:1'],
            'neighbourhood_id' => ['nullable', 'integer', 'min:1'],
            'street_id' => ['nullable', 'integer', 'min:1'],
            'address_detail' => ['nullable', 'string', 'max:5000'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'website' => ['nullable', 'url', 'max:2048'],
            'working_hours' => ['nullable', 'string', 'max:255'],
            'map_url' => ['nullable', 'url', 'regex:/^https:\/\//i', 'max:2048'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90', 'required_with:longitude'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180', 'required_with:latitude'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function after(BranchLocationService $locations): array
    {
        return [function (Validator $validator) use ($locations): void {
            $selection = $this->only(array_values($locations->fields()));
            if (! $locations->isValidSelection($selection)) {
                $validator->errors()->add('city_id', 'Konum seçimlerini kontrol edin; seçilen her alt konum için üst konumlar da seçilmelidir.');
            }
        }];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'branch_category_id' => 'Kategori', 'name' => 'Ad', 'city_id' => 'İl', 'town_id' => 'İlçe',
            'district_id' => 'Ara bölge', 'neighbourhood_id' => 'Mahalle', 'street_id' => 'Cadde / sokak',
            'address_detail' => 'Adres detayı', 'phone' => 'Telefon', 'email' => 'E-posta', 'website' => 'Web sitesi',
            'working_hours' => 'Çalışma saatleri', 'map_url' => 'Harita bağlantısı', 'latitude' => 'Enlem',
            'longitude' => 'Boylam', 'sort_order' => 'Sıralama', 'is_active' => 'Durum',
        ];
    }
}
