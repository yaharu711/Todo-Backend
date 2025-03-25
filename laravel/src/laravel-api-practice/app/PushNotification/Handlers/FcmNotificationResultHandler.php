<?php
namespace App\PushNotification\Handlers;

use App\PushNotification\Dto\FcmPushNotificationResultDto;
use App\PushNotification\Dto\NotificationResultDto;
use Exception;
use Illuminate\Support\Facades\DB;

class FcmNotificationResultHandler implements NotificationResultHandlerInterface
{
    /**
     * @param FcmPushNotificationResultDto $dto
     */
    public function handle(NotificationResultDto $dto): void
    {
        $this->handleSuccess($dto->successes);
        $this->handleErrors($dto->errors);   
    }

    /**
     * 成功した通知に対するスケジュール処理を行う
     *
     * @param FcmPushNotificationSuccessDto[] $success_notification_dto_list
     *
     * @throws \Exception
     */
    private function handleSuccess(array $success_notification_dto_list): void
    {
        DB::beginTransaction();
        try {
            $todo_ids = array_map(fn($success_notification_dto) => $success_notification_dto->todo_id, $success_notification_dto_list);
            DB::table('todo_notification_schedules')
                ->whereIn('todo_id', $todo_ids)
                ->delete();
                
            // どの通知手段だったかを記録するカラムも追加する。今回で言うと、fcmが入る
            $insert_data = array_map(function ($success_notification_dto) {
                return [
                    'todo_id'       => $success_notification_dto->todo_id,
                    'notificate_at' => $success_notification_dto->notificate_at,
                    'created_at'    => $success_notification_dto->now,
                ];
            }, $success_notification_dto_list);
            // バッチインサートで一括挿入
            DB::table('success_todo_notification_schedules')->insert($insert_data);
            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }

        /**
     * 失敗したレスポンス（FailedMessage）の配列、通知情報、基準時刻を受け取り、エラーハンドリングを実施する
     *
     * @param FcmPushNotificationErrorDto[] $failed_notification_dto_list
     */
    private function handleErrors(array $failed_notification_dto_list): void
    {
        foreach ($failed_notification_dto_list as $failed_notification_dto) {
            if ($failed_notification_dto->invalided_argument) 
            {
                DB::beginTransaction();
                try {
                    DB::statement(
                        'delete from fcm where user_id = ? and token = ?',
                        [$failed_notification_dto->user_id, $failed_notification_dto->token]
                    );
                    // 同じトークンが複数回失敗している場合に備えinvalidated_fcmテーブルにupsert
                    DB::statement(
                        'insert into invalidated_fcm(user_id, token, created_at) values (?, ?, ?)
                        on conflict(user_id, token) do nothing',
                        [$failed_notification_dto->user_id, $failed_notification_dto->token, $failed_notification_dto->now]
                    );
                    DB::commit();
                    continue;
                } catch (Exception $exception) {
                    DB::rollBack();
                    continue;
                }
            }
            // その他のエラーの場合、failed_todo_notification_schedulesテーブルにinsert
            DB::statement(
                'insert into failed_todo_notification_schedules(todo_id, notificate_at, failed_reason, created_at)
                values (?, ?, ?, ?)',
                [
                    $failed_notification_dto->todo_id,
                    $failed_notification_dto->notificate_at,
                    $failed_notification_dto->error_message,
                    $failed_notification_dto->now,
                ]
            );
        }
    }
}
