<?php
declare(strict_types=1);

namespace App\PushNotification\Dto;

class LinePushNotificationDto
{
    public function __construct(
        public readonly string $line_user_id,
        public readonly int $todo_id,
        public readonly string $todo_name,
    ) {}
}
