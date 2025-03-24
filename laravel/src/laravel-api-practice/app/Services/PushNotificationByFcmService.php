<?php
namespace App\Services;

use App\Clients\KreaitFirebase\KreaitFirebaseClient;
use App\Dto\FcmPushNotificationErrorDto;
use App\Dto\FcmPushNotificationRequestDto;
use App\Dto\FcmPushNotificationSuccessDto;
use DateTimeImmutable;
use Exception;
use Illuminate\Support\Facades\DB;

class PushNotificationByFcmService
{
    public function __construct(
        readonly private DateTimeImmutable $now,
        // このServiceをテストするためには、KreaitFirebaseClientのモックを作成する必要があり、インターフェースが必要
        readonly private KreaitFirebaseClient $messaging
    ){}

    public function run(): void
    {
        try {
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
            if (count($notification_request_list_array) === 0) return;
            $notification_request_list = array_map(function ($notification_request) {
                return new FcmPushNotificationRequestDto(
                    $notification_request->user_id,
                    $notification_request->id,
                    $notification_request->name,
                    $notification_request->token,
                    $notification_request->notificate_at
                );
            }, $notification_request_list_array);

            foreach ($notification_request_list as $notification_request) {
                $message[] = [
                    'token' => $notification_request->token,
                    'webpush' => [
                        'headers' => [  
                            'Urgency' => 'high',
                        ],
                        'notification' => [
                            'title' => $notification_request->todo_name,
                            'body' => 'リマインドです！',
                            'icon' => 'https://todo-laravel-react.s3.ap-northeast-1.amazonaws.com/icon-512x512.png'
                        ],
                        'fcm_options' => [
                            'link' => env('FRONTEND_APP_URL') . '/todos#' . $notification_request->user_id,
                        ]
                    ],
                ];
            }

            $result = $this->messaging->sendAll($message);

            // あと、そろそろRepoも実装しないと、テストの観点や再利用性や単一責任の観点からも良くないね
            // 以下の成功したとき、失敗したときの処理は別のクラスで持ってテストしやすいようにしたいけど、これは同じクラスの責務かな？
            // 成功した通知について抽出する
            $success_notification_dto_list = $result->toSuccessNotificationDtoList($notification_request_list, $this->now);
            self::processSuccessNotifications($success_notification_dto_list, $this->now);
            
            // エラーがなければ、その時点でFCM通知処理終了
            if (!$result->hasFailures()) return;

            // 失敗した通知についても、同様にして抽出する
            $failed_notification_dto_list = $result->toFailedNotificationDtoList($notification_request_list, $this->now);
            // 一部または全部失敗している場合
            self::handleFailures($failed_notification_dto_list, $this->now);
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
    private function processSuccessNotifications(array $success_notification_dto_list): void
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
    private function handleFailures(array $failed_notification_dto_list): void
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
                    $failed_notification_dto,
                    $failed_notification_dto->now,
                ]
            );
        }
    }
}
