<?php

namespace App\Http\Controllers;

use App\Repositories\LineUserRelationRepository;
use DateTimeImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class CheckLineBotFriendController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(): JsonResponse
    {
        $now = new DateTimeImmutable();
        $line_user_profile_repository = new LineUserRelationRepository($now);
        $user_id = Auth::id();

        $line_user_relation = $line_user_profile_repository->getLineUserRelation($user_id);
        if ($line_user_relation === null) {
            // 存在しないということは、そもそもLINEログインをしていない＝友達ではないということになる
            return response()->json([
                'message' => 'success',
                'friend_flag' => false,
            ]);
        }

        return response()->json([
            'message' => 'success',
            'friend_flag' => $line_user_relation->friend_flag,
        ]);
    }
}
