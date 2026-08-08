<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SystemNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $titleKey,
        private readonly string $messageKey,
        private readonly array $parameters,
        private readonly string $url,
        private readonly string $category,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title_key' => $this->titleKey,
            'message_key' => $this->messageKey,
            'parameters' => $this->parameters,
            'url' => $this->url,
            'category' => $this->category,
        ];
    }
}
