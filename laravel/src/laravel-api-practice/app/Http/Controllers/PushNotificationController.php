<?php

namespace App\Http\Controllers;

use App\PushNotification\Handlers\FcmNotificationResultHandler;
use App\PushNotification\Services\PushNotificationByFcmService;
use App\PushNotification\Services\PushNotificationByLineService;
use Illuminate\Http\JsonResponse;

class PushNotificationController extends Controller
{
    public function __invoke(
        PushNotificationByFcmService $fcm_messaging,
        FcmNotificationResultHandler $handler,
        PushNotificationByLineService $line_messaging
    ): JsonResponse {
        // LINE通知の方が目玉機能なので、遅延が出ないようにLINE通知を先に実行する
            // 今はFCMの通知がされる時はtodo_notification_schedulesテーブルからレコード削除している
            // しかし、FCMの通知がなければ通知されたレコードも残り続ける→冪等性はなくなるため、よくない
        $line_messaging->run();
        // FCM通知を実行する
        $result = $fcm_messaging->run();
        // 通知の結果をハンドリングする
        // MEMO:
        // 通知の種類ごとの成功、失敗したtodo_idのリストを持ったresultをline_messagingやfcm_messagingから戻り値として受け取り、
        // handlerでsuccessテーブルやfailedテーブルに追加、todo_notification_schedulesテーブルから削除するとか？（本当はSQSとLambdaを使って並列実行が良さそうだけど、まだその段階でもない）
        $handler->handle($result);

        return response()->json(['message' => 'success']);        
    }
}
