<?php

namespace Modules\Events\Services;

use Illuminate\Support\Facades\DB;
use Modules\Events\Models\Event;

class EventService
{
    /** @param array<string, mixed> $data */
    public function save(array $data, ?Event $event = null): Event
    {
        return DB::transaction(function () use ($data, $event): Event {
            $event ??= new Event;
            $event->fill($data)->save();

            return $event;
        });
    }

    public function delete(Event $event): void
    {
        DB::transaction(fn () => $event->delete());
    }
}
