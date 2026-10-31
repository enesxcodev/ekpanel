<?php

namespace Modules\Store\Services;

use App\Models\Settings\Language;
use Illuminate\Database\Eloquent\Collection;

class StoreLanguageService
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
        request()->attributes->set('store_language_id', $language?->id);
        request()->attributes->set('store_language', $language?->code ?? self::PrimaryCode);

        return $language;
    }

    public function forFrontend(?string $code = null): ?Language
    {
        $code ??= request()->route('lang') ?? request()->query('lang', request()->attributes->get('store_language', self::PrimaryCode));
        $language = Language::query()->where('is_active', true)->where('code', $code)->first();
        request()->attributes->set('store_language_id', $language?->id);
        request()->attributes->set('store_language', $language?->code ?? self::PrimaryCode);

        return $language;
    }

    public function forPublic(?string $code = null): Language
    {
        return $this->forFrontend($code) ?? abort(404);
    }
}
