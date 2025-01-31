<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DeleteTodoController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(int $todo_id): JsonResponse
    {
        DB::beginTransaction();
        try {
            $todo = DB::select('select * from todos where id = :id', [$todo_id]);
            if (count($todo) === 0) return response()->json(['message' => "指定されたtodo（id: {$todo_id}）は存在していません。"], 404);
            DB::table('todos')->where('id', $todo_id)->delete();

            // 未完了TODOの並び順を更新
            $user_id = Auth::id();
            DB::statement('UPDATE imcompleted_todo_orders
                SET imcompleted_todo_order = array_remove(imcompleted_todo_order, ?)
                WHERE user_id = ?
            ', [$todo_id, $user_id]);

            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }

        return response()->json(['message' => 'success']);
    }
}
