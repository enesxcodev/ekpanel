<?php

namespace Modules\Menus\Interfaces;

interface LocalizedMenuSource
{
    /** @return list<array{value: string, label: string}> */
    public function optionsForLanguage(string $languageCode): array;

    /** @return array{label: string, url: ?string, error: ?string}|null */
    public function findForLanguage(string $id, string $languageCode): ?array;

    /** @return list<array{label: string, url: string, new_tab: bool, children: array}> */
    public function childrenForLanguage(string $id, string $languageCode): array;
}
