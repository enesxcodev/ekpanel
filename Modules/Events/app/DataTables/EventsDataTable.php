<?php

namespace Modules\Events\DataTables;

use Illuminate\Http\JsonResponse;
use Modules\Events\Models\Event;
use Yajra\DataTables\Facades\DataTables;

class EventsDataTable
{
    public function handle(): JsonResponse
    {
        return DataTables::eloquent(Event::query()->select(['id', 'title', 'venue', 'starts_at', 'capacity', 'is_active']))
            ->editColumn('starts_at', fn (Event $event): string => $event->starts_at->format('d.m.Y H:i'))
            ->editColumn('capacity', fn (Event $event): string => $event->capacity === null ? 'Sınırsız' : (string) $event->capacity)
            ->editColumn('is_active', fn (Event $event): string => $event->is_active ? 'Aktif' : 'Pasif')
            ->addColumn('actions', fn (Event $event): string => view('events::partials.actions', compact('event'))->render())
            ->rawColumns(['actions'])
            ->toJson();
    }
}
