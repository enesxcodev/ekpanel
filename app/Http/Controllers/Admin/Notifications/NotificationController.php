<?php

namespace App\Http\Controllers\Admin\Notifications;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Notifications\UpdateNotificationsRequest;
use App\Services\Admin\NotificationLink;
use App\Services\Admin\NotificationService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class NotificationController extends Controller
{
    public function __construct(private NotificationService $notifications, private NotificationLink $links) {}

    public function index(Request $request): View
    {
        $filter = $this->filter($request);

        return view('admin.notifications.index', compact('filter'));
    }

    public function data(Request $request): JsonResponse
    {
        $filter = $this->filter($request);
        $request->validate(['length' => ['sometimes', 'integer', 'min:1', 'max:100'], 'start' => ['sometimes', 'integer', 'min:0']]);

        return DataTables::eloquent($this->notifications->query($request->user('admin'), $filter)->getQuery())
            ->order(function (Builder $query) use ($request): void {
                $direction = $request->input('order.0.dir') === 'asc' ? 'asc' : 'desc';
                $query->reorder()->orderBy('created_at', $direction)->orderBy('id', $direction);
            })
            ->addColumn('selection', fn (DatabaseNotification $notification): string => view('admin.notifications.selection', compact('notification'))->render())
            ->addColumn('message', fn (DatabaseNotification $notification): string => view('admin.notifications.message', ['notification' => $notification, 'destination' => $this->links->destination($notification->data['link'] ?? null)])->render())
            ->addColumn('status', fn (DatabaseNotification $notification): string => $notification->read_at ? 'Okunmuş' : 'Okunmamış')
            ->editColumn('created_at', fn (DatabaseNotification $notification): string => $notification->created_at->format('d.m.Y H:i'))
            ->rawColumns(['selection', 'message'])->toJson();
    }

    public function header(Request $request): JsonResponse
    {
        $header = $this->notifications->header($request->user('admin'));

        return response()->json(['count' => $header['unreadNotificationCount'], 'html' => view('admin.notifications.dropdown', $header)->render()]);
    }

    public function show(Request $request, string $notification): View
    {
        $item = $this->notifications->find($request->user('admin'), $notification);

        return view('admin.notifications.show', ['notification' => $item, 'destination' => $this->links->destination($item->data['link'] ?? null)]);
    }

    public function open(Request $request, string $notification): RedirectResponse
    {
        $item = $this->notifications->find($request->user('admin'), $notification);
        $destination = $this->links->destination($item->data['link'] ?? null);

        try {
            $item->markAsRead();
        } catch (QueryException $exception) {
            report($exception);
        }

        return $destination ? redirect()->to($destination) : redirect()->route('admin.notifications.show', $item->id);
    }

    public function read(Request $request, string $notification): JsonResponse
    {
        $this->notifications->markRead($request->user('admin'), $notification);

        return response()->json(['message' => 'Bildirim okundu olarak işaretlendi.']);
    }

    public function update(UpdateNotificationsRequest $request): JsonResponse|RedirectResponse
    {
        $input = $request->validated();
        $updated = $this->notifications->update($request->user('admin'), $input['action'], $input['scope'] === 'all' ? null : $input['ids']);
        $message = $updated.' bildirim güncellendi.';

        return $request->expectsJson() ? response()->json(compact('message', 'updated')) : back()->with('notification', ['type' => 'success', 'message' => $message]);
    }

    private function filter(Request $request): string
    {
        return $request->validate(['filter' => ['sometimes', Rule::in(['all', 'unread', 'read', 'archive'])]])['filter'] ?? 'all';
    }
}
