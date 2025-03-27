<?php

namespace App\PushNotification\Dto;

class FcmPushNotificationResultDto extends NotificationResultDto
{
    /**
     * @param FcmPushNotificationSuccessDto[] $successes
     * @param FcmPushNotificationErrorDto[] $errors
     */
    public function __construct(
        readonly public array $successes,
        readonly public array $errors
    ) {}
}
