<?php 
namespace App\PushNotification\Dto;

use DateTimeImmutable;

class FcmPushNotificationRequestDto
{
    public function __construct(
        readonly public int $user_id,
        readonly public int $todo_id,
        readonly public string $todo_name,
        readonly public string $token,
        readonly public DateTimeImmutable $notificated_at
    ) {}
}
