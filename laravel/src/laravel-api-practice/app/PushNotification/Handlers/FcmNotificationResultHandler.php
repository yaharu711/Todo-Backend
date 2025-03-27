<?php
namespace App\PushNotification\Handlers;

use App\PushNotification\Dto\FcmPushNotificationResultDto;
use App\PushNotification\Dto\FcmPushNotificationSuccessDto;
use App\PushNotification\Dto\FcmPushNotificationErrorDto;
use App\PushNotification\Dto\NotificationResultDto;
use App\PushNotification\Model\SuccessTodoNotificationScheduleModel;
use App\PushNotification\Repositories\FcmTokenRepository;
use App\PushNotification\Repositories\TodoNotificationScheduleRepository;
use DateTimeImmutable;
use Exception;
use Illuminate\Support\Facades\DB;

class FcmNotificationResultHandler implements NotificationResultHandlerInterface
{
    public function __construct(
        readonly private DateTimeImmutable $now,
        readonly private TodoNotificationScheduleRepository $todo_notification_schedule_repository,
        readonly private FcmTokenRepository $fcm_token_repository
    ){}

    /**
     * TODO: 他の通知処理でも使えるように、SuccessTodoNotificationScheduleModelを受け取るように
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
     * TODO: 他の通知処理でも使えるように、SuccessTodoNotificationScheduleModelを受け取るように
     * @param FcmPushNotificationSuccessDto[] $success_notification_dto_list
     *
     * @throws \Exception
     */
    private function handleSuccess(array $success_notification_dto_list): void
    {
        DB::beginTransaction();
        try {
            $todo_ids = array_map(fn($success_notification_dto) => $success_notification_dto->todo_id, $success_notification_dto_list);
            $this->todo_notification_schedule_repository->deleteScheduleByTodoIds($todo_ids);
                
            // どの通知手段だったかを記録するカラムも追加する。今回で言うと、fcmが入る
            $success_notification_model_list = array_map(function ($success_notification_dto) {
                return new SuccessTodoNotificationScheduleModel(
                    0,
                    $success_notification_dto->user_id,
                    $success_notification_dto->todo_id,
                    $success_notification_dto->notificated_at,
                    $this->now,
                    'FCM',
                );
            }, $success_notification_dto_list);
            $this->todo_notification_schedule_repository->insertSuccessNotificationSchedule($success_notification_model_list);
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
        $invalided_notification_dto_list = [];
        $other_error_notification_dto_list = [];
        foreach ($failed_notification_dto_list as $failed_notification_dto) {
            if ($failed_notification_dto->invalided_argument) {
                $invalided_notification_dto_list[] = $failed_notification_dto;
            } else {
                $other_error_notification_dto_list[] = $failed_notification_dto;
            }
        }
        
        if (count($invalided_notification_dto_list) > 0) {
            DB::beginTransaction();
            try {
                $this->fcm_token_repository->deleteFcmToken($invalided_notification_dto_list);
                // 同じトークンが複数回失敗している場合に備えinvalidated_fcmテーブルにupsert
                $this->fcm_token_repository->insertInvalidatedFcmToken($invalided_notification_dto_list);
                DB::commit();
            } catch (Exception $exception) {
                DB::rollBack();
            }
        }
        
        if (count($other_error_notification_dto_list) > 0) {
            // その他のエラーの場合、どのようなエラーが起きているのか分析するために、エラー通知スケジュールを登録する
            $this->todo_notification_schedule_repository->insertErrorNotificationSchedule([$failed_notification_dto]);
        }
    }
}
