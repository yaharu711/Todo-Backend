<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateTodoRequest;
use App\Repositories\TodoRepository;
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
        $todo_repository = new TodoRepository(new DateTimeImmutable());
        $user_id = Auth::id();
        $now = new DateTimeImmutable();
        $input_name = $request->input('name');
        $input_memo = $request->input('memo');
        $input_notificate_at = $request->input('notificate_at');
        $input_is_completed = $request->input('is_completed');

        DB::beginTransaction();
        try {
            $todo = $todo_repository->getTodo($user_id, $todo_id);
            if (is_null($todo)) return response()->json(['message' => '指定されたtodoは存在しません'], 404);

            $should_update_name = !is_null($input_name) && $input_name !== $todo->name;
            $should_update_memo = !is_null($input_memo) && $input_memo !== $todo->memo;
            $should_update_notificate_at = !is_null($input_notificate_at) && $input_notificate_at !== $todo->notificate_at;
            $should_update_is_completed = !is_null($input_is_completed) && $input_is_completed !== $todo->is_completed;

            // TODO名について
            if ($should_update_name) $todo->name = $input_name;
            // メモについて
            if ($should_update_memo) $todo->memo = $input_memo;
            // 通知の日時について
            if ($should_update_notificate_at) $todo->notificate_at = new DateTimeImmutable($input_notificate_at);
            // 完了・未完了について
            if ($should_update_is_completed) {
                $todo->is_completed = $input_is_completed;
                if ($input_is_completed) {
                    $todo->completed_at = $now;
                    self::deleteImcompletedTodoOrder($user_id, $todo_id);
                } else {
                    $todo->imcompleted_at = $now;
                    self::addImcompletedTodoOrder($user_id, $todo_id);
                }
            }

            $todo_repository->updateTodo($todo);
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
