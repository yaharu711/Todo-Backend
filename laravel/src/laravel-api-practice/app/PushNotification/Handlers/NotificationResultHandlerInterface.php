<?php
namespace App\PushNotification\Handlers;

use App\PushNotification\Dto\NotificationResultDto;

interface NotificationResultHandlerInterface
{
    public function handle(NotificationResultDto $dto): void;
}