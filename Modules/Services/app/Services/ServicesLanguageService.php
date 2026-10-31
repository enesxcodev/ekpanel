<?php

namespace Modules\Services\Services;

use App\Models\Settings\Language;
use Illuminate\Database\Eloquent\Collection;

class ServicesLanguageService
{
    public const string PrimaryCode = 'tr';

    /** @return Collection<int, Language> */
    public function active(): Collection
    {
        return Language::query()->where('is_active', true)->orderByRaw('CASE WHEN code = ? THEN 0 ELSE 1 END', [self::PrimaryCode])->orderBy('id')->get();
    }

    public function forAdmin(?string $code = null): ?Language
    {
        if ($code === null) {
            $language = $this->active()->first();
        } else {
            $language = Language::query()->where('is_active', true)->where('code', $code)->firstOrFail();
        }

        request()->attributes->set('services_language', $language?->code ?? self::PrimaryCode);

        return $language;
    }

    public function forPublic(?string $code = null): Language
    {
        return $this->forFrontend($code) ?? abort(404);
    }

    public function forFrontend(?string $code = null): ?Language
    {
        $code ??= request()->attributes->get('services_language', self::PrimaryCode);

        return Language::query()->where('is_active', true)->where('code', $code)->first();
    }
}
