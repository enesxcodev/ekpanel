<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class AdminNotification extends Notification
{
    public function __construct(public string $title, public string $content, public ?string $link = null) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** @return array{title: string, content: string, link: ?string} */
    public function toDatabase(object $notifiable): array
    {
        return ['title' => $this->title, 'content' => $this->content, 'link' => $this->link];
    }
}
