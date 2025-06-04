<?php
declare(strict_types=1);
namespace App\PushNotification\Services;

use App\PushNotification\Dto\LinePushNotificationMessageDto;
use App\PushNotification\QueryServices\LinePushNotificationQueryService;
use App\PushNotification\Repositories\LineBotMessageRepository;
use Illuminate\Support\Facades\Log;
use Throwable;

class PushNotificationByLineService
{
    public function __construct(
        private readonly LinePushNotificationQueryService $line_push_notification_query_service,
        private readonly LineBotMessageRepository $line_bot_message_repository
    ) {}

    public function run(): void
    {
        $dtos = $this->line_push_notification_query_service->getLinePushNotificationtDto();

        foreach ($dtos as $dto) {
            $message = LinePushNotificationMessageDto::createMessage(
                $dto->todo_id,
                $dto->todo_name
            );

            try {
                $this->line_bot_message_repository->pushNotification(
                    $dto->line_user_id,
                    $message
                );
            } catch (Throwable $e) { // 特定のTodo通知で起きる場合があり、他のTodo通知処理に影響を出さないためThrowableをキャッチする
                $context = [
                    'line_user_id'  => $dto->line_user_id,
                    'todo_id'       => $dto->todo_id,
                    'todo_name'     => $dto->todo_name,
                    'Exception detail'       => $e->getMessage()
                ];
                Log::error('Lineの通知処理で予期しないエラーが発生しました。', $context); 
            }
            // 実際にtodo通知のAPIを呼び出し、通知を実行する
            // todo_notification_schedulesテーブルからいつレコードを削除しようか、、
            // そして、success_todo_notification_schedulesテーブルに移動するのは、どうする？notification_typeも追加していないし、
                // 一応PKがidなので、同じtodo_idに対してレコード挿入できるからLINEの通知処理が終わったらまとめてINSERTしたい
            // $this->line_bot_service->pushMessage($dto->line_user_id, $dto->todo_name);
            // handleSuccessは一応FCMとLINEで共通の処理にできそう
                // todo_notification_schedulesのレコード削除とsuccess_todo_notification_schedulesテーブルに挿入
        }
    }
}
