<?php

namespace Modules\Popup\Services;

use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Schema;
use Modules\Popup\Models\Popup;
use Nwidart\Modules\Facades\Module;

class PopupFrontendService
{
    /** @return Collection<int, Popup> */
    public function published(?DateTimeInterface $at = null): Collection
    {
        if (! $this->available()) {
            return new Collection;
        }

        $at = $this->instant($at);

        return Popup::query()->with('imageMedia')->where('is_active', true)
            ->where('starts_at', '<=', $at)->where('ends_at', '>', $at)
            ->orderByDesc('starts_at')->orderByDesc('id')->get();
    }

    /**
     * Visitor state is supplied by the caller; this service never persists it.
     *
     * @param  array<int, array{closed_at?: ?DateTimeInterface, last_shown_at?: ?DateTimeInterface}>  $visitorStates
     * @return Collection<int, Popup>
     */
    public function eligible(array $visitorStates = [], ?DateTimeInterface $at = null): Collection
    {
        $at = $this->instant($at);

        return $this->published($at)->filter(fn (Popup $popup): bool => $this->matchesVisitorState(
            $popup, $visitorStates[$popup->id]['closed_at'] ?? null,
            $visitorStates[$popup->id]['last_shown_at'] ?? null, $at,
        ))->values();
    }

    public function shouldShow(Popup $popup, ?DateTimeInterface $closedAt = null, ?DateTimeInterface $lastShownAt = null, ?DateTimeInterface $at = null): bool
    {
        if (! $this->available()) {
            return false;
        }

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

    private function available(): bool
    {
        return Module::find('Popup')?->isEnabled() === true && Schema::hasTable('popups');
    }

    private function instant(?DateTimeInterface $at): CarbonImmutable
    {
        return ($at === null ? CarbonImmutable::now() : CarbonImmutable::instance($at))->setTimezone(config('app.timezone'));
    }
}
