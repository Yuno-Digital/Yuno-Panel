<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

/**
 * A simple in-app (database) notification with a title, message and optional link.
 */
class PanelNotification extends Notification
{
    public function __construct(
        public string $title,
        public string $message = '',
        public ?string $url = null,
        public string $level = 'info',
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'url' => $this->url,
            'level' => $this->level,
        ];
    }
}
