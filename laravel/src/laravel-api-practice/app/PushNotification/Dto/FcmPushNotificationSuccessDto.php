<?php

namespace App\PushNotification\Dto;

use DateTimeImmutable;

class FcmPushNotificationSuccessDto
{
    public function __construct(
        readonly public int $user_id,
        readonly public int $todo_id,
        readonly public string $token,
        readonly public DateTimeImmutable $now,
        readonly public DateTimeImmutable $notificate_at
    ) {}
}
