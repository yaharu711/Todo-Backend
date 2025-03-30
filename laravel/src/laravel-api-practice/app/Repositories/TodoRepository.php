<?php
namespace App\Repositories;

use App\Models\TodoModel;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;

class TodoRepository
{
    public function __construct(readonly DateTimeImmutable $now) {}

    public function getTodo(int $user_id, int $todo_id): TodoModel|null
    {
        $todo_arr = DB::select('
            SELECT 
                todos.*, todo_notification_schedules.notificate_at
            FROM 
                todos
            LEFT OUTER JOIN 
                todo_notification_schedules
            ON 
                todos.id = todo_notification_schedules.todo_id
            WHERE 
                todos.user_id = ?  AND todos.id = ?
        ', [
            $user_id,
            $todo_id,
        ]);
        if (count($todo_arr) === 0) return null;

        return new TodoModel(
            $todo_arr[0]->id,
            $todo_arr[0]->user_id,
            $todo_arr[0]->name,
            $todo_arr[0]->memo,
            is_null($todo_arr[0]->notificate_at) ? null : new DateTimeImmutable($todo_arr[0]->notificate_at),
            new DateTimeImmutable($todo_arr[0]->created_at),
            new DateTimeImmutable($todo_arr[0]->imcompleted_at),
            $todo_arr[0]->is_completed,
            is_null($todo_arr[0]->completed_at) ? null : new DateTimeImmutable($todo_arr[0]->completed_at),
        );
    }

    public function updateTodo(TodoModel $model): void
    {
        DB::statement('
            UPDATE
                todos
            SET
                name = ?,
                memo = ?,
                is_completed = ?,
                imcompleted_at = ?,
                completed_at = ?
            WHERE
                id = ?
        ', [
            $model->name,
            $model->memo,
            $model->is_completed,
            $model->imcompleted_at->format('Y-m-d H:i:s'),
            is_null($model->completed_at) ? null : $model->completed_at->format('Y-m-d H:i:s'), // やっぱりTodoモデルも完了・未完了で分けた方が危険少なそう
            $model->id,
        ]);
        if (is_null($model->notificate_at)) {
            DB::statement('
                DELETE FROM
                    todo_notification_schedules
                WHERE
                    todo_id = ?
            ', [
                $model->id,
            ]);
            return ;
        };

        DB::statement('
            INSERT INTO 
                todo_notification_schedules (
                    todo_id,
                    notificate_at,
                    created_at,
                    updated_at
                )
                VALUES (?, ?, ?, ?)
            ON CONFLICT (todo_id) DO UPDATE
            SET 
                notificate_at = EXCLUDED.notificate_at,
                updated_at = ?
        ', [
            $model->id,
            $model->notificate_at->format('Y-m-d H:i'),
            $this->now->format('Y-m-d H:i:s'),
            $this->now->format('Y-m-d H:i:s'),
            $this->now->format('Y-m-d H:i:s'),
        ]);
    }
}
