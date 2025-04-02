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
        $notificationsByTodoId = [];
        foreach ($notification_request_list as $notification) {
            $notificationsByTodoId[$notification->todo_id] = $notification;
        }

        $success_responses = $this->successes();
        $success_notification_dto_list = [];
        foreach ($success_responses as $sendReport) {
            $row_message = $sendReport->message();
            $todo_id = (int)json_decode(json_encode($row_message), true)["data"]["todo_id"];
            if (isset($notificationsByTodoId[$todo_id])) {
                $success_notification_dto_list[] = new FcmPushNotificationSuccessDto(
                    $notificationsByTodoId[$todo_id]->user_id,
                    $notificationsByTodoId[$todo_id]->todo_id,
                    $notificationsByTodoId[$todo_id]->token,
                    $notificationsByTodoId[$todo_id]->notificated_at
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
        $notificationsByTodoId = [];
        foreach ($notification_request_list as $notification) {
            $notificationsByTodoId[$notification->todo_id] = $notification;
        }
        $failure_responses = $this->failures();
        $failed_notification_dto_list = [];
        foreach ($failure_responses as $sendReport) {
            $row_message = $sendReport->message();
            $todo_id = (int)json_decode(json_encode($row_message), true)["data"]["todo_id"];
            $error_message = $sendReport->error()->getMessage();
            $is_invalidated_request = $sendReport->messageTargetWasInvalid() | $sendReport->messageWasInvalid() | $sendReport->messageWasSentToUnknownToken(); 
            if (isset($notificationsByTodoId[$todo_id])) {
                $failed_notification_dto_list[] = new FcmPushNotificationErrorDto(
                    $notificationsByTodoId[$todo_id]->user_id,
                    $notificationsByTodoId[$todo_id]->todo_id,
                    $notificationsByTodoId[$todo_id]->token,
                    $notificationsByTodoId[$todo_id]->notificated_at,
                    $error_message,
                    $is_invalidated_request
                );
            }
        }

        return $failed_notification_dto_list;
    }
}
