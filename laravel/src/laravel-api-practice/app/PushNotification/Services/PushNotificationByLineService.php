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
        }
    }
}
