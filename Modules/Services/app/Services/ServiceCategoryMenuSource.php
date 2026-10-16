<?php

namespace Modules\Services\Services;

use Modules\Menus\Interfaces\MenuSource;
use Modules\Services\Models\ServiceCategory;

class ServiceCategoryMenuSource implements MenuSource
{
    public function label(): string
    {
        return 'Hizmet kategorileri (bağlantısız grup)';
    }

    /** @return list<array{value: string, label: string}> */
    public function options(): array
    {
        return ServiceCategory::query()->orderBy('name')->orderBy('id')
            ->get(['id', 'name'])->map(fn (ServiceCategory $category): array => ['value' => (string) $category->id, 'label' => $category->name.' (grup başlığı)'])->all();
    }

    /** @return array{label: string, url: ?string, error: ?string}|null */
    public function find(string $id): ?array
    {
        $category = ServiceCategory::query()->find($id);

        return $category === null ? null : [
            'label' => $category->name,
            'url' => null,
            'error' => null,
        ];
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
