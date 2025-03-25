<?php

namespace App\Http\Controllers;

use App\PushNotification\Clients\KreaitFirebase\KreaitFirebaseClient;
use App\PushNotification\Handlers\FcmNotificationResultHandler;
use App\PushNotification\QueryServices\FcmNotificationQueryService;
use App\PushNotification\Repositories\TodoNotificationScheduleRepository;
use App\PushNotification\Services\PushNotificationByFcmService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use DateTimeImmutable;

class PushNotificationController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        // TODO: DIコンテナを使う
        $now = new DateTimeImmutable();
        // 本当はユーザの選択している通知状態をみて、生成するものは変える→messagingのインターフェースも必要
        $messaging = new KreaitFirebaseClient();
        $query_service = new FcmNotificationQueryService($now);
        $messaging = new PushNotificationByFcmService($now, $messaging, $query_service);
        $todo_notification_schedule_repository = new TodoNotificationScheduleRepository($now);
        $handler = new FcmNotificationResultHandler($now, $todo_notification_schedule_repository);

        $result = $messaging->run();
        // 通知の結果をハンドリングする
        //（このようにハンドリングを分離することで、単一責任を守りテストがしやすく影響範囲が閉じた設計にしている）
        $handler->handle($result);

        return response()->json(['message' => 'PushNotificationTriggerController debug']);        
    }
}
