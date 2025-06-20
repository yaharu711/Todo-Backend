<?php

namespace App\Http\Controllers;

use App\Http\Requests\SwitchLineNotificationRequest;
use App\Repositories\LineUserRelationRepository;
use DateTimeImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class SwitchLineNotificationController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(
        SwitchLineNotificationRequest $request,
        DateTimeImmutable $now,
        LineUserRelationRepository $line_user_relation_repository
    ): JsonResponse {
        $is_notification = $request->input('is_notification');
        $userId = Auth::id();
        $line_user_relation_repository = new LineUserRelationRepository($now);

        $line_user_relation_repository->updateNotificationStatus($userId, $is_notification, $now);
        return response()->json(['message' => 'success'], 200);
    }
}
