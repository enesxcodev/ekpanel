<?php

namespace Modules\Branches\Services;

use App\Models\Settings\Language;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class BranchesLanguageService
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
            ? Language::query()->where('code', self::PrimaryCode)->where('is_active', true)->first() ?? $this->active()->first()
            : Language::query()->where('code', $code)->where('is_active', true)->firstOrFail();
        request()->attributes->set('branches_language', $language?->code ?? self::PrimaryCode);

        return $language;
    }

    public function selectedAdmin(Request $request): ?Language
    {
        return $this->forAdmin($request->query('lang'));
    }

    public function forFrontend(?string $code = null): ?Language
    {
        $code ??= request()->attributes->get('branches_language');
        $code ??= app()->getLocale();

        return Language::query()->where('code', $code)->where('is_active', true)->first()
            ?? Language::query()->where('code', self::PrimaryCode)->where('is_active', true)->first();
    }

    /** @return list<int> */
    public function translatedLanguageIds(object $record): array
    {
        return $record->translations()->pluck('lang_id')->map(static fn ($id): int => (int) $id)->all();
    }
}
