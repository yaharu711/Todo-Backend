<?php
namespace App\PushNotification\Clients\KreaitFirebase;

use App\PushNotification\Dto\FcmPushNotificationErrorDto;
use App\PushNotification\Dto\FcmPushNotificationRequestDto;
use App\PushNotification\Dto\FcmPushNotificationSuccessDto;
use Kreait\Firebase\Messaging\MulticastSendReport;

/**
 * KreaitFirebaseライブラリの関心はApp\Clients\KreaitFirebaseの中で閉じるようにする
 * Serviceクラスなどのビジネスロジックではライブラリの知識を知らなくても良いようにする
 */
class FcmSendAllReport
{
    private MulticastSendReport $report;

    public function __construct(MulticastSendReport $report)
    {
        $this->report = $report;
    }

    /**
     * 成功したレスポンスの配列を返す
     *
     * @return SendReport[]
     */
    private function successes(): array
    {
        return $this->report->successes()->getItems();
    }

    /**
     * 成功したレスポンスの配列を返す
     *
     * @return SendReport[]
     */
    private function failures(): array
    {
        return $this->report->failures()->getItems();
    }

    /**
     * エラーがあるかどうかを返す
     */
    public function hasFailures(): bool
    {
        return $this->report->hasFailures();
    }

    /**
     * Dtoに変換することで、ビジネスロジックの中でライブラリの知識を持たないようにする
     * @param FcmPushNotificationRequestDto[] $notification_request_list
     * @return FcmPushNotificationSuccessDto[]
     */
    public function toSuccessNotificationDtoList(
        array $notification_request_list,
    ): array {
        $notificationsByToken = [];
        foreach ($notification_request_list as $notification) {
            if (isset($notification->token)) {
                $notificationsByToken[$notification->token] = $notification;
            }
        }
        // successes() のレスポンスから、対応する通知を抽出する
        $success_responses = $this->successes();
        $success_notification_dto_list = [];
        foreach ($success_responses as $sendReport) {
            $token = $sendReport->target()->value();
            if (isset($notificationsByToken[$token])) {
                $success_notification_dto_list[] = new FcmPushNotificationSuccessDto(
                    $notificationsByToken[$token]->user_id,
                    $notificationsByToken[$token]->todo_id,
                    $notificationsByToken[$token]->token,
                    $notificationsByToken[$token]->notificated_at
                );
            }
        }
        return $success_notification_dto_list;
    }

    /**
     * Dtoに変換することで、ビジネスロジックの中でライブラリの知識を持たないようにする
     * @param FcmPushNotificationRequestDto[] $notification_request_list
     * @return FcmPushNotificationErrorDto[]
     */
    public function toErrorNotificationDtoList(
        array $notification_request_list,
    ): array {
        $notificationsByToken = [];
        foreach ($notification_request_list as $notification) {
            if (isset($notification->token)) {
                $notificationsByToken[$notification->token] = $notification;
            }
        }
        $failed_notification_dto_list = [];
        foreach ($this->failures() as $sendReport) {
            $token = $sendReport->target()->value();
            $error_message = $sendReport->error()->getMessage();
            $invalidated_argument = $sendReport->messageTargetWasInvalid() | $sendReport->messageWasInvalid() | $sendReport->messageWasSentToUnknownToken(); 
            if (isset($notificationsByToken[$token])) {
                $failed_notification_dto_list[] = new FcmPushNotificationErrorDto(
                    $notificationsByToken[$token]->user_id,
                    $notificationsByToken[$token]->todo_id,
                    $notificationsByToken[$token]->token,
                    $notificationsByToken[$token]->notificated_at,
                    $error_message,
                    $invalidated_argument
                );
            }
        }
        return $failed_notification_dto_list;
    }
}
