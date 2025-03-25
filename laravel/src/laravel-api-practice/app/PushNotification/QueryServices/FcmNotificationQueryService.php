<?php
namespace App\PushNotification\QueryServices;

use App\PushNotification\Dto\FcmPushNotificationRequestDto;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;

class FcmNotificationQueryService
{
    public function __construct(readonly private DateTimeImmutable $now) {}

    /**
     * @return FcmPushNotificationRequestDto[]
     */
    public function getFcmPushNotificationRequestDto(): array
    {
        $notification_request_list_array = DB::select('
                SELECT DISTINCT
                    todos.id,
                    todos.name,
                    fcm.token,
                    fcm.user_id,
                    todo_notification_schedules.notificate_at
                FROM
                    todo_notification_schedules
                INNER JOIN
                    todos
                ON
                    todo_notification_schedules.todo_id = todos.id
                INNER JOIN
                    fcm
                ON
                    todos.user_id = fcm.user_id
                WHERE
                    to_char(todo_notification_schedules.notificate_at, \'YYYY-MM-DD HH24:MI\') = ?
            ', [
                $this->now->format('Y-m-d H:i'),
        ]);

        return array_map(function ($notification_request) {
            return new FcmPushNotificationRequestDto(
                $notification_request->user_id,
                $notification_request->id,
                $notification_request->name,
                $notification_request->token,
                new DateTimeImmutable($notification_request->notificate_at)
            );
        }, $notification_request_list_array);
    }
}
