<?php

namespace Modules\Konum\Services;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class KonumVerificationService
{
    public function __construct(private PolygonGeometry $geometry) {}

    public function classify(): void
    {
        foreach (KonumSource::TABLES as $table) {
            DB::table('konum_'.$table)->update(['is_consistent' => false]);
            $this->consistentQuery($table)->update(['is_consistent' => true]);
        }
    }

    private function consistentQuery(string $table): Builder
    {
        $query = DB::table('konum_'.$table);
        $ancestors = [];
        foreach (KonumFrontendService::PARENTS as $column => $parent) {
            if ($parent === $table) {
                break;
            }
            $query->whereExists(function (Builder $parentQuery) use ($table, $column, $parent, $ancestors): void {
                $parentQuery->selectRaw('1')->from('konum_'.$parent.' as parent')
                    ->whereColumn('parent.id', 'konum_'.$table.'.'.$column)->where('parent.is_consistent', true);
                foreach ($ancestors as $ancestor) {
                    $parentQuery->whereColumn('parent.'.$ancestor, 'konum_'.$table.'.'.$ancestor);
                }
            });
            $ancestors[] = $column;
        }

        return $query;
    }

    /** @param array<string, mixed>|null $expected
     * @return array<string, mixed>
     */
    public function verify(?array $expected = null): array
    {
        if ($expected === null) {
            $stored = DB::table('konum_imports')->where('source_commit', config('konum.source.commit'))->first();
            if ($stored === null || ! in_array($stored->status, ['complete', 'warnings'], true)) {
                throw new RuntimeException('Tamamlanmış import bulunamadı.');
            }
            $expected = json_decode($stored->report, true, 512, JSON_THROW_ON_ERROR);
        }
        $parents = [];
        $report = [];
        foreach (KonumSource::TABLES as $table) {
            $query = DB::table('konum_'.$table);
            $count = $query->count();
            $digest = hash_init('sha256');
            $polygons = 0;
            foreach ($query->orderBy('id')->lazyById(100) as $record) {
                $row = (array) $record;
                $fingerprint = KonumImportService::fingerprint($row);
                if (! hash_equals($row['source_row_hash'], $fingerprint)) {
                    throw new RuntimeException('Kayıt içerik doğrulaması başarısız: '.$table.' #'.$row['id']);
                }
                hash_update($digest, $fingerprint);
                if (! empty($row['polygons'])) {
                    $this->geometry->decode($row['polygons']);
                    $polygons++;
                }
            }
            $actualDigest = hash_final($digest);
            if ($count !== ($expected[$table]['source_count'] ?? null) || ! hash_equals($expected[$table]['source_digest'] ?? '', $actualDigest)) {
                throw new RuntimeException('Kaynak/hedef sayısı veya özeti uyuşmuyor: '.$table);
            }
            $orphans = 0;
            $mismatches = 0;
            foreach ($parents as $column => $parent) {
                $joined = DB::table('konum_'.$table.' as child')->leftJoin('konum_'.$parent.' as parent', 'child.'.$column, '=', 'parent.id');
                $orphans += (clone $joined)->whereNull('parent.id')->count();
                foreach (array_keys($parents) as $ancestor) {
                    if ($ancestor === $column) {
                        break;
                    }
                    $mismatches += (clone $joined)->whereColumn('child.'.$ancestor, '!=', 'parent.'.$ancestor)->count();
                }
            }
            $consistentQuery = $this->consistentQuery($table);
            $consistentCount = (clone $consistentQuery)->count();
            $flaggedCount = DB::table('konum_'.$table)->where('is_consistent', true)->count();
            if ($consistentCount !== $flaggedCount || (clone $consistentQuery)->where('is_consistent', true)->count() !== $consistentCount) {
                throw new RuntimeException('Konum hiyerarşisi karantina doğrulaması başarısız: '.$table);
            }
            $report[$table] = [...$expected[$table], 'target_count' => $count, 'target_digest' => $actualDigest, 'orphans' => $orphans, 'hierarchy_mismatches' => $mismatches, 'duplicate_ids' => 0, 'polygons_verified' => $polygons, 'quarantined_count' => $count - $consistentCount, 'usable_count' => $consistentCount];
            $parents[['cities' => 'city_id', 'towns' => 'town_id', 'districts' => 'district_id', 'neighbourhoods' => 'neighbourhood_id', 'streets' => 'street_id'][$table]] = $table;
        }

        return $report;
    }
}
