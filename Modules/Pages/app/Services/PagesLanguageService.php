<?php

namespace Modules\Pages\Services;

use App\Models\Settings\Language;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class PagesLanguageService
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
            ? $this->forFrontend(self::PrimaryCode)
            : Language::query()->where('is_active', true)->where('code', $code)->firstOrFail();

        request()->attributes->set('pages_language', $language?->code ?? self::PrimaryCode);

        return $language;
    }

    public function forFrontend(?string $code = null): ?Language
    {
        $code ??= request()->attributes->get('pages_language');
        $code ??= request()->route('lang');
        $code ??= self::PrimaryCode;

        return Language::query()->where('is_active', true)->where('code', $code)->first();
    }

    public function forPublic(?string $code = null): Language
    {
        $language = $this->forFrontend($code) ?? abort(404);
        request()->attributes->set('pages_language', $language->code);

        return $language;
    }

    public function selectedAdmin(Request $request): ?Language
    {
        return $this->forAdmin($request->query('lang'));
    }

    /** @return list<int> */
    public function translatedLanguageIds(object $record): array
    {
        return $record->translations()->pluck('lang_id')->map(static fn ($id): int => (int) $id)->all();
    }
}
