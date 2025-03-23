<?php

namespace App\Http\Controllers;

use App\Services\PushNotificationByFcmService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use DateTimeImmutable;
use Kreait\Firebase\Factory;

class PushNotificationController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $now = new DateTimeImmutable();
        $messaging = (new Factory)
        ->withServiceAccount(env('FIREBASE_SERVICE_ACCOUNT_PRIVATE_FILE_PATH'))
        ->createMessaging();
        PushNotificationByFcmService::run($now, $messaging);
        return response()->json(['message' => 'PushNotificationTriggerController debug']);        
    }
}
