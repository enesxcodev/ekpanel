<?php

namespace Modules\Konum\Console;

use Illuminate\Console\Command;
use Modules\Konum\Services\KonumImportService;
use Throwable;

class ImportKonum extends Command
{
    protected $signature = 'konum:import {--source-dir= : Doğrulanmış SQL dosyalarının klasörü}';

    protected $description = 'Sabit Türkiye adres kaynağını aktarır ve bütünlüğünü doğrular.';

    public function handle(KonumImportService $importer): int
    {
        try {
            $report = $importer->import($this->option('source-dir') ?: null, function (string $table, int $count): void {
                $this->line($table.': '.$count);
            });
            $this->line(json_encode($report, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE));
            $quarantined = array_sum(array_column($report, 'quarantined_count'));
            if ($quarantined > 0) {
                $this->warn('Aktarım doğrulandı. Kaynak ilişkileri tutarsız '.$quarantined.' kayıt korundu ve seçimlerden çıkarıldı.');
            } else {
                $this->info('Konum aktarımı ve doğrulaması tamamlandı.');
            }

            return self::SUCCESS;
        } catch (Throwable $exception) {
            report($exception);
            $this->error($exception->getMessage());

            return self::FAILURE;
        }
    }
}
