<?php
namespace App\PushNotification\Model;

use DateTimeImmutable;

class SuccessTodoNotificationScheduleModel
{
    public function __construct(
        readonly public int $id,
        readonly public int $user_id,
        readonly public int $todo_id,
        readonly public DateTimeImmutable $notificated_at,
        readonly public DateTimeImmutable $created_at,
        readonly public string $notification_type
    ) {}
}
