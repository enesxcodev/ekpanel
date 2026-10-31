<?php

namespace Modules\Menus\Services;

use App\Models\Settings\Language;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class MenusLanguageService
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
            $code = self::PrimaryCode;
        }

        $language = Language::query()->where('is_active', true)->where('code', $code)->firstOrFail();
        request()->attributes->set('menus_language', $language->code);

        return $language;
    }

    public function selectedAdmin(Request $request): ?Language
    {
        return $this->forAdmin($request->query('lang'));
    }

    public function forFrontend(?string $code = null): ?Language
    {
        $code ??= request()->route('lang');
        $code ??= request()->attributes->get('menus_language');
        $code ??= self::PrimaryCode;

        $language = Language::query()->where('is_active', true)->where('code', $code)->first();
        if ($language !== null) {
            request()->attributes->set('menus_language', $language->code);
        }

        return $language;
    }
}
