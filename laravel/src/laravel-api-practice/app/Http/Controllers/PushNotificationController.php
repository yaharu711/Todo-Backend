<?php

namespace App\Http\Controllers;

use App\PushNotification\Clients\KreaitFirebase\KreaitFirebaseClient;
use App\PushNotification\Handlers\FcmNotificationResultHandler;
use App\PushNotification\Services\PushNotificationByFcmService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use DateTimeImmutable;

class PushNotificationController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $now = new DateTimeImmutable();
        $messaging = new KreaitFirebaseClient();
        $messaging = new PushNotificationByFcmService($now, $messaging);
        $handler = new FcmNotificationResultHandler();

        $result = $messaging->run();
        // 通知の結果をハンドリングする
        //（このようにハンドリングを分離することで、単一責任を守りテストがしやすく影響範囲が閉じた設計にしている）
        $handler->handle($result);

        return response()->json(['message' => 'PushNotificationTriggerController debug']);        
    }
}
