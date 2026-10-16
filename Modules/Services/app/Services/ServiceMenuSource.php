<?php

namespace Modules\Services\Services;

use Modules\Menus\Interfaces\MenuSource;
use Modules\Services\Models\Service;

class ServiceMenuSource implements MenuSource
{
    public function label(): string
    {
        return 'Hizmetler (genel bağlantı yok)';
    }

    /** @return list<array{value: string, label: string}> */
    public function options(): array
    {
        return Service::query()->where('is_active', true)->orderBy('title')->orderBy('id')
            ->get(['id', 'title'])->map(fn (Service $service): array => ['value' => (string) $service->id, 'label' => $service->title.' (genel bağlantı yok)'])->all();
    }

    /** @return array{label: string, url: ?string, error: ?string}|null */
    public function find(string $id): ?array
    {
        $service = Service::query()->find($id);

        if ($service === null) {
            return null;
        }

        return ['label' => $service->title, 'url' => null, 'error' => $service->is_active
            ? 'Hizmetler modülünde herkese açık detay route tanımlı değil. Özel URL veya bölüm bağlantısı kullanın.'
            : 'Hizmet devre dışı.'];
    }

    public function supportsChildren(): bool
    {
        return false;
    }

    /** @return list<array{label: string, url: string, new_tab: bool, children: array}> */
    public function children(string $id): array
    {
        return [];
    }
}
