<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveFcmTokenRequest;
use DateTimeImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SaveFcmTokenController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(
        SaveFcmTokenRequest $request,
        DateTimeImmutable $now
    ): JsonResponse {
        $fcm_token = $request->input('fcm_token');
        $user_id = Auth::id();

        DB::statement('
            INSERT INTO fcm (user_id, token, created_at)
            VALUES (?, ?, ?)
            ON CONFLICT (user_id, token) DO NOTHING
        ', [$user_id, $fcm_token, $now]);

        return response()->json(['message' => 'success']);
    }
}
