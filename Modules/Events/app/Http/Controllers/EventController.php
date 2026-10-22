<?php

namespace Modules\Events\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\NotifyService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Modules\Events\DataTables\EventsDataTable;
use Modules\Events\Http\Requests\SaveEventRequest;
use Modules\Events\Models\Event;
use Modules\Events\Services\EventService;

class EventController extends Controller
{
    public function index(): View
    {
        $this->authorize('events.view');

        return view('events::index', ['events' => Event::query()->orderBy('starts_at')->paginate(20)]);
    }

    public function create(): View
    {
        $this->authorize('events.create');

        return view('events::create', ['event' => null]);
    }

    public function data(EventsDataTable $dataTable): JsonResponse
    {
        $this->authorize('events.view');

        return $dataTable->handle();
    }

    public function store(SaveEventRequest $request, EventService $service, NotifyService $notifications): RedirectResponse
    {
        $this->authorize('events.create');
        $event = $service->save($request->validated());
        $notifications->sendToAdminsForActor($request->user('admin'), 'Etkinlik eklendi', 'Yeni bir etkinlik eklendi.', route('admin.events.edit', $event, false));

        return redirect()->route('admin.events.index')->with('notification', ['type' => 'success', 'message' => 'Etkinlik başarıyla eklendi.']);
    }

    public function edit(Event $event): View
    {
        $this->authorize('events.update');

        return view('events::edit', compact('event'));
    }

    public function update(SaveEventRequest $request, Event $event, EventService $service, NotifyService $notifications): RedirectResponse
    {
        $this->authorize('events.update');
        $event = $service->save($request->validated(), $event);
        $notifications->sendToAdminsForActor($request->user('admin'), 'Etkinlik güncellendi', 'Bir etkinlik güncellendi.', route('admin.events.edit', $event, false));

        return redirect()->route('admin.events.index')->with('notification', ['type' => 'success', 'message' => 'Etkinlik başarıyla güncellendi.']);
    }

    public function destroy(Event $event, EventService $service, NotifyService $notifications): RedirectResponse
    {
        $this->authorize('events.delete');
        $service->delete($event);
        $notifications->sendToAdminsForActor(request()->user('admin'), 'Etkinlik silindi', 'Bir etkinlik silindi.', route('admin.events.index', [], false));

        return redirect()->route('admin.events.index')->with('notification', ['type' => 'success', 'message' => 'Etkinlik başarıyla silindi.']);
    }
}
