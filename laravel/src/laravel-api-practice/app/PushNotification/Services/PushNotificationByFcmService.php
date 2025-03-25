<?php
namespace App\PushNotification\Services;

use App\PushNotification\Clients\KreaitFirebase\KreaitFirebaseClient;
use App\PushNotification\Dto\FcmPushNotificationResultDto;
use App\PushNotification\QueryServices\FcmNotificationQueryService;
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
        readonly private KreaitFirebaseClient $messaging,
        readonly private FcmNotificationQueryService $fcm_notification_query_service
    ){}

    // TODO: とりあえずRepositoryを作成して、DB処理をRepositoryに移譲する
    // 成功した、失敗した時の処理ってアプリケーションのテーブル構成とかに強く依存しているから、Repositoryはapp配下で良いかな
    public function run(): FcmPushNotificationResultDto
    {
        try {
            $notification_request_list = $this->fcm_notification_query_service->getFcmPushNotificationRequestDto();
            if (count($notification_request_list) === 0) return new FcmPushNotificationResultDto([], []);

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
