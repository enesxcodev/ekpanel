<?php

namespace Modules\Testimonials\Services;

use App\Models\Settings\Language;
use Illuminate\Database\Eloquent\Collection;

class TestimonialLanguageService
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
        request()->attributes->set('testimonials_language', $language?->code ?? self::PrimaryCode);

        return $language;
    }

    public function idForAdmin(?string $code = null): ?int
    {
        return $this->forAdmin($code)?->id;
    }

    public function forFrontend(?string $code = null): ?Language
    {
        $code ??= request()->attributes->get('testimonials_language') ?? request()->attributes->get('pages_language') ?? request()->route('lang') ?? self::PrimaryCode;

        return Language::query()->where('is_active', true)->where('code', (string) $code)->first();
    }
}
