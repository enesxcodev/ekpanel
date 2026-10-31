<?php

namespace Modules\Events\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Modules\Events\Http\Requests\SaveEventRegistrationRequest;
use Modules\Events\Models\Event;
use Modules\Events\Models\EventRegistration;
use Modules\Events\Services\EventLanguageService;

class EventRegistrationController extends Controller
{
    public function index(EventLanguageService $languages, Request $request): View
    {
        $this->authorize('events.view');

        $selectedLanguage = $languages->forAdmin($request->query('lang'));
        $registrations = EventRegistration::query()->with(['event', 'event.selectedTranslation'])->latest()->paginate(20)->withQueryString();

        return view('events::registrations.index', compact('registrations', 'selectedLanguage'));
    }

    public function create(EventLanguageService $languages, Request $request): View
    {
        $this->authorize('events.create');

        $selectedLanguage = $languages->forAdmin($request->query('lang'));
        $events = Event::query()->join('event_translations as event_translation', function ($join) use ($selectedLanguage): void {
            $join->on('event_translation.event_id', '=', 'events.id')->where('event_translation.lang_id', '=', $selectedLanguage?->id);
        })->where('events.is_active', true)->orderBy('events.starts_at')->get(['events.id', 'event_translation.title']);

        return view('events::registrations.create', compact('events', 'selectedLanguage'));
    }

    public function store(SaveEventRegistrationRequest $request, EventLanguageService $languages): RedirectResponse
    {
        $this->authorize('events.create');
        $data = $request->validated();
        $selectedLanguage = $languages->forAdmin($request->query('lang'));
        $event = Event::query()->findOrFail($data['event_id']);
        if ($data['status'] === 'registered' && $event->capacity !== null && $event->registrations()->where('status', 'registered')->count() >= $event->capacity) {
            throw ValidationException::withMessages(['event_id' => 'Etkinlik kapasitesi dolu.']);
        }
        EventRegistration::query()->create($data);

        return redirect()->route('admin.events.registrations.index', ['lang' => $selectedLanguage?->code])->with('notification', ['type' => 'success', 'message' => 'Katılımcı kaydı eklendi.']);
    }
}
