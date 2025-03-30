<?php
namespace App\Repositories;

use App\Models\TodoModel;
use Exception;
use Illuminate\Support\Facades\DB;

class TodoRepository
{
    public function updateTodo(TodoModel $model): void
    {
        DB::beginTransaction();
        try {
            DB::statement('
                UPDATE
                    todos
                SET
                    name = ?
                    memo = ?
                    is_completed = ?
                WHERE
                    id = ?
            ', [
                $model->name,
                $model->memo,
                $model->is_completed,
                $model->id,
            ]);
            DB::statement('
                UPDATE
                    todo_notification_schedules
                SET
                    notificate_at = ?
                WHERE
                    id = ?
            ', [
                $model->notificate_at,
                $model->id,
            ]);
            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }
}
