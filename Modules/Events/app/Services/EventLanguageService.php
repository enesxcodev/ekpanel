<?php

namespace Modules\Events\Services;

use App\Models\Settings\Language;
use Illuminate\Database\Eloquent\Collection;

class EventLanguageService
{
    public const string PrimaryCode = 'tr';

    /** @return Collection<int, Language> */
    public function active(): Collection
    {
        return Language::query()->where('is_active', true)->orderByRaw('CASE WHEN code = ? THEN 0 ELSE 1 END', [self::PrimaryCode])->orderBy('id')->get();
    }

    public function forAdmin(?string $code = null): ?Language
    {
        $language = $code === null
            ? $this->active()->first()
            : Language::query()->where('is_active', true)->where('code', $code)->firstOrFail();
        request()->attributes->set('events_language', $language?->code ?? self::PrimaryCode);
        request()->attributes->set('events_language_id', $language?->id);

        return $language;
    }

    public function forFrontend(?string $code = null): ?Language
    {
        $code ??= request()->attributes->get('events_language') ?? request()->route('lang');
        $code ??= self::PrimaryCode;
        $language = Language::query()->where('is_active', true)->where('code', $code)->first();
        request()->attributes->set('events_language_id', $language?->id);

        return $language;
    }
}
