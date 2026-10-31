<?php

namespace Modules\Faqs\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Schema;
use Modules\Faqs\Models\Faq;
use Nwidart\Modules\Facades\Module;

class FaqsFrontendService
{
    public function __construct(private FaqLanguageService $languages) {}

    /** @return Collection<int, Faq> */
    public function getAll(?int $categoryId = null, ?string $languageCode = null): Collection
    {
        return $this->visibleQuery($categoryId, $languageCode)?->get()->each(fn (Faq $faq) => $this->localize($faq)) ?? new Collection;
    }

    public function getFirst(?int $categoryId = null, ?string $languageCode = null): ?Faq
    {
        $faq = $this->visibleQuery($categoryId, $languageCode)?->first();

        return $faq === null ? null : $this->localize($faq);
    }

    public function find(int $id, ?string $languageCode = null): ?Faq
    {
        $faq = $this->visibleQuery(null, $languageCode)?->whereKey($id)->first();

        return $faq === null ? null : $this->localize($faq);
    }

    private function localize(Faq $faq): Faq
    {
        $faq->setRelation('selectedTranslation', $faq->translations->first());
        if ($faq->category !== null) {
            $faq->category->setRelation('selectedTranslation', $faq->category->translations->first());
        }

        return $faq;
    }

    /** @return Builder<Faq>|null */
    private function visibleQuery(?int $categoryId = null, ?string $languageCode = null): ?Builder
    {
        if (! Module::find('Faqs')?->isEnabled() || ! Schema::hasTable('faqs') || ! Schema::hasTable('faq_categories') || ! Schema::hasTable('faq_translations') || ! Schema::hasTable('faq_category_translations') || ! Schema::hasTable('languages')) {
            return null;
        }
        $language = $this->languages->forFrontend($languageCode);
        if ($language === null) {
            return null;
        }

        $query = Faq::query()
            ->with([
                'translations' => fn ($translationQuery) => $translationQuery->where('lang_id', $language->id),
                'category.translations' => fn ($translationQuery) => $translationQuery->where('lang_id', $language->id),
            ])
            ->whereHas('translations', fn ($translationQuery) => $translationQuery->where('lang_id', $language->id));

        if ($categoryId !== null) {
            $query->where('category_id', $categoryId);
        }

        return $query;
    }
}
