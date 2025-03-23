<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PushNotificationController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        // やることリスト
          // todo_notification_scheduleから今の時刻と一致するものに絞り込み、todo_notification_schedule.todo_idとtodos.idでINNER JOIN、さらにtodos.user_idでfcmのuser_idとINNER JOINして情報を取得
            // 取得する情報は以下の通り
            // todos.id→リンクに埋め込む
            //  todos.name→notificationのtitleに使う
            //  fcm.token→push notificationの送信先
            // userごとに通知するための情報を$messaging->sendAll()の引数に渡せるようにまとめる
            // 通知の内容は以下のようにする
            //         foreach ($notification_list as $notification) {
                    //     $message[] = [
                    //         'token' => $fcm->token,
                    //         'webpush' => [
                    //             'headers' => [  
                    //                 'Urgency' => 'high',
                    //             ],
                    //             'notification' => [
                    //                 'title' => 'Laravel APIからweb push 成功!!' . 'user_id=' . $fcm->user_id,
                    //                 'body' => 'よーし！ChatGPTどんどん使いこなしていく',
                    //                 'icon' => 'https://todo-laravel-react.s3.ap-northeast-1.amazonaws.com/icon-512x512.png'
                    //             ],
                    //             'fcm_options' => [
                    //                 'link' => env('FRONTEND_APP_URL') . '/todos',
                    //             ]
                    //         ],
                    //     ];
                    // }
          // 上記連想配列のリストを$messaging->sendAll();に渡す
          // 通知が成功したら通知テーブルのレコードを削除
          // 通知が失敗したら通知テーブルのレコードを削除して、通知の失敗テーブルにinsert
        return response()->json(['message' => 'PushNotificationTriggerController debug']);        
    }
}
