<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GetTodosController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(): JsonResponse
    {
        $user_id = Auth::user()->id;
        // 未完了のTODOには並び替え機能があるため、以下のようなSQLを実行する
        $imcompletedTodos = DB::select('
            SELECT todos.*
            FROM todos
            JOIN imcompleted_todo_orders
            ON todos.user_id = imcompleted_todo_orders.user_id
            JOIN LATERAL unnest(imcompleted_todo_orders.imcompleted_todo_order) WITH ORDINALITY as ord(todo_id, ord)
            ON todos.id = ord.todo_id
            WHERE todos.user_id = ?  AND todos.is_completed = false
            ORDER BY ord.ord;
        ', [$user_id]);
        $completedTodos = DB::select('SELECT * FROM todos WHERE user_id = ? AND is_completed = true ORDER BY completed_at DESC', [$user_id]);

        return response()->json([
            'imcompletedTodos' => $imcompletedTodos,
            'completedTodos' => $completedTodos,
        ]);
    }
}
