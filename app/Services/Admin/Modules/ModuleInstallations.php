<?php

namespace App\Services\Admin\Modules;

use App\Jobs\InstallQueuedModule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use RuntimeException;
use stdClass;

class ModuleInstallations
{
    public const STEPS = 12;

    private function records(): Builder
    {
        return DB::table('module_installations');
    }

    /** @param list<array<string, mixed>> $entries */
    public function enqueue(array $entries, int $actor): string
    {
        $ordered = [];
        while ($entries !== []) {
            $remaining = array_column($entries, 'id');
            $progress = false;
            foreach ($entries as $key => $entry) {
                if (array_intersect(array_keys($entry['requires']['modules']), $remaining) !== []) {
                    continue;
                }
                $ordered[] = $entry;
                unset($entries[$key]);
                $progress = true;
            }
            if (! $progress) {
                throw new RuntimeException('Seçilen modüller arasında döngüsel bağımlılık var.');
            }
        }

        return DB::transaction(function () use ($ordered, $actor): string {
            if ($this->records()->whereNotNull('active_key')->exists()) {
                throw new RuntimeException('Bir modül kurulumu zaten sırada veya çalışıyor. İlerlemeyi kurulum ekranından izleyin.');
            }
            $id = (string) Str::uuid();
            $results = array_map(fn (array $entry): array => [
                'id' => $entry['id'], 'name' => $entry['name'], 'status' => 'queued',
                'stage' => 'Sırada', 'completed' => 0, 'error' => null,
            ], $ordered);
            $this->records()->insert([
                'id' => $id, 'actor' => $actor, 'active_key' => 'installation', 'status' => 'queued',
                'current_index' => 0, 'entries' => json_encode($ordered, JSON_THROW_ON_ERROR),
                'results' => json_encode($results, JSON_THROW_ON_ERROR),
                'created_at' => now(), 'updated_at' => now(),
            ]);
            Queue::connection('module-installations')->push(new InstallQueuedModule($id, 0), '', 'module-installations');

            return $id;
        });
    }

    public function claim(string $id, int $index): bool
    {
        return $this->records()->where('id', $id)->where('status', 'queued')->where('current_index', $index)
            ->update(['status' => 'running', 'updated_at' => now()]) === 1;
    }

    /** @return array<string, mixed> */
    public function entry(string $id, int $index): array
    {
        $record = $this->records()->where('id', $id)->first();
        if (! $record || $record->status !== 'running' || (int) $record->current_index !== $index) {
            throw new RuntimeException('Kurulum işi artık çalışır durumda değil.');
        }

        return ['entry' => json_decode($record->entries, true, 64, JSON_THROW_ON_ERROR)[$index], 'actor' => $record->actor];
    }

    public function progress(string $id, int $index, int $completed, string $stage): void
    {
        DB::transaction(function () use ($id, $index, $completed, $stage): void {
            $record = $this->records()->where('id', $id)->lockForUpdate()->first();
            if (! $record || $record->status !== 'running' || (int) $record->current_index !== $index) {
                throw new RuntimeException('Kurulumun çalışma kaydı değişti; işlem durduruldu.');
            }
            $results = json_decode($record->results, true, 64, JSON_THROW_ON_ERROR);
            $results[$index]['status'] = 'running';
            $results[$index]['completed'] = max($results[$index]['completed'], min(self::STEPS - 1, $completed));
            $results[$index]['stage'] = $stage;
            $this->records()->where('id', $id)->update(['results' => json_encode($results, JSON_THROW_ON_ERROR), 'updated_at' => now()]);
        });
    }

