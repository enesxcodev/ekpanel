<?php

namespace Modules\Slider\Services;

use App\Models\Settings\Language;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Schema;
use Modules\Slider\Models\Slider;
use Nwidart\Modules\Facades\Module;

class SliderFrontendService
{
    public function getFirst(Language|string|null $language = null): ?Slider
    {
        $slider = $this->activeQuery($language)?->first();
        if ($slider !== null) {
            $slider->setRelation('selectedTranslation', $slider->translations->first());
        }

        return $slider;
    }

    /** @return Collection<int, Slider> */
    public function getAll(Language|string|null $language = null): Collection
    {
        return $this->activeQuery($language)?->get()->each(function (Slider $slider): void {
            $slider->setRelation('selectedTranslation', $slider->translations->first());
        }) ?? new Collection;
    }

    /** @return Builder<Slider>|null */
    private function activeQuery(Language|string|null $language): ?Builder
    {
        if (! Module::find('Slider')?->isEnabled() || ! Schema::hasTable('sliders') || ! Schema::hasTable('slider_translations') || ! Schema::hasTable('languages')) {
            return null;
        }
        $selectedLanguage = app(SliderLanguageService::class)->forFrontend($language);
        if ($selectedLanguage === null) {
            return null;
        }

        request()->attributes->set('slider_language', $selectedLanguage->code);

        return Slider::query()->with(['imageMedia', 'translations' => fn ($query) => $query->where('lang_id', $selectedLanguage->id)])
            ->where('is_active', true)
            ->whereHas('translations', fn ($query) => $query->where('lang_id', $selectedLanguage->id));
    }
}
