<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateTodoRequest;
use App\Repositories\ImcompletedTodoOrderRepository;
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
    public function __invoke(
        UpdateTodoRequest $request,
        DateTimeImmutable $now,
        int $todo_id,
        TodoRepository $todo_repository,
        ImcompletedTodoOrderRepository $imcompleted_todo_order_repository
    ): JsonResponse {
        $user_id = Auth::id();
        $input_name = $request->input('name');
        $input_memo = $request->input('memo');
        $input_notificate_at = $this->calculateNotificateAt($request->input('notificate_at'));
        $input_is_completed = $request->input('is_completed');

        DB::beginTransaction();
        try {
            $todo = $todo_repository->getTodo($user_id, $todo_id);
            if (is_null($todo)) return response()->json(['message' => '指定されたtodoは存在しません'], 404);

            $should_update_name = !is_null($input_name) && $input_name !== $todo->name;
            $should_update_memo = !is_null($input_memo) && $input_memo !== $todo->memo;
            // DateTimeImmutable同士で比較（タイムゾーン差分も考慮したいので==を使用し、等価でない場合のみ更新）
            $should_update_notificate_at = !($input_notificate_at == $todo->notificate_at);
            $should_update_is_completed = !is_null($input_is_completed) && $input_is_completed !== $todo->is_completed;

            // TODO名について
            if ($should_update_name) $todo->name = $input_name;
            // メモについて
            if ($should_update_memo) $todo->memo = $input_memo;
            // 通知の日時について
            // 過去時刻が送られてきても元々登録されている時刻であれば保持したいのと、新しく過去の時刻が送られてきて登録されても特に問題がないため、処理が複雑にならないようにそのまま処理している
            if ($should_update_notificate_at) $todo->notificate_at = $input_notificate_at;
            // 完了・未完了について
            if ($should_update_is_completed) {
                $todo->is_completed = $input_is_completed;
                if ($input_is_completed) {
                    $todo->completed_at = $now;
                    $imcompleted_todo_order_repository->delete($user_id, $todo_id);
                } else {
                    $todo->imcompleted_at = $now;
                    $imcompleted_todo_order_repository->upsertAsFirst($user_id, $todo_id);
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

    private function calculateNotificateAt(?string $input_notificate_at): ?DateTimeImmutable
    {
        if (is_null($input_notificate_at)) return null;

        return new DateTimeImmutable($input_notificate_at);
    }
}
