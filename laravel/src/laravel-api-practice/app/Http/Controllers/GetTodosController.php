<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class GetTodosController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $user_id = Auth::user()->id;
        $is_completed_only = $request->query('is_completed_only', 'false') === 'true';

        if ($is_completed_only) {
            $todos = DB::select('SELECT * FROM todos WHERE user_id = ? AND is_completed = true ORDER BY completed_at DESC', [$user_id]);
            return response()->json([
                'todos' => $todos,
            ]);
        }

        // 未完了のTODOには並び替え機能があるため、以下のようなSQLを実行する
        $todos = DB::select('
            SELECT 
                todos.*, todo_notification_schedules.notificate_at
            FROM 
                todos
            INNER JOIN 
                imcompleted_todo_orders
            ON todos.user_id = imcompleted_todo_orders.user_id
            JOIN LATERAL 
                unnest(imcompleted_todo_orders.imcompleted_todo_order) WITH ORDINALITY as ord(todo_id, ord)
            ON todos.id = ord.todo_id
            LEFT OUTER JOIN 
                todo_notification_schedules
            ON todos.id = todo_notification_schedules.todo_id
            WHERE 
                todos.user_id = ?  
                AND todos.is_completed = false
            ORDER BY ord.ord;
        ', [$user_id]);

        return response()->json([
            'todos' => $todos,
        ]);
    }
}
