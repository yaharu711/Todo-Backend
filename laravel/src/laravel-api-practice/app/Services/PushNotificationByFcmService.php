<?php
namespace App\Services;

use App\Dto\FcmPushNotificationErrorDto;
use App\Dto\FcmPushNotificationSuccessDto;
use DateTimeImmutable;
use Exception;
use Illuminate\Support\Facades\DB;
use Kreait\Firebase\Contract\Messaging;

class PushNotificationByFcmService
{
    public static function run(DateTimeImmutable $now, Messaging $messaging): void
    {
        try {
            $notification_request_list = DB::select('
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
                $now->format('Y-m-d H:i'),
            ]);
            if (count($notification_request_list) === 0) return;

            foreach ($notification_request_list as $notification_request) {
                $message[] = [
                    'token' => $notification_request->token,
                    'webpush' => [
                        'headers' => [  
                            'Urgency' => 'high',
                        ],
                        'notification' => [
                            'title' => $notification_request->name,
                            'body' => 'リマインドです！',
                            'icon' => 'https://todo-laravel-react.s3.ap-northeast-1.amazonaws.com/icon-512x512.png'
                        ],
                        'fcm_options' => [
                            'link' => env('FRONTEND_APP_URL') . '/todos#' . $notification_request->id,
                        ]
                    ],
                ];
            }

            $result = $messaging->sendAll($message);

            // ここから下の成功した通知と失敗した通知の処理の実装についてリファクタリングしたい
            // messagingをラップしたクラスを作成して、それを使って処理を行うように変更する？
            // FcmPushNotificationSuccessDto[]とFcmPushNotificationErrorDto[]を返すメソッドをラップしたクラスに作ろう
            // 実際に成功した時の処理と失敗した時の処理はこのクラスのprivateメソッドのままで良さそう。
            // これにより、メッセージングのライブラリの知識を隠蔽できて、アプリケーションロジックと分離できる
            // あと、そろそろRepoも実装しないと、テストの観点や再利用性や単一責任の観点からも良くないね
            $notificationsByToken = [];
            foreach ($notification_request_list as $notification) {
                if (isset($notification->token)) {
                    $notificationsByToken[$notification->token] = $notification;
                }
            }
            // successes() のレスポンスから、対応する通知を抽出する
            $success_responses = $result->successes()->getItems();
            $success_notification_dto_list = [];
            foreach ($success_responses as $sendReport) {
                $token = $sendReport->target()->value();
                if (isset($notificationsByToken[$token])) {
                    $success_notification_dto_list[] = new FcmPushNotificationSuccessDto(
                        $notificationsByToken[$token]->user_id,
                        $notificationsByToken[$token]->id,
                        $notificationsByToken[$token]->token,
                        $now,
                        new DateTimeImmutable($notificationsByToken[$token]->notificate_at)
                    );
                }
            }
            self::processSuccessNotifications($success_notification_dto_list, $now);
            
            if (!$result->hasFailures()) return;

            // 失敗した通知についても、同様にして抽出する
            $failed_notification_dto_list = [];
            foreach ($result->failures()->getItems() as $sendReport) {
                $token = $sendReport->target()->value();
                $error_message = $sendReport->error()->getMessage();
                $invalidated_argument = $sendReport->messageTargetWasInvalid() | $sendReport->messageWasInvalid() | $sendReport->messageWasSentToUnknownToken(); 
                if (isset($notificationsByToken[$token])) {
                    $failed_notification_dto_list[] = new FcmPushNotificationErrorDto(
                        $notificationsByToken[$token]->user_id,
                        $notificationsByToken[$token]->id,
                        $notificationsByToken[$token]->token,
                        $now,
                        new DateTimeImmutable($notificationsByToken[$token]->notificate_at),
                        $error_message,
                        $invalidated_argument
                    );
                }
            }
            // 一部または全部失敗している場合
            self::handleFailures($failed_notification_dto_list, $now);

            // 通知が成功したかどうかの確認をする
            // 全件成功していたらtodo_notification_schedulesテーブルからレコード削除して、成功した通知の情報をsuccess_todo_notification_schedulesテーブルに記録
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }

    /**
     * 成功した通知に対するスケジュール処理を行う
     *
     * @param FcmPushNotificationSuccessDto[] $success_notification_dto_list
     *
     * @throws \Exception
     */
    private static function processSuccessNotifications(array $success_notification_dto_list): void
    {
        DB::beginTransaction();
        try {
            $todo_ids = array_map(fn($success_notification_dto) => $success_notification_dto->todo_id, $success_notification_dto_list);
            DB::table('todo_notification_schedules')
                ->whereIn('todo_id', $todo_ids)
                ->delete();
                
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
    private static function handleFailures(array $failed_notification_dto_list): void
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
                [$failed_notification_dto->todo_id, $failed_notification_dto->notificate_at, $failed_notification_dto, $failed_notification_dto->now]
            );
        }
    }
}
