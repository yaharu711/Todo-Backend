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
    public function __invoke(UpdateTodoRequest $request, int $todo_id): JsonResponse
    {
        $now = new DateTimeImmutable();
        $todo_repo = new TodoRepository($now);
        $imcompleted_todo_order_repo = new ImcompletedTodoOrderRepository();
        $user_id = Auth::id();
        $input_name = $request->input('name');
        $input_memo = $request->input('memo');
        $input_notificate_at = $this->calculateNotificateAt($now, $request->input('notificate_at'));
        $input_is_completed = $request->input('is_completed');

        DB::beginTransaction();
        try {
            $todo = $todo_repo->getTodo($user_id, $todo_id);
            if (is_null($todo)) return response()->json(['message' => '指定されたtodoは存在しません'], 404);

            $should_update_name = !is_null($input_name) && $input_name !== $todo->name;
            $should_update_memo = !is_null($input_memo) && $input_memo !== $todo->memo;
            $should_update_notificate_at = $input_notificate_at !== $todo->notificate_at;
            $should_update_is_completed = !is_null($input_is_completed) && $input_is_completed !== $todo->is_completed;

            // TODO名について
            if ($should_update_name) $todo->name = $input_name;
            // メモについて
            if ($should_update_memo) $todo->memo = $input_memo;
            // 通知の日時について
            if ($should_update_notificate_at) $todo->notificate_at = is_null($input_notificate_at) ? null : new DateTimeImmutable($input_notificate_at);
            // 完了・未完了について
            if ($should_update_is_completed) {
                $todo->is_completed = $input_is_completed;
                if ($input_is_completed) {
                    $todo->completed_at = $now;
                    $imcompleted_todo_order_repo->delete($user_id, $todo_id);
                } else {
                    $todo->imcompleted_at = $now;
                    $imcompleted_todo_order_repo->insert($user_id, $todo_id);
                }
            }

            $todo_repo->updateTodo($todo);
            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
        return response()->json(['message' => 'success']);
    }

    private function calculateNotificateAt(DateTimeImmutable $now, ?string $input_notificate_at): ?string
    {
        $now_minute_formatted = $now->format('Y-m-d H:i');
        $input_notificate_at_minute_formatted = (new DateTimeImmutable($input_notificate_at))->format('Y-m-d H:i');
        $is_input_notificate_at_before_now = !is_null($input_notificate_at) && $now_minute_formatted >= $input_notificate_at_minute_formatted;
        // 通知を設定して、通知の時間が来るまで（来たあとも）リロードしていないユーザがいる
        // その時memoの更新などを行った時、再度notificate_atが登録されてしまうことを防いでいる
        return $is_input_notificate_at_before_now ? null : $input_notificate_at;
    }
}
