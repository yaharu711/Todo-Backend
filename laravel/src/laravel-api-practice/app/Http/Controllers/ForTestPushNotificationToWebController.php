<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $user_id = Auth::id();
        $fcm = DB::select('select * from fcm where user_id = ? order by created_at desc limit 1', [$user_id])[0];

        $messaging = (new Factory)
            ->withServiceAccount(env('FIREBASE_SERVICE_ACCOUNT_PRIVATE_FILE_PATH'))
            ->createMessaging();

        $message = [
            'token' => $fcm->token,
            'webpush' => [
                'headers' => [
                    'Urgency' => 'high',
                ],
                'notification' => [
                    'title' => 'Laravel APIからweb push 成功!!',
                    'body' => 'よーし！ChatGPTどんどん使いこなしていく',
                    // 'requireInteraction' => 'true',
                ],
            ],
        ];
        
        try {
            $messaging->send($message);
        } catch (NotFound $exception) {
            // fcmのinvalid_flgをtrueにする
            return response()->json(['message' => '有効なFCMトークンがありません'], 404);
        } catch (Exception $exception) {
            return response()->json(['message' => $exception->getMessage()], 500);
        }
    }
}
