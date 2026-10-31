<?php

namespace Modules\Events\Services;

use App\Models\Settings\Language;
use Illuminate\Support\Facades\DB;
use Modules\Events\Models\Event;

class EventService
{
    /** @param array<string, mixed> $data */
    public function save(array $data, ?Event $event = null): Event
    {
        return DB::transaction(function () use ($data, $event): Event {
            $language = Language::query()->where('is_active', true)->findOrFail($data['lang_id']);
            $event ??= new Event;
            if (! $event->exists || $language->code === EventLanguageService::PrimaryCode) {
                $event->fill(array_intersect_key($data, array_flip(['starts_at', 'ends_at', 'capacity', 'is_active'])))->save();
            }

            $event->translations()->updateOrCreate(
                ['lang_id' => $language->id],
                ['title' => $data['title'], 'venue' => $data['venue'] ?? null, 'description' => $data['description'] ?? null],
            );

            return $event;
        });
    }

    public function delete(Event $event): void
    {
        DB::transaction(fn () => $event->delete());
    }
}
