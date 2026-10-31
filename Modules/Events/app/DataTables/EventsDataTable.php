<?php

namespace Modules\Events\DataTables;

use Illuminate\Http\JsonResponse;
use Modules\Events\Models\Event;
use Modules\Events\Services\EventLanguageService;
use Yajra\DataTables\Facades\DataTables;

class EventsDataTable
{
    public function handle(): JsonResponse
    {
        $languageId = request()->attributes->get('events_language_id') ?? app(EventLanguageService::class)->forAdmin()?->id;
        $query = Event::query()->join('event_translations as event_translation', function ($join) use ($languageId): void {
            $join->on('event_translation.event_id', '=', 'events.id')->where('event_translation.lang_id', '=', $languageId);
        })->select(['events.id', 'event_translation.title', 'event_translation.venue', 'events.starts_at', 'events.capacity', 'events.is_active']);

        return DataTables::eloquent($query)
            ->editColumn('starts_at', fn (Event $event): string => $event->starts_at->format('d.m.Y H:i'))
            ->editColumn('capacity', fn (Event $event): string => $event->capacity === null ? 'Sınırsız' : (string) $event->capacity)
            ->editColumn('is_active', fn (Event $event): string => $event->is_active ? 'Aktif' : 'Pasif')
            ->addColumn('actions', fn (Event $event): string => view('events::partials.actions', compact('event'))->render())
            ->rawColumns(['actions'])
            ->toJson();
    }
}
