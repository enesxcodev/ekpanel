<?php

namespace Modules\Branches\Services;

use App\Models\Settings\Language;
use Illuminate\Support\Facades\DB;
use Modules\Branches\Models\Branch;
use Modules\Branches\Models\BranchCategory;

class BranchService
{
    /** @param array<string, mixed> $data */
    public function saveCategory(array $data, ?BranchCategory $category = null): BranchCategory
    {
        return DB::transaction(function () use ($data, $category): BranchCategory {
            $language = Language::query()->whereKey($data['lang_id'] ?? null)->where('is_active', true)->firstOrFail();
            $isNew = $category === null;
            $category = $category === null ? new BranchCategory : BranchCategory::query()->lockForUpdate()->findOrFail($category->id);
            if ($isNew) {
                $category->fill([
                    'slug' => $data['slug'], 'sort_order' => $data['sort_order'], 'is_active' => (bool) $data['is_active'],
                    'name' => $data['name'],
                ])->save();
            }
            $translation = $category->translations()->firstOrNew(['lang_id' => $language->id]);
            $translation->name = $data['name'];
            $translation->save();
            if ($language->code === BranchesLanguageService::PrimaryCode) {
                $category->forceFill(['name' => $data['name']])->save();
            }
            $category->setRelation('translations', collect([$translation]));

            return $category;
        });
    }

    /** @param array<string, mixed> $data */
    public function save(array $data, ?Branch $branch = null): Branch
    {
        return DB::transaction(function () use ($data, $branch): Branch {
            $branch = $branch === null ? new Branch : Branch::query()->lockForUpdate()->findOrFail($branch->id);
            $language = Language::query()->whereKey($data['lang_id'] ?? null)->where('is_active', true)->firstOrFail();
            $branch->fill([
                'branch_category_id' => $data['branch_category_id'],
                ...($branch->exists ? [] : ['name' => $data['name']]),
                'city_id' => $data['city_id'] ?? null,
                'town_id' => $data['town_id'] ?? null,
                'district_id' => $data['district_id'] ?? null,
                'neighbourhood_id' => $data['neighbourhood_id'] ?? null,
                'street_id' => $data['street_id'] ?? null,
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'sort_order' => $data['sort_order'] ?? 0,
                'is_active' => (bool) $data['is_active'],
            ])->save();

            $translation = $branch->translations()->firstOrNew(['lang_id' => $language->id]);
            $translation->fill([
                'name' => $data['name'],
                'address_detail' => $data['address_detail'] ?? null,
                'phone' => $data['phone'] ?? null,
                'email' => $data['email'] ?? null,
                'website' => $data['website'] ?? null,
                'working_hours' => $data['working_hours'] ?? null,
                'map_url' => $data['map_url'] ?? null,
            ])->save();
            if ($language->code === BranchesLanguageService::PrimaryCode) {
                $branch->forceFill([
                    'name' => $translation->name, 'address_detail' => $translation->address_detail, 'phone' => $translation->phone,
                    'email' => $translation->email, 'website' => $translation->website, 'working_hours' => $translation->working_hours,
                    'map_url' => $translation->map_url,
                ])->save();
            }
            $branch->setRelation('translations', collect([$translation]));

            return $branch;
        });
    }

    public function delete(Branch $branch): void
    {
        DB::transaction(function () use ($branch): void {
            Branch::query()->lockForUpdate()->findOrFail($branch->id)->delete();
        });
    }
}
