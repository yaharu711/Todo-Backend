<?php

namespace App\Http\Controllers;

use App\Repositories\LineUserRelationRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class GetLineNotificationStatusController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(
        LineUserRelationRepository $line_user_relation_repository
    ): JsonResponse {
        $userId = Auth::id();

        $line_user_relation = $line_user_relation_repository->getLineUserRelation($userId);
        if ($line_user_relation === null) {
            return response()->json([
                'message' => 'not found user line relation',
                'is_notification' => false, // LINE連携していない場合は通知の設定ができないため、falseにする
            ], 200);
        }

        return response()->json([
            'message' => 'success',
            'is_notification' => $line_user_relation->is_notification,
        ], 200);
    }
}
