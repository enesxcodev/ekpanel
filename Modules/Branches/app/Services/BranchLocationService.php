<?php

namespace Modules\Branches\Services;

use Illuminate\Database\Eloquent\Collection;
use Modules\Konum\Models\LocationRecord;
use Modules\Konum\Services\KonumFrontendService;

class BranchLocationService
{
    private const ENTITY_FIELDS = [
        'cities' => 'city_id',
        'towns' => 'town_id',
        'districts' => 'district_id',
        'neighbourhoods' => 'neighbourhood_id',
        'streets' => 'street_id',
    ];

    private const PARENT_FIELDS = [
        'cities' => [],
        'towns' => ['city_id'],
        'districts' => ['city_id', 'town_id'],
        'neighbourhoods' => ['city_id', 'town_id', 'district_id'],
        'streets' => ['city_id', 'town_id', 'district_id', 'neighbourhood_id'],
    ];

    public function __construct(private KonumFrontendService $locations) {}

    public function ready(): bool
    {
        return $this->locations->ready();
    }

    /** @param array<string, mixed> $selection */
    public function isValidSelection(array $selection): bool
    {
        $selected = [];
        foreach (self::ENTITY_FIELDS as $entity => $field) {
            $value = $selection[$field] ?? null;
            if ($value === null || $value === '') {
                continue;
            }
            foreach (self::PARENT_FIELDS[$entity] as $parentField) {
                if (empty($selection[$parentField])) {
                    return false;
                }
            }
            $selected[$entity] = (int) $value;
        }

        if ($selected === []) {
            return true;
        }
        if (! $this->locations->ready()) {
            return false;
        }

        foreach ($selected as $entity => $id) {
            $parents = array_intersect_key($selection, array_flip(self::PARENT_FIELDS[$entity]));
            $query = $this->locations->query($entity, $parents);
            if ($query === null || ! $query->whereKey($id)->exists()) {
                return false;
            }
        }

        return true;
    }

    /** @param array<string, mixed> $parents
     * @return Collection<int, LocationRecord>
     */
    public function options(string $entity, array $parents, ?int $selectedId = null, string $search = ''): Collection
    {
        foreach (self::PARENT_FIELDS[$entity] ?? [] as $parentField) {
            if (empty($parents[$parentField])) {
                return new Collection;
            }
        }
        $query = $this->locations->query($entity, $parents);
        if ($query === null) {
            return new Collection;
        }

        if ($search !== '') {
            $query->where('name', 'like', mb_substr($search, 0, 100).'%');
        }

        if ($selectedId !== null) {
            $query->whereKey($selectedId);
        }

        return $query->orderBy('name')->orderBy('id')->limit($selectedId === null ? 100 : 1)->get();
    }

    /** @return array<string, string> */
    public function parentsFor(string $entity, array $selection): array
    {
        return array_intersect_key($selection, array_flip(self::PARENT_FIELDS[$entity] ?? []));
    }

    /** @return array<string, string> */
    public function fields(): array
    {
        return self::ENTITY_FIELDS;
    }
}
