<?php

namespace App\Console\Commands;

use App\Services\Analytics\VisitAnalytics;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class PruneVisitAnalytics extends Command
{
    protected $signature = 'analytics:prune-visits';

    protected $description = 'Remove expired visitor deduplication guards and old daily analytics.';

    public function handle(VisitAnalytics $analytics): int
    {
        $now = CarbonImmutable::now();
        $deletedGuards = $analytics->pruneGuards($now);
        $retainedFrom = $now->startOfMonth()->subMonths(24);
        $deletedStats = $analytics->pruneStats($retainedFrom);

        $this->info("Deleted {$deletedGuards} expired guards and {$deletedStats} old analytics rows.");

        return self::SUCCESS;
    }
}
