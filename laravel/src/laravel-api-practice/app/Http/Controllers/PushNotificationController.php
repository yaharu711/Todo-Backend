<?php

namespace App\Http\Controllers;

use App\PushNotification\Clients\KreaitFirebase\KreaitFirebaseClient;
use App\PushNotification\Handlers\FcmNotificationResultHandler;
use App\PushNotification\QueryServices\FcmNotificationQueryService;
use App\PushNotification\QueryServices\LinePushNotificationQueryService;
use App\PushNotification\Repositories\FcmTokenRepository;
use App\PushNotification\Repositories\LineBotMessageRepository;
use App\PushNotification\Repositories\TodoNotificationScheduleRepository;
use App\PushNotification\Services\PushNotificationByFcmService;
use App\PushNotification\Services\PushNotificationByLineService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use DateTimeImmutable;
use Illuminate\Support\Facades\Config;

class PushNotificationController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        // TODO: DIコンテナを使う
        $now = new DateTimeImmutable();
        // 本当はユーザの選択している通知状態をみて、生成するものは変える→messagingのインターフェースも必要
        $messaging = new KreaitFirebaseClient();
        $fcm_query_service = new FcmNotificationQueryService($now);
        $fcm_messaging = new PushNotificationByFcmService($now, $messaging, $fcm_query_service);
        $todo_notification_schedule_repository = new TodoNotificationScheduleRepository($now);
        $fcm_token_repository = new FcmTokenRepository($now);
        $handler = new FcmNotificationResultHandler($now, $todo_notification_schedule_repository, $fcm_token_repository);

        // LINE通知について
        $line_query_service = new LinePushNotificationQueryService($now);
        $line_bot_channel_access_token = Config::get('services.line_bot.access_token');
        $line_bot_message_repository = new LineBotMessageRepository($line_bot_channel_access_token);
        $line_messaging = new PushNotificationByLineService($line_query_service, $line_bot_message_repository);
        // LINE通知の方が目玉機能なので、遅延が出ないようにLINE通知を先に実行する
        $line_messaging->run();

        // 今はFCMの通知がされる時はtodo_notification_schedulesテーブルからレコード削除している
        // しかし、FCMの通知がなければ通知されたレコードも残り続ける→冪等性はなくなるため、よくない
        // TODO: FCMトークンがあれば通知するようにする（if文追加）
        $result = $fcm_messaging->run();
        // 通知の結果をハンドリングする
        //（このようにハンドリングを分離することで、単一責任を守りテストがしやすく影響範囲が閉じた設計にしている）
        $handler->handle($result);

        return response()->json(['message' => 'success']);        
    }
}
