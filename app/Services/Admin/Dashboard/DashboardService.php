<?php

namespace App\Services\Admin\Dashboard;

use App\Models\Admin\Admin;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class DashboardService
{
    public function __construct(private DashboardWidgetRegistry $registry, private DashboardPreferenceService $preferences) {}

    /** @return array<string, array{definition: array<string, mixed>, widget: DashboardWidget}> */
    public function available(Admin $admin): array
    {
        return array_filter($this->registry->all(), function (array $entry) use ($admin): bool {
            foreach ($entry['definition']['permissions'] as $permission) {
                if (! $admin->can($permission)) {
                    return false;
                }
            }

            return true;
        });
    }

    /** @return array{definitions: array<string, array<string, mixed>>, selected: list<string>, revision: int} */
    public function state(Admin $admin): array
    {
        $available = $this->available($admin);
        $preference = $this->preferences->find($admin);
        $order = $preference === null
            ? array_keys(array_filter($available, fn (array $entry): bool => $entry['definition']['default']))
            : $preference['widget_order'];

        return [
            'definitions' => array_map(fn (array $entry): array => $entry['definition'], $available),
            'selected' => array_values(array_intersect($order, array_keys($available))),
            'revision' => $preference['revision'] ?? 0,
        ];
    }

    /** @param list<string> $ids
     * @return list<array{id: string, html: string, status: string}>
     */
    public function data(Admin $admin, array $ids): array
    {
        $available = $this->available($admin);
        $this->assertAccessible($ids, $available);
        $results = [];
        foreach ($ids as $id) {
            $entry = $available[$id];
            $status = 'ready';
            $result = null;
            try {
                $result = $entry['widget']->data($admin);
                if (! isset($result['value']) || (! is_int($result['value']) && ! is_string($result['value']))) {
                    throw new RuntimeException('Invalid widget result.');
                }
                if (isset($result['description']) && ! is_string($result['description'])) {
                    throw new RuntimeException('Invalid widget description.');
                }
                if (isset($result['url']) && (! is_string($result['url']) || ! str_starts_with($result['url'], '/') || str_starts_with($result['url'], '//'))) {
                    throw new RuntimeException('Widget link must be local.');
                }
            } catch (Throwable $exception) {
                $status = 'error';
                $result = null;
                Log::warning('Dashboard widget data failed.', ['widget' => $id, 'error' => $exception::class]);
            }
            $results[] = ['id' => $id, 'status' => $status, 'html' => view('components.admin.dashboard.widget', [
                'definition' => $entry['definition'], 'result' => $result, 'status' => $status,
            ])->render()];
        }

        return $results;
    }

    /** @param list<string> $ids
     * @return array{widget_order: list<string>, revision: int}
     */
    public function save(Admin $admin, array $ids, int $revision): array
    {
        $available = $this->available($admin);
        $this->assertAccessible($ids, $available);

        return $this->preferences->save($admin, $ids, $revision, array_keys($available));
    }

    /** @param list<string> $ids
     * @param  array<string, mixed>  $available
     */
    private function assertAccessible(array $ids, array $available): void
    {
        if (array_diff($ids, array_keys($available)) !== []) {
            throw ValidationException::withMessages(['widget_order' => 'Seçilen widget artık erişilebilir değil. Sayfayı yenileyin.']);
        }
    }
}