    public function finish(string $id, int $index, ?string $error = null): void
    {
        DB::transaction(function () use ($id, $index, $error): void {
            $record = $this->records()->where('id', $id)->lockForUpdate()->first();
            if (! $record || $record->status !== 'running' || (int) $record->current_index !== $index) {
                return;
            }
            $entries = json_decode($record->entries, true, 64, JSON_THROW_ON_ERROR);
            $results = json_decode($record->results, true, 64, JSON_THROW_ON_ERROR);
            $results[$index]['status'] = $error === null ? 'succeeded' : 'failed';
            $results[$index]['error'] = $error;
            if ($error === null) {
                $results[$index]['completed'] = self::STEPS;
                $results[$index]['stage'] = 'Tamamlandı';
            }
            $next = $index + 1;
            while (isset($entries[$next])) {
                $failedDependencies = array_column(array_filter($results, fn (array $result): bool => in_array($result['status'], ['failed', 'skipped'], true)), 'id');
                if (array_intersect(array_keys($entries[$next]['requires']['modules']), $failedDependencies) === []) {
                    break;
                }
                $results[$next]['status'] = 'skipped';
                $results[$next]['stage'] = 'Bağımlı modül kurulamadığı için atlandı';
                $results[$next]['error'] = 'Önce bağımlı modülün kurulumunu tamamlayın.';
                $next++;
            }
            $finished = ! isset($entries[$next]);
            $hasFailure = count(array_filter($results, fn (array $result): bool => in_array($result['status'], ['failed', 'skipped'], true))) > 0;
            $this->records()->where('id', $id)->update([
                'results' => json_encode($results, JSON_THROW_ON_ERROR), 'current_index' => $next,
                'status' => $finished ? ($hasFailure ? 'failed' : 'succeeded') : 'queued',
                'active_key' => $finished ? null : 'installation',
                'error' => $hasFailure ? 'Bazı modüller kurulamadı. Ayrıntıları inceleyin. Yarım kalan kurulu modüller için Etkinleştir işlemini kullanın; veriler korunur.' : null,
                'updated_at' => now(),
            ]);
            if (! $finished) {
                Queue::connection('module-installations')->push(new InstallQueuedModule($id, $next), '', 'module-installations');
            }
        });
    }

    public function interrupt(string $id, int $index, string $message): void
    {
        DB::transaction(function () use ($id, $index, $message): void {
            $this->records()->where('id', $id)->where('status', 'queued')->where('current_index', $index)
                ->update(['status' => 'running']);
            $this->finish($id, $index, $message);
        });
    }

    public function assertIdle(): void
    {
        if ($this->records()->whereNotNull('active_key')->exists()) {
            throw new RuntimeException('Modül kurulumu sürerken diğer modül işlemleri beklemelidir.');
        }
    }

    /** @return array<string, mixed>|null */
    public function latest(): ?array
    {
        $record = $this->records()->orderByRaw('active_key IS NULL')->orderByDesc('created_at')->first();

        return $record ? $this->present($record) : null;
    }

    /** @return array<string, mixed>|null */
    public function find(string $id): ?array
    {
        $record = $this->records()->where('id', $id)->first();

        return $record ? $this->present($record) : null;
    }

    /** @return array<string, mixed> */
    private function present(stdClass $record): array
    {
        if ($record->status === 'running' && now()->diffInSeconds($record->updated_at, absolute: true) > 3600) {
            $this->interrupt($record->id, (int) $record->current_index, 'Kuyruk çalışanı kesildi veya süre sınırı aşıldı. Otomatik tekrar yapılmadı.');

            return $this->find($record->id);
        }
        $results = json_decode($record->results, true, 64, JSON_THROW_ON_ERROR);
        $percentage = (int) floor(array_sum(array_column($results, 'completed')) / (count($results) * self::STEPS) * 100);
        $current = $results[$record->current_index] ?? null;

        return [
            'id' => $record->id, 'status' => $record->status,
            'percentage' => $record->status === 'succeeded' ? 100 : min(99, $percentage),
            'stage' => $current['stage'] ?? ($record->status === 'succeeded' ? 'Tüm modüller kuruldu' : 'Kurulum tamamlandı; hataları inceleyin'),
            'error' => $record->error, 'results' => $results, 'updated_at' => $record->updated_at,
        ];
    }
}
