<?php
namespace App\PushNotification\Repositories;

use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use App\PushNotification\Models\SuccessTodoNotificationScheduleModel;
use App\PushNotification\Dto\FcmPushNotificationErrorDto;

class TodoNotificationScheduleRepository
{
    public function __construct(readonly private DateTimeImmutable $now)
    {}

    public function deleteScheduleByTodoIds(array $todo_ids): void
    {
        DB::table('todo_notification_schedules')
            ->whereIn('todo_id', $todo_ids)
            ->delete();
    }

    /**
    * @param SuccessTodoNotificationScheduleModel[] $success_notification_model_list
     */
    public function insertSuccessNotificationSchedule(array $success_notification_model_list): void
    {
        $insert_data = array_map(function ($success_notification_model) {
            return [
                'todo_id'       => $success_notification_model->todo_id,
                'notificate_at' => $success_notification_model->notificated_at,
                'created_at'    => $this->now,
            ];
        }, $success_notification_model_list);
        DB::table('success_todo_notification_schedules')->insert($insert_data);
    }

    /**
     * @param FcmPushNotificationErrorDto[] $error_notification_model_list
     */
    public function insertErrorNotificationSchedule(array $error_notification_model_list): void
    {
        $insert_data = array_map(function ($error_notification_model) {
            return [
                'todo_id'       => $error_notification_model->todo_id,
                'notificate_at' => $error_notification_model->notificated_at,
                'failed_reason' => $error_notification_model->error_message,
                'created_at'    => $this->now,
            ];
        }, $error_notification_model_list);
        DB::table('failed_todo_notification_schedules')->insert($insert_data);
    }
}
