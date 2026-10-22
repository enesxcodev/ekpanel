<?php

namespace Modules\Konum\Services;

use Closure;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class KonumImportService
{
    public function __construct(private KonumSource $source, private SqlDumpReader $reader, private PolygonGeometry $geometry, private KonumVerificationService $verification) {}

    /** @return array<string, mixed> */
    public function import(?string $directory = null, ?Closure $progress = null): array
    {
        $connection = config('database.default');
        $database = config('database.connections.'.$connection.'.database');
        $lockPath = storage_path('app/private/konum-import-'.hash('sha256', $connection.'|'.$database).'.lock');
        if (! is_dir(dirname($lockPath))) {
            mkdir(dirname($lockPath), 0755, true);
        }
        $lock = fopen($lockPath, 'c');
        if ($lock === false || ! flock($lock, LOCK_EX | LOCK_NB)) {
            if (is_resource($lock)) {
                fclose($lock);
            }
            throw new RuntimeException('Başka bir Konum importu çalışıyor.');
        }
        $commit = config('konum.source.commit');
        try {
            $paths = $this->source->prepare($directory);
            DB::table('konum_imports')->updateOrInsert(['source_commit' => $commit], ['status' => 'running', 'error' => null, 'updated_at' => now(), 'created_at' => now()]);
            $report = [];
            foreach (KonumSource::TABLES as $table) {
                $count = 0;
                $previousId = 0;
                $batch = [];
                $digest = hash_init('sha256');
                foreach ($this->reader->rows($paths[$table], $table) as $row) {
                    $row = $this->normalize($row);
                    if ((int) $row['id'] <= $previousId) {
                        throw new RuntimeException('Kaynak kimlikleri sıralı ve benzersiz değil: '.$table);
                    }
                    $previousId = (int) $row['id'];
                    if (! empty($row['polygons'])) {
                        $this->geometry->decode($row['polygons']);
                    }
                    $row['source_row_hash'] = self::fingerprint($row);
                    hash_update($digest, $row['source_row_hash']);
                    $batch[] = $row;
                    $count++;
                    if (count($batch) === 500) {
                        DB::transaction(fn () => DB::table('konum_'.$table)->insertOrIgnore($batch));
                        $batch = [];
                    }
                }
                if ($batch !== []) {
                    DB::transaction(fn () => DB::table('konum_'.$table)->insertOrIgnore($batch));
                }
                if ($count === 0) {
                    throw new RuntimeException('Kaynak tablo boş: '.$table);
                }
                $report[$table] = ['source_count' => $count, 'source_digest' => hash_final($digest), 'sha256' => config('konum.source.files.'.$table.'.sha256')];
                $progress?->__invoke($table, $count);
                DB::table('konum_imports')->where('source_commit', $commit)->update(['report' => json_encode($report, JSON_THROW_ON_ERROR), 'updated_at' => now()]);
            }
            $this->verification->classify();
            $report = $this->verification->verify($report);
            $status = array_sum(array_column($report, 'quarantined_count')) === 0 ? 'complete' : 'warnings';
            DB::table('konum_imports')->where('source_commit', $commit)->update(['status' => $status, 'report' => json_encode($report, JSON_THROW_ON_ERROR), 'updated_at' => now()]);

            return $report;
        } catch (Throwable $exception) {
            DB::table('konum_imports')->where('source_commit', $commit)->update(['status' => 'failed', 'error' => mb_substr($exception->getMessage(), 0, 2000), 'updated_at' => now()]);
            throw $exception;
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }

    /** @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function normalize(array $row): array
    {
        if (isset($row['zip_code'])) {
            $row['zip_code'] = str_pad((string) $row['zip_code'], 5, '0', STR_PAD_LEFT);
            if (! preg_match('/^\d{5}$/', $row['zip_code'])) {
                throw new RuntimeException('Posta kodu geçersiz.');
            }
        }
        foreach (['towns', 'districts', 'neighbourhoods', 'streets', 'boundingbox'] as $column) {
            if (isset($row[$column])) {
                $row[$column] = json_encode(json_decode($row[$column], true, 512, JSON_THROW_ON_ERROR), JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }
        }

        return $row;
    }

    /** @param array<string, mixed> $row */
    public static function fingerprint(array $row): string
    {
        unset($row['source_row_hash'], $row['is_consistent']);
        ksort($row);
        foreach ($row as $key => $value) {
            if (in_array($key, ['towns', 'districts', 'neighbourhoods', 'streets', 'boundingbox'], true) && $value !== null) {
                $row[$key] = json_encode(json_decode($value, true, 512, JSON_THROW_ON_ERROR), JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            } else {
                $row[$key] = $value === null ? null : (string) $value;
            }
        }

        return hash('sha256', json_encode($row, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }
}
