<?php

namespace Modules\Slider\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Schema;
use Modules\Slider\Models\Slider;
use Nwidart\Modules\Facades\Module;

class SliderFrontendService
{
    public function getFirst(): ?Slider
    {
        return $this->activeQuery()?->first();
    }

    /** @return Collection<int, Slider> */
    public function getAll(): Collection
    {
        return $this->activeQuery()?->get() ?? new Collection;
    }

    /** @return Builder<Slider>|null */
    private function activeQuery(): ?Builder
    {
        if (! Module::find('Slider')?->isEnabled() || ! Schema::hasTable('sliders')) {
            return null;
        }

        return Slider::query()->with('imageMedia')->where('is_active', true);
    }
}
