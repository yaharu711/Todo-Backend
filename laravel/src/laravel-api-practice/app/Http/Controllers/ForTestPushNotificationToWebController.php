<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Kreait\Firebase\Exception\Messaging\NotFound;
use Kreait\Firebase\Factory;

class ForTestPushNotificationToWebController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $fcm_list = DB::select('select * from fcm');
        if (count($fcm_list) === 0) return response()->json(['message' => '有効なFCMトークンがありません。通知設定でONにしてから再度実行してください'], 400);
        
        $messaging = (new Factory)
            ->withServiceAccount(env('FIREBASE_SERVICE_ACCOUNT_PRIVATE_FILE_PATH'))
            ->createMessaging();

        foreach ($fcm_list as $fcm) {
            $message[] = [
                'token' => $fcm->token,
                'webpush' => [
                    'headers' => [  
                        'Urgency' => 'high',
                    ],
                    'notification' => [
                        'title' => 'Laravel APIからweb push 成功!!' . 'user_id=' . $fcm->user_id,
                        'body' => 'よーし！ChatGPTどんどん使いこなしていく',
                        'icon' => 'https://todo-laravel-react.s3.ap-northeast-1.amazonaws.com/icon-512x512.png'
                    ],
                    'fcm_options' => [
                        'link' => env('FRONTEND_APP_URL') . '/todos',
                    ]
                ],
            ];
        }
        
        try {
            $result = $messaging->sendAll($message);
        } catch (NotFound $exception) {
            // fcmのinvalidated_fcmテーブルにinsertする
            return response()->json(['message' => '有効なFCMトークンがありません'], 404);
        } catch (Exception $exception) {
            return response()->json(['message' => $exception->getMessage()], 500);
        }
    }
}
