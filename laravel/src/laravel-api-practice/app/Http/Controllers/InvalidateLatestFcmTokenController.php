<?php

namespace App\Http\Controllers;

use DateTimeImmutable;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InvalidateLatestFcmTokenController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $user_id = Auth::id();
        $now = new DateTimeImmutable();

        DB::beginTransaction();
        try {
            $row = DB::select('select * from fcm where user_id = ? order by created_at desc limit 1', [$user_id]);
            if (count($row) === 0) return response()->json(['message' => 'success']);
            $fcm_token = $row[0]->token;

            DB::statement('delete from fcm where user_id = ? and token = ?', [$user_id, $fcm_token]);
            DB::statement('insert into invalidated_fcm(user_id, token, created_at) values (?, ?, ?)', [$user_id, $fcm_token, $now]);

            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            return response()->json(['message' => "予期せぬエラーが起きました"]);
        }

        return response()->json(['message' => 'success']);
    }
}
