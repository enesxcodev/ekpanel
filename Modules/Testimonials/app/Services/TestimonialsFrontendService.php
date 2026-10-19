<?php

namespace Modules\Testimonials\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Schema;
use Modules\Testimonials\Models\Testimonial;
use Nwidart\Modules\Facades\Module;

class TestimonialsFrontendService
{
    /** @return Collection<int, Testimonial> */
    public function getAll(): Collection
    {
        return $this->visibleQuery()?->get() ?? new Collection;
    }

    public function getFirst(): ?Testimonial
    {
        return $this->visibleQuery()?->first();
    }

    public function find(int $id): ?Testimonial
    {
        return $this->visibleQuery()?->whereKey($id)->first();
    }

    /** @return Builder<Testimonial>|null */
    private function visibleQuery(): ?Builder
    {
        if (! Module::find('Testimonials')?->isEnabled() || ! Schema::hasTable('testimonials')) {
            return null;
        }

        $query = Testimonial::query()->with(['imageMedia'])->published();

        return $query;
    }
}
