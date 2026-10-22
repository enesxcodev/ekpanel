<?php

namespace Modules\Events\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Modules\Events\Http\Requests\SaveEventRegistrationRequest;
use Modules\Events\Models\Event;
use Modules\Events\Models\EventRegistration;

class EventRegistrationController extends Controller
{
    public function index(): View
    {
        $this->authorize('events.view');

        return view('events::registrations.index', ['registrations' => EventRegistration::query()->with('event:id,title')->latest()->paginate(20)]);
    }

    public function create(): View
    {
        $this->authorize('events.create');

        return view('events::registrations.create', ['events' => Event::query()->where('is_active', true)->orderBy('starts_at')->get(['id', 'title'])]);
    }

    public function store(SaveEventRegistrationRequest $request): RedirectResponse
    {
        $this->authorize('events.create');
        $data = $request->validated();
        $event = Event::query()->findOrFail($data['event_id']);
        if ($data['status'] === 'registered' && $event->capacity !== null && $event->registrations()->where('status', 'registered')->count() >= $event->capacity) {
            throw ValidationException::withMessages(['event_id' => 'Etkinlik kapasitesi dolu.']);
        }
        EventRegistration::query()->create($data);

        return redirect()->route('admin.events.registrations.index')->with('notification', ['type' => 'success', 'message' => 'Katılımcı kaydı eklendi.']);
    }
}
