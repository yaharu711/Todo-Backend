<?php

namespace App\PushNotification\Dto;

use DateTimeImmutable;

class FcmPushNotificationErrorDto
{
    public function __construct(
        readonly public int $user_id,
        readonly public int $todo_id,
        readonly public string $token,
        readonly public DateTimeImmutable $notificated_at,
        readonly public string $error_message,
        readonly public bool $invalided_argument
    ) {}
}
