<?php
namespace App\PushNotification\Services;

use App\PushNotification\Clients\KreaitFirebase\KreaitFirebaseClient;
use App\PushNotification\Dto\FcmPushNotificationRequestDto;
use App\PushNotification\Dto\FcmPushNotificationResultDto;
use DateTimeImmutable;
use Exception;
use Illuminate\Support\Facades\DB;

class PushNotificationByFcmService
{
    public function __construct(
        readonly private DateTimeImmutable $now,
        // このServiceをテストするためには、KreaitFirebaseClientのモックを作成する必要があり、インターフェースが必要
        // sendAll()から返ってくるFcmSendAllReportのモックも作成する必要がある（成功と失敗のオブジェクトは固定値に設定しないといけない）
        // まあ、まだまだテストがしにくいクラスだね、責務の分離がうまくできていないかな
        readonly private KreaitFirebaseClient $messaging
    ){}

    // TODO: とりあえずRepositoryを作成して、DB処理をRepositoryに移譲する
    // 成功した、失敗した時の処理ってアプリケーションのテーブル構成とかに強く依存しているから、Repositoryはapp配下で良いかな
    public function run(): FcmPushNotificationResultDto
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
            if (count($notification_request_list_array) === 0) return new FcmPushNotificationResultDto([], []);
            $notification_request_list = array_map(function ($notification_request) {
                return new FcmPushNotificationRequestDto(
                    $notification_request->user_id,
                    $notification_request->id,
                    $notification_request->name,
                    $notification_request->token,
                    $notification_request->notificate_at
                );
            }, $notification_request_list_array);

            $result = $this->messaging->sendAll($notification_request_list);

            // 成功した通知について抽出する
            $success_notification_dto_list = $result->toSuccessNotificationDtoList($notification_request_list, $this->now);
            $error_notification_dto_list = $result->toErrorNotificationDtoList($notification_request_list, $this->now);

            return new FcmPushNotificationResultDto(
                $success_notification_dto_list,
                $error_notification_dto_list
            );
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }
}
