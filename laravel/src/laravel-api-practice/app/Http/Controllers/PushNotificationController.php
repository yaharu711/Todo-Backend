<?php

namespace App\Http\Controllers;

use App\Clients\KreaitFirebase\KreaitFirebaseClient;
use App\Services\PushNotificationByFcmService;
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
        $messaging->run();
        return response()->json(['message' => 'PushNotificationTriggerController debug']);        
    }
}
