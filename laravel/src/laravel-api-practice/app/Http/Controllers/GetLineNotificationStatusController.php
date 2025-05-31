<?php

namespace App\Http\Controllers;

use App\Repositories\LineUserRelationRepository;
use DateTimeImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GetLineNotificationStatusController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $now = new DateTimeImmutable();
        $userId = Auth::id();
        $repository = new LineUserRelationRepository($now);

        $line_user_relation = $repository->getLineUserRelation($userId);
        if ($line_user_relation === null) {
            return response()->json(['message' => 'not found user line relation'], 404);
        }

        return response()->json([
            'message' => 'success',
            'is_notification' => $line_user_relation->is_notification,
        ], 200);
    }
}
