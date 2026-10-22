<?php

namespace App\Services\Analytics;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class VisitAnalytics
{
    public const SITE_SCOPE = '__site__';

    public function record(string $ipAddress, string $path, string $visitorType, string $deviceType): void
    {
        $key = (string) config('app.key');

        if ($key === '') {
            throw new \RuntimeException('Application key is required for visit deduplication.');
        }

        $identityHash = hash_hmac('sha256', $ipAddress, $key);
        $now = CarbonImmutable::now();

        foreach ([self::SITE_SCOPE, $path] as $scopeKey) {
            $scopeHash = hash_hmac('sha256', $scopeKey, $key);
            $this->recordIfNew($identityHash, $scopeHash, $scopeKey, $visitorType, $deviceType, $now);
        }
    }

    /**
     * @return array{inserted: bool, refreshed: bool}
     */
    public function reserve(string $identityHash, string $scopeHash, CarbonImmutable $now): array
    {
        return DB::transaction(function () use ($identityHash, $scopeHash, $now): array {
            $inserted = DB::table('visit_analytics_guards')->insertOrIgnore([
                'identity_hash' => $identityHash,
                'scope_hash' => $scopeHash,
                'expires_at' => $now->addMinutes(30),
            ]);

            if ($inserted > 0) {
                return ['inserted' => true, 'refreshed' => false];
            }

            $refreshed = DB::table('visit_analytics_guards')
                ->where('identity_hash', $identityHash)
                ->where('scope_hash', $scopeHash)
                ->where('expires_at', '<=', $now)
                ->update(['expires_at' => $now->addMinutes(30)]);

            return ['inserted' => false, 'refreshed' => $refreshed > 0];
        }, 3);
    }

    public function increment(CarbonImmutable $date, string $scopeKey, string $visitorType, string $deviceType): void
    {
        $row = [
            'stat_date' => $date->toDateString(),
            'scope_key' => $scopeKey,
            'visitor_type' => $visitorType,
            'device_type' => $deviceType,
        ];
        $inserted = DB::table('visit_analytics')->insertOrIgnore($row + [
            'visit_count' => 1,
            'created_at' => $date,
            'updated_at' => $date,
        ]);

        if ($inserted === 0) {
            DB::table('visit_analytics')
                ->where($row)
                ->increment('visit_count', 1, ['updated_at' => $date]);
        }
    }

    public function pruneGuards(CarbonImmutable $now, int $limit = 5000): int
    {
        $deleted = 0;

        do {
            $ids = DB::table('visit_analytics_guards')
                ->where('expires_at', '<=', $now)
                ->orderBy('id')
                ->limit($limit)
                ->pluck('id');

            if ($ids->isNotEmpty()) {
                $deleted += DB::table('visit_analytics_guards')->whereIn('id', $ids)->delete();
            }
        } while ($ids->count() === $limit);

        return $deleted;
    }

    public function pruneStats(CarbonImmutable $before): int
    {
        return DB::table('visit_analytics')->where('stat_date', '<', $before->toDateString())->delete();
    }

    /**
     * @return array{
     *     months: array<int, string>,
     *     visitors: array<string, array<int, int>>,
     *     devices: array<string, array<int, int>>,
     *     popularPages: array<int, array{path: string, visits: int, trend: array<int, int>}>
     * }
     */
    public function dashboardData(int $year, string $device = 'all'): array
    {
        $start = CarbonImmutable::create($year, 1, 1)->startOfDay();
        $end = $start->endOfYear()->endOfDay();
        $months = ['Oca', 'Şub', 'Mar', 'Nis', 'May', 'Haz', 'Tem', 'Ağu', 'Eyl', 'Eki', 'Kas', 'Ara'];
        $visitorSeries = ['guest' => array_fill(1, 12, 0), 'user' => array_fill(1, 12, 0)];
        $deviceSeries = ['computer' => array_fill(1, 12, 0), 'phone' => array_fill(1, 12, 0), 'other' => array_fill(1, 12, 0)];

        $siteRows = DB::table('visit_analytics')
            ->where('scope_key', self::SITE_SCOPE)
            ->whereBetween('stat_date', [$start->toDateString(), $end->toDateString()])
            ->get(['stat_date', 'visitor_type', 'device_type', 'visit_count']);

        foreach ($siteRows as $row) {
            $month = (int) CarbonImmutable::parse($row->stat_date)->format('n');
            $visitorSeries[$row->visitor_type][$month] += (int) $row->visit_count;
            if ($device === 'all' || $device === $row->device_type) {
                $deviceSeries[$row->device_type][$month] += (int) $row->visit_count;
            }
        }

        $topPages = DB::table('visit_analytics')
            ->where('scope_key', '!=', self::SITE_SCOPE)
            ->whereBetween('stat_date', [$start->toDateString(), $end->toDateString()])
            ->select('scope_key', DB::raw('SUM(visit_count) as total_visits'))
            ->groupBy('scope_key')
            ->orderByDesc('total_visits')
            ->limit(20)
            ->get();
        $topPaths = $topPages->pluck('scope_key')->all();
        $trends = [];

        if ($topPaths !== []) {
            $trendRows = DB::table('visit_analytics')
                ->whereIn('scope_key', $topPaths)
                ->whereBetween('stat_date', [$start->toDateString(), $end->toDateString()])
                ->select('scope_key', 'stat_date', DB::raw('SUM(visit_count) as total_visits'))
                ->groupBy('scope_key', 'stat_date')
                ->get();

            foreach ($trendRows as $row) {
                $month = (int) CarbonImmutable::parse($row->stat_date)->format('n');
                $trends[$row->scope_key][$month] = ($trends[$row->scope_key][$month] ?? 0) + (int) $row->total_visits;
            }
        }

        $popularPages = $topPages->map(fn (object $row): array => [
            'path' => $row->scope_key,
            'visits' => (int) $row->total_visits,
            'trend' => array_replace(array_fill(1, 12, 0), $trends[$row->scope_key] ?? []),
        ])->all();

        return [
            'months' => $months,
            'visitors' => $visitorSeries,
            'devices' => $deviceSeries,
            'popularPages' => $popularPages,
        ];
    }

    public function siteTotal(CarbonImmutable $from, CarbonImmutable $to): int
    {
        return (int) DB::table('visit_analytics')
            ->where('scope_key', self::SITE_SCOPE)
            ->whereBetween('stat_date', [$from->toDateString(), $to->toDateString()])
            ->sum('visit_count');
    }

    private function recordIfNew(
        string $identityHash,
        string $scopeHash,
        string $scopeKey,
        string $visitorType,
        string $deviceType,
        CarbonImmutable $now,
    ): void {
        DB::transaction(function () use ($identityHash, $scopeHash, $scopeKey, $visitorType, $deviceType, $now): void {
            $reservation = $this->reserve($identityHash, $scopeHash, $now);

            if ($reservation['inserted'] || $reservation['refreshed']) {
                $this->increment($now, $scopeKey, $visitorType, $deviceType);
            }
        }, 3);
    }
}
