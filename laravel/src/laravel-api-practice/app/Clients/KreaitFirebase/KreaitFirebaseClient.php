<?php
namespace App\Clients\KreaitFirebase;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging;

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

    public function sendAll(array $message): FcmSendAllReport
    {
        $result = $this->messaging->sendAll($message);
        return new FcmSendAllReport($result);
    }
}
