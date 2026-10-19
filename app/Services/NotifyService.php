<?php

namespace App\Services;

use App\Enums\AdminType\AdminTypeEnum;
use App\Models\Admin\Admin;
use App\Notifications\AdminNotification;
use App\Services\Admin\NotificationLink;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class NotifyService
{
    public function __construct(private NotificationLink $links) {}

    public function send(Admin $recipient, string $title, string $content, ?string $link = null): void
    {
        $this->sendMany([$recipient], $title, $content, $link);
    }

    /** @param iterable<Admin> $recipients */
    public function sendForActor(Admin $actor, iterable $recipients, string $title, string $content, ?string $link = null): void
    {
        if ($actor->type === AdminTypeEnum::SuperAdmin) {
            return;
        }

        $this->sendMany($recipients, $title, $content, $link);
    }

    public function sendToAdminsForActor(Admin $actor, string $title, string $content, ?string $link = null): void
    {
        $this->sendForActor($actor, Admin::query()->get(), $title, $content, $link);
    }

    /** @param iterable<Admin> $recipients */
    public function sendMany(iterable $recipients, string $title, string $content, ?string $link = null): void
    {
        Validator::make(compact('title', 'content', 'link'), [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string', 'max:10000'],
            'link' => ['nullable', 'string', 'max:2048'],
        ])->validate();

        $path = $this->links->safePath($link);
        if ($link !== null && $path === null) {
            throw ValidationException::withMessages(['link' => 'Bildirim bağlantısı güvenli bir uygulama içi adres olmalıdır.']);
        }

        DB::transaction(function () use ($recipients, $title, $content, $path): void {
            $sent = [];
            foreach ($recipients as $recipient) {
                if (! $recipient instanceof Admin || ! $recipient->exists) {
                    throw ValidationException::withMessages(['recipients' => 'Alıcı kayıtlı bir yönetici olmalıdır.']);
                }
                if (! isset($sent[$recipient->getKey()])) {
                    $recipient->notify(new AdminNotification($title, $content, $path));
                    $sent[$recipient->getKey()] = true;
                }
            }
        });
    }
}
