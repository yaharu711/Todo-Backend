<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateTodoRequest;
use DateTimeImmutable;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UpdateTodoController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(UpdateTodoRequest $request, int $todo_id): JsonResponse
    {
        $user_id = Auth::id();
        $now = new DateTimeImmutable();
        $input_name = $request->input('name');
        $input_memo = $request->input('memo');
        $input_is_completed = $request->input('is_completed');

        DB::beginTransaction();
        try {
            $todo = DB::select('select * from todos where id = :id', [$todo_id]);
            if (count($todo) === 0) return response()->json(['message' => "指定されたtodo（id: {$todo_id}）は存在していません。"], 404);

            $updated_todo_arr = [];
            $should_update_name = !is_null($input_name) && $input_name !== $todo[0]->name;
            $should_update_memo = !is_null($input_memo) && $input_memo !== $todo[0]->memo;
            $should_update_is_completed = !is_null($input_is_completed) && $input_is_completed !== $todo[0]->is_completed;

            // TODO名について
            if ($should_update_name) $updated_todo_arr['name'] = $input_name;
            // メモについて
            if ($should_update_memo) $updated_todo_arr['memo'] = $input_memo;
            // 完了・未完了について
            if ($should_update_is_completed) {
                $updated_todo_arr['is_completed'] = $input_is_completed;
                if ($input_is_completed) {
                    $updated_todo_arr['completed_at'] = $now->format('Y-m-d H:i:s');
                    self::deleteImcompletedTodoOrder($user_id, $todo_id);
                } else {
                    $updated_todo_arr['imcompleted_at'] = $now->format('Y-m-d H:i:s');
                    self::addImcompletedTodoOrder($user_id, $todo_id);
                }
            }

            if (count($updated_todo_arr) !== 0) DB::table('todos')
                ->where('id', $todo_id)
                ->update($updated_todo_arr);
            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
        return response()->json(['message' => 'success']);
    }

    private static function addImcompletedTodoOrder(int $user_id, int $todo_id): void
    {
        DB::statement('
            UPDATE imcompleted_todo_orders 
            SET imcompleted_todo_order = ARRAY[?]::int[] || imcompleted_todo_orders.imcompleted_todo_order
            WHERE user_id = ?
        ', [$todo_id, $user_id]);
    }

    private static function deleteImcompletedTodoOrder(int $user_id, int $todo_id): void
    {
        // 特定要素へのリクエストなので、配列の規模が大きくなるほどパフォーマンス低下する。。
        DB::statement('
            UPDATE imcompleted_todo_orders
            SET imcompleted_todo_order = array_remove(imcompleted_todo_order, ?)
            WHERE user_id = ?
        ', [$todo_id, $user_id]);
    }
}
