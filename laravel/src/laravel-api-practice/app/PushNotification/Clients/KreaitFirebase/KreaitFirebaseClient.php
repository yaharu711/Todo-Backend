<?php
namespace App\PushNotification\Clients\KreaitFirebase;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging;
use App\PushNotification\Dto\FcmPushNotificationRequestDto;
use App\PushNotification\PushNotificationContent;

/**
 * KreaitFirebaseライブラリの関心はApp\Clients\KreaitFirebaseの中で閉じるようにする
 * Serviceクラスなどのビジネスロジックではライブラリの知識を知らなくても良いようにする
 */
class KreaitFirebaseClient
{
    private readonly Messaging $messaging;

    public function __construct()
    {
        $this->messaging = (new Factory)
            ->withServiceAccount(env('FIREBASE_SERVICE_ACCOUNT_PRIVATE_FILE_PATH'))
            ->createMessaging();
    }

    /**
     * FCMで送信する
     *
     * @param FcmPushNotificationRequestDto[] $notification_request_list
     * @return FcmSendAllReport
     */
    public function sendAll(array $notification_request_list): FcmSendAllReport
    {
        foreach ($notification_request_list as $notification_request) {
            // TODO: 通知の設定内容を汎用化したが、これは通知の手段によって意外に変わるので、意味ないので、PushNotificationContentクラスをやめてこのクラスに凝集する
            $message[] = [
                'token' => $notification_request->token,
                'webpush' => [
                    'headers' => [  
                        'Urgency' => PushNotificationContent::URGENCY,
                    ],
                    'notification' => [
                        'title' => PushNotificationContent::getTitle($notification_request->todo_name),
                        'body' => PushNotificationContent::BODY,
                        'icon' => PushNotificationContent::ICON,
                    ],
                    'fcm_options' => [
                        'link' => PushNotificationContent::getLink($notification_request->todo_id),
                    ]
                ],
            ];
        }

        $result = $this->messaging->sendAll($message);

        return new FcmSendAllReport($result);
    }
}
