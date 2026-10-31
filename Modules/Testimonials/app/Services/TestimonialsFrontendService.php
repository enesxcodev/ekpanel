<?php

namespace Modules\Testimonials\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Schema;
use Modules\Testimonials\Models\Testimonial;
use Nwidart\Modules\Facades\Module;

class TestimonialsFrontendService
{
    public function __construct(private TestimonialLanguageService $languages) {}

    /** @return Collection<int, Testimonial> */
    public function getAll(?string $languageCode = null): Collection
    {
        return $this->visibleQuery($languageCode)?->get()->each(fn (Testimonial $testimonial) => $this->localize($testimonial)) ?? new Collection;
    }

    public function getFirst(?string $languageCode = null): ?Testimonial
    {
        $testimonial = $this->visibleQuery($languageCode)?->first();

        return $testimonial ? $this->localize($testimonial) : null;
    }

    public function find(int $id, ?string $languageCode = null): ?Testimonial
    {
        $testimonial = $this->visibleQuery($languageCode)?->whereKey($id)->first();

        return $testimonial ? $this->localize($testimonial) : null;
    }

    private function localize(Testimonial $testimonial): Testimonial
    {
        $testimonial->setRelation('selectedTranslation', $testimonial->translations->first());

        return $testimonial;
    }

    /** @return Builder<Testimonial>|null */
    private function visibleQuery(?string $languageCode = null): ?Builder
    {
        if (! Module::find('Testimonials')?->isEnabled() || ! Schema::hasTable('testimonials') || ! Schema::hasTable('testimonial_translations') || ! Schema::hasTable('languages')) {
            return null;
        }
        $language = $this->languages->forFrontend($languageCode);
        if ($language === null) {
            return null;
        }

        $query = Testimonial::query()->with(['imageMedia', 'translations' => fn ($query) => $query->where('lang_id', $language->id)])
            ->where('is_active', true)->whereHas('translations', fn ($query) => $query->where('lang_id', $language->id))
            ->orderBy('sort_order')->orderBy('id');

        return $query;
    }
}
