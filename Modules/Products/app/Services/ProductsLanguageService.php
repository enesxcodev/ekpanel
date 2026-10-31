<?php

namespace Modules\Products\Services;

use App\Models\Settings\Language;
use Illuminate\Database\Eloquent\Collection;

class ProductsLanguageService
{
    public const string PrimaryCode = 'tr';

    /** @return Collection<int, Language> */
    public function active(): Collection
    {
        return Language::query()->where('is_active', true)->orderByRaw('CASE WHEN code = ? THEN 0 ELSE 1 END', [self::PrimaryCode])->orderBy('id')->get();
    }

    public function forAdmin(?string $code = null): ?Language
    {
        $language = $code === null ? $this->active()->first() : Language::query()->where('is_active', true)->where('code', $code)->firstOrFail();
        request()->attributes->set('products_language_id', $language?->id);
        request()->attributes->set('products_language', $language?->code ?? self::PrimaryCode);

        return $language;
    }

    public function forFrontend(?string $code = null): ?Language
    {
        $code ??= request()->attributes->get('products_language', self::PrimaryCode);
        $language = Language::query()->where('is_active', true)->where('code', $code)->first();
        request()->attributes->set('products_language_id', $language?->id);

        return $language;
    }
}
