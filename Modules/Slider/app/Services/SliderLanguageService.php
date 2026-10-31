<?php

namespace Modules\Slider\Services;

use App\Models\Settings\Language;
use Illuminate\Database\Eloquent\Collection;

class SliderLanguageService
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
        request()->attributes->set('slider_language', $language?->code ?? self::PrimaryCode);

        return $language;
    }

    public function forFrontend(Language|string|null $language = null): ?Language
    {
        $code = $language instanceof Language ? $language->code : $language;
        $code ??= request()->attributes->get('slider_language', self::PrimaryCode);

        return Language::query()->where('is_active', true)->where('code', $code)->first();
    }
}
