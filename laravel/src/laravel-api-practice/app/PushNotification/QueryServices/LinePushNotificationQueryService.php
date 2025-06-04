<?php 
declare(strict_types=1);
namespace App\PushNotification\QueryServices;

use App\PushNotification\Dto\LinePushNotificationDto;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;

class LinePushNotificationQueryService
{
    public function __construct(readonly private DateTimeImmutable $now) {}

    /**
     * @return LinePushNotificationDto[]
     */
    public function getLinePushNotificationtDto(): array
    {
        $rows = DB::select('
            SELECT
                line_user_relation.line_user_id,
                todos.id,
                todos.name
            FROM
                todo_notification_schedules
            INNER JOIN
                todos
            ON
                todo_notification_schedules.todo_id = todos.id
            INNER JOIN
                line_user_relation
            ON
                todos.user_id = line_user_relation.user_id
            WHERE
                to_char(todo_notification_schedules.notificate_at, \'YYYY-MM-DD HH24:MI\') = ?  
            AND
                line_user_relation.is_notification = true
        ', [
            $this->now->format('Y-m-d H:i'), // 分単位で通知を設定できる仕様のため、秒数は省いて比較する
        ]);

        return array_map(function ($row) {
            return new LinePushNotificationDto(
                $row->line_user_id,
                $row->id,
                $row->name
            );
        }, $rows);
    }
}
