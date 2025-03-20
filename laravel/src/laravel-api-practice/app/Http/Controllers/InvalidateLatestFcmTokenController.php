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
            $rows = DB::select('SELECT * FROM fcm WHERE user_id = ?', [$user_id]);
            if (count($rows) === 0) return response()->json(['message' => 'success']);
            $tokens = array_map(fn($row) => $row->token, $rows);

            // 通知をOFFにするためユーザに紐づくトークンは全て削除
            DB::statement('DELETE FROM fcm WHERE user_id = ?', [$user_id]);
        
            $placeholders = implode(',', array_fill(0, count($tokens), '(?, ?, ?)'));
            $bindings = [];
            foreach ($tokens as $token) {
                array_push($bindings, $user_id, $token, $now);
            }
        
            DB::statement("INSERT INTO invalidated_fcm(user_id, token, created_at) VALUES $placeholders ON CONFLICT (user_id, token) DO NOTHING", $bindings);

            DB::commit();
        } catch (Exception $exception) {
            dd($exception->getMessage());
            DB::rollBack();
            return response()->json(['message' => "予期せぬエラーが起きました"], 500);
        }

        return response()->json(['message' => 'success']);
    }
}
