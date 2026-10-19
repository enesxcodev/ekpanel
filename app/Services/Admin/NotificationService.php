<?php

namespace App\Services\Admin;

use App\Models\Admin\Admin;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\DB;

class NotificationService
{
    public function __construct(private NotificationLink $links) {}

    /** @return MorphMany<DatabaseNotification, Admin> */
    public function query(Admin $admin, string $filter = 'all'): MorphMany
    {
        $query = $admin->notifications()->reorder()->orderByDesc('created_at')->orderByDesc('id');
        if ($filter === 'archive') {
            return $query->whereNotNull('archived_at');
        }
        $query->whereNull('archived_at');
        if ($filter === 'unread') {
            $query->whereNull('read_at');
        } elseif ($filter === 'read') {
            $query->whereNotNull('read_at');
        }

        return $query;
    }

    /** @return array{notificationItems: Collection<int, DatabaseNotification>, unreadNotificationCount: int} */
    public function header(Admin $admin): array
    {
        return [
            'notificationItems' => $this->query($admin)->limit(10)->get()->each(function (DatabaseNotification $notification): void {
                $notification->setAttribute('destination', $this->links->destination($notification->data['link'] ?? null));
            }),
            'unreadNotificationCount' => $this->query($admin, 'unread')->count(),
        ];
    }

    public function find(Admin $admin, string $id): DatabaseNotification
    {
        return $admin->notifications()->whereKey($id)->firstOrFail();
    }

    public function markRead(Admin $admin, string $id): void
    {
        $this->find($admin, $id)->markAsRead();
    }

    /** @param list<string>|null $ids */
    public function update(Admin $admin, string $action, ?array $ids): int
    {
        return DB::transaction(function () use ($admin, $action, $ids): int {
            $query = $admin->notifications();
            if ($ids !== null) {
                $query->whereKey($ids);
                abort_unless((clone $query)->count() === count($ids), 404);
            } else {
                $query->whereNull('archived_at');
            }

            return match ($action) {
                'read' => $query->whereNull('read_at')->update(['read_at' => now()]),
                'archive' => $query->whereNull('archived_at')->update(['archived_at' => now()]),
                'restore' => $query->whereNotNull('archived_at')->update(['archived_at' => null]),
            };
        });
    }
}
