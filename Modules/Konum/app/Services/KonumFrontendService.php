<?php

namespace Modules\Konum\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Konum\Models\City;
use Modules\Konum\Models\District;
use Modules\Konum\Models\LocationRecord;
use Modules\Konum\Models\Neighbourhood;
use Modules\Konum\Models\Street;
use Modules\Konum\Models\Town;
use Nwidart\Modules\Facades\Module;

class KonumFrontendService
{
    public const LABELS = ['cities' => 'İller', 'towns' => 'İlçeler', 'districts' => 'Ara bölgeler', 'neighbourhoods' => 'Mahalleler', 'streets' => 'Cadde / sokaklar'];

    public const PARENTS = ['city_id' => 'cities', 'town_id' => 'towns', 'district_id' => 'districts', 'neighbourhood_id' => 'neighbourhoods'];

    public const MODELS = ['cities' => City::class, 'towns' => Town::class, 'districts' => District::class, 'neighbourhoods' => Neighbourhood::class, 'streets' => Street::class];

    public function ready(): bool
    {
        if (! Module::find('Konum')?->isEnabled() || ! Schema::hasTable('konum_imports')) {
            return false;
        }
        foreach (KonumSource::TABLES as $table) {
            if (! Schema::hasTable('konum_'.$table)) {
                return false;
            }
        }

        return DB::table('konum_imports')->where('source_commit', config('konum.source.commit'))->whereIn('status', ['complete', 'warnings'])->exists();
    }

    /** @param array<string, int|string> $parents
     * @return Builder<LocationRecord>|null
     */
    public function query(string $entity, array $parents = []): ?Builder
    {
        if (! isset(self::MODELS[$entity]) || ! $this->ready()) {
            return null;
        }
        $model = self::MODELS[$entity];
        $columns = ['id', 'name', 'is_active'];
        $filters = [];
        foreach (self::PARENTS as $column => $parent) {
            if ($parent === $entity) {
                break;
            }
            $columns[] = $column;
            if (isset($parents[$column])) {
                if (filter_var($parents[$column], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) === false) {
                    return null;
                }
                $filters[$column] = (int) $parents[$column];
            }
        }
        if (in_array($entity, ['districts', 'neighbourhoods'], true)) {
            $columns[] = 'zip_code';
        }
        if ($entity === 'cities') {
            $columns[] = 'plate_code';
        }

        return $model::query()->select($columns)->where('is_active', true)->where('is_consistent', true)->where($filters);
    }

    /** @param array<string, int|string> $parents
     * @return Collection<int, LocationRecord>
     */
    public function search(string $entity, array $parents = [], string $search = '', int $limit = 25): Collection
    {
        $query = $this->query($entity, $parents);
        if ($query === null) {
            return new Collection;
        }
        if ($search !== '') {
            $query->where('name', 'like', mb_substr($search, 0, 100).'%');
        }

        return $query->orderBy('name')->orderBy('id')->limit(max(1, min(100, $limit)))->get();
    }
}
