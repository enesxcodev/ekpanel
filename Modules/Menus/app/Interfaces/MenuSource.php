<?php

namespace Modules\Menus\Interfaces;

interface MenuSource
{
    public function label(): string;

    /** @return list<array{value: string, label: string}> */
    public function options(): array;

    /** @return array{label: string, url: ?string, error: ?string}|null */
    public function find(string $id): ?array;

    public function supportsChildren(): bool;

    /** @return list<array{label: string, url: string, new_tab: bool, children: array}> */
    public function children(string $id): array;
}
