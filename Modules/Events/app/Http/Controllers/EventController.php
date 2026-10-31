<?php

namespace Modules\Events\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Settings\Language;
use App\Services\NotifyService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Events\DataTables\EventsDataTable;
use Modules\Events\Http\Requests\SaveEventRequest;
use Modules\Events\Models\Event;
use Modules\Events\Services\EventLanguageService;
use Modules\Events\Services\EventService;

class EventController extends Controller
{
    public function index(EventLanguageService $languages, Request $request): View
    {
        $this->authorize('events.view');

        $activeLanguages = $languages->active();
        $selectedLanguage = $languages->forAdmin($request->query('lang'));

        return view('events::index', compact('activeLanguages', 'selectedLanguage'));
    }

    public function create(EventLanguageService $languages, Request $request): View
    {
        $this->authorize('events.create');

        $activeLanguages = $languages->active();
        $selectedLanguage = $languages->forAdmin($request->query('lang', 'tr'));
        if ($selectedLanguage?->code !== 'tr') {
            abort(404);
        }
        $translation = null;
        $translatedLanguageIds = [];

        return view('events::create', compact('activeLanguages', 'selectedLanguage', 'translation', 'translatedLanguageIds') + ['event' => null]);
    }

    public function data(EventsDataTable $dataTable): JsonResponse
    {
        $this->authorize('events.view');
        app(EventLanguageService::class)->forAdmin(request()->query('lang'));

        return $dataTable->handle();
    }

    public function store(SaveEventRequest $request, EventService $service, NotifyService $notifications): RedirectResponse
    {
        $this->authorize('events.create');
        $event = $service->save($request->validated());
        $notifications->sendToAdminsForActor($request->user('admin'), 'Etkinlik eklendi', 'Yeni bir etkinlik eklendi.', route('admin.events.edit', $event, false));

        return redirect()->route('admin.events.edit', ['event' => $event->id, 'lang' => 'tr'])->with('notification', ['type' => 'success', 'message' => 'Etkinlik başarıyla eklendi.']);
    }

    public function edit(Event $event, EventLanguageService $languages, Request $request): View
    {
        $this->authorize('events.update');

        $activeLanguages = $languages->active();
        $selectedLanguage = $languages->forAdmin($request->query('lang'));
        $event->load('translations');
        $translation = $event->translations->firstWhere('lang_id', $selectedLanguage?->id);
        $translatedLanguageIds = $event->translations->pluck('lang_id')->all();

        return view('events::edit', compact('event', 'activeLanguages', 'selectedLanguage', 'translation', 'translatedLanguageIds'));
    }

    public function update(SaveEventRequest $request, Event $event, EventService $service, NotifyService $notifications): RedirectResponse
    {
        $this->authorize('events.update');
        $event = $service->save($request->validated(), $event);
        $notifications->sendToAdminsForActor($request->user('admin'), 'Etkinlik güncellendi', 'Bir etkinlik güncellendi.', route('admin.events.edit', $event, false));

        $code = Language::query()->findOrFail($request->validated('lang_id'))->code;

        return redirect()->route('admin.events.edit', ['event' => $event->id, 'lang' => $code])->with('notification', ['type' => 'success', 'message' => 'Etkinlik başarıyla güncellendi.']);
    }

    public function destroy(Event $event, EventService $service, NotifyService $notifications): RedirectResponse
    {
        $this->authorize('events.delete');
        $service->delete($event);
        $notifications->sendToAdminsForActor(request()->user('admin'), 'Etkinlik silindi', 'Bir etkinlik silindi.', route('admin.events.index', [], false));

        return redirect()->route('admin.events.index')->with('notification', ['type' => 'success', 'message' => 'Etkinlik başarıyla silindi.']);
    }
}
