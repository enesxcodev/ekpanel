<?php

namespace Modules\Popup\Services;

use App\Models\Settings\Language;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Schema;
use Modules\Popup\Models\Popup;
use Nwidart\Modules\Facades\Module;

class PopupFrontendService
{
    /** @return Collection<int, Popup> */
    public function published(?DateTimeInterface $at = null, Language|string|null $language = null): Collection
    {
        $resolved = $this->languages($language);
        if (! $this->available() || $resolved === []) {
            return new Collection;
        }

        $at = $this->instant($at);
        $languageIds = array_map(fn (Language $item): int => $item->id, $resolved);

        return Popup::query()->with(['imageMedia', 'translations' => fn ($query) => $query->whereIn('lang_id', $languageIds)])
            ->whereHas('translations', fn ($query) => $query->whereIn('lang_id', $languageIds))
            ->where('is_active', true)->where('starts_at', '<=', $at)->where('ends_at', '>', $at)
            ->orderByDesc('starts_at')->orderByDesc('id')->get()
            ->each(function (Popup $popup) use ($resolved): void {
                $selected = null;
                foreach ($resolved as $language) {
                    $selected = $popup->translations->firstWhere('lang_id', $language->id);
                    if ($selected !== null) {
                        break;
                    }
                }
                $popup->setRelation('selectedTranslation', $selected);
            });
    }

    /**
     * Visitor state is supplied by the caller; this service never persists it.
     *
     * @param  array<int, array{closed_at?: ?DateTimeInterface, last_shown_at?: ?DateTimeInterface}>  $visitorStates
     * @return Collection<int, Popup>
     */
    public function eligible(array $visitorStates = [], ?DateTimeInterface $at = null, Language|string|null $language = null): Collection
    {
        $at = $this->instant($at);

        return $this->published($at, $language)->filter(fn (Popup $popup): bool => $this->matchesVisitorState(
            $popup, $visitorStates[$popup->id]['closed_at'] ?? null,
            $visitorStates[$popup->id]['last_shown_at'] ?? null, $at,
        ))->values();
    }

    public function shouldShow(Popup $popup, ?DateTimeInterface $closedAt = null, ?DateTimeInterface $lastShownAt = null, ?DateTimeInterface $at = null, Language|string|null $language = null): bool
    {
        $resolved = $this->languages($language);
        if (! $this->available() || $resolved === []) {
            return false;
        }

        $translation = null;
        if ($popup->relationLoaded('translations')) {
            foreach ($resolved as $item) {
                $translation = $popup->translations->firstWhere('lang_id', $item->id);
                if ($translation !== null) {
                    break;
                }
            }
        } elseif ($popup->relationLoaded('selectedTranslation') && $popup->getRelation('selectedTranslation')?->lang_id === $resolved[0]->id) {
            $translation = $popup->getRelation('selectedTranslation');
        } else {
            $translation = $popup->translations()->whereIn('lang_id', array_map(fn (Language $item): int => $item->id, $resolved))
                ->orderByRaw('CASE WHEN lang_id = ? THEN 0 ELSE 1 END', [$resolved[0]->id])->first();
            $popup->setRelation('selectedTranslation', $translation);
        }
        if ($translation === null) {
            return false;
        }
        $popup->setRelation('selectedTranslation', $translation);

        $at = $this->instant($at);

        return $popup->is_active && $popup->starts_at !== null && $popup->ends_at !== null
            && $popup->starts_at->lte($at) && $popup->ends_at->gt($at)
            && $this->matchesVisitorState($popup, $closedAt, $lastShownAt, $at);
    }

    private function matchesVisitorState(Popup $popup, ?DateTimeInterface $closedAt, ?DateTimeInterface $lastShownAt, CarbonImmutable $at): bool
    {
        if ($popup->repeat_policy === 'never_after_close') {
            return $closedAt === null;
        }
        if ($popup->repeat_policy !== 'interval' || $popup->repeat_interval < 1 || ! in_array($popup->repeat_unit, ['hours', 'days'], true)) {
            return false;
        }

        $reference = $lastShownAt === null ? null : CarbonImmutable::instance($lastShownAt);
        if ($closedAt !== null && ($reference === null || $reference->lt($closedAt))) {
            $reference = CarbonImmutable::instance($closedAt);
        }

        return $reference === null || $reference->addSeconds($popup->repeat_interval * ($popup->repeat_unit === 'hours' ? 3600 : 86400))->lte($at);
    }

    /** @return list<Language> */
    private function languages(Language|string|null $language): array
    {
        if (! Schema::hasTable('languages')) {
            return [];
        }
        $code = $language instanceof Language ? $language->code : $language;
        $code ??= request()->attributes->get('popup_language', PopupLanguageService::PrimaryCode);
        $selected = Language::query()->where('is_active', true)->where('code', $code)->first();
        $fallback = Language::query()->where('is_active', true)->where('code', PopupLanguageService::PrimaryCode)->first();

        return array_values(array_filter([$selected, $fallback], fn (?Language $item): bool => $item !== null));
    }

    private function available(): bool
    {
        return Module::find('Popup')?->isEnabled() === true
            && Schema::hasTable('popups')
            && Schema::hasTable('popup_translations');
    }

    private function instant(?DateTimeInterface $at): CarbonImmutable
    {
        return ($at === null ? CarbonImmutable::now() : CarbonImmutable::instance($at))->setTimezone(config('app.timezone'));
    }
}
