<?php

namespace Modules\Branches\Services;

use Illuminate\Support\Facades\DB;
use Modules\Branches\Models\Branch;

class BranchService
{
    /** @param array<string, mixed> $data */
    public function save(array $data, ?Branch $branch = null): Branch
    {
        return DB::transaction(function () use ($data, $branch): Branch {
            $branch = $branch === null ? new Branch : Branch::query()->lockForUpdate()->findOrFail($branch->id);
            $branch->fill([
                'branch_category_id' => $data['branch_category_id'],
                'name' => $data['name'],
                'city_id' => $data['city_id'] ?? null,
                'town_id' => $data['town_id'] ?? null,
                'district_id' => $data['district_id'] ?? null,
                'neighbourhood_id' => $data['neighbourhood_id'] ?? null,
                'street_id' => $data['street_id'] ?? null,
                'address_detail' => $data['address_detail'] ?? null,
                'phone' => $data['phone'] ?? null,
                'email' => $data['email'] ?? null,
                'website' => $data['website'] ?? null,
                'working_hours' => $data['working_hours'] ?? null,
                'map_url' => $data['map_url'] ?? null,
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'sort_order' => $data['sort_order'] ?? 0,
                'is_active' => (bool) $data['is_active'],
            ])->save();

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
