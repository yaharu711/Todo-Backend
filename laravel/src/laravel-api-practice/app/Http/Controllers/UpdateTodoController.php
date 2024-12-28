<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateTodoRequest;
use DateTimeImmutable;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class UpdateTodoController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(UpdateTodoRequest $request, int $id): JsonResponse
    {
        $now = new DateTimeImmutable();
        $input_name = $request->input('name');
        $input_is_completed = $request->input('is_completed');

        DB::beginTransaction();
        try {
            $todo = DB::select('select * from todos where id = :id', [$id]);

            $updated_todo_arr = [];
            $should_update_name = !is_null($input_name) && $input_name !== $todo[0]->name;
            $should_update_is_completed = !is_null($input_is_completed) && $input_is_completed !== $todo[0]->is_completed;

            // TODO名について
            if ($should_update_name) $updated_todo_arr['name'] = $input_name;
            // 完了・未完了について
            if ($should_update_is_completed) {
                $updated_todo_arr['is_completed'] = $input_is_completed;
                if ($input_is_completed) {
                    $updated_todo_arr['completed_at'] = $now->format('Y-m-d H:m:s');
                } else {
                    $updated_todo_arr['imcompleted_at'] = $now->format('Y-m-d H:m:s');
                }
            }

            if (count($updated_todo_arr) !== 0) DB::table('todos')
                ->where('id', $id)
                ->update($updated_todo_arr);
            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
        return response()->json(['message' => 'success']);
    }
}
