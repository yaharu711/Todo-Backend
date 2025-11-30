<?php
namespace App\Repositories;

use App\Models\TodoModel;
use DateTimeImmutable;
use InvalidArgumentException;
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

        return $this->mapRowToTodoModel($todo_arr[0]);
    }

    /**
     * 未完了のTodoを並び順付きで取得し、filter条件に応じて絞り込む
     *
     * @return TodoModel[]
     */
    public function getImcompletedTodosWithOrderAndFilter(int $user_id, string $filter): array
    {
        $base_sql = '
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
        ';

        $bindings = [$user_id];

        switch ($filter) {
            case 'today':
                $base_sql .= ' AND todo_notification_schedules.notificate_at::date = ?';
                $bindings[] = $this->now->format('Y-m-d');
                break;
            case 'overdue':
                // ユーザーには秒数はわからないため、通知予定時刻（分単位）が現在より前のものを期限切れとして抽出
                $base_sql .= ' AND todo_notification_schedules.notificate_at IS NOT NULL'
                           . ' AND to_char(todo_notification_schedules.notificate_at, \"YYYY-MM-DD HH24:MI\") < ?';
                $bindings[] = $this->now->format('Y-m-d H:i');
                break;
            case 'all':
                break;
            default:
                throw new InvalidArgumentException('Unsupported filter: ' . $filter);
        }

        $base_sql .= ' ORDER BY ord.ord;';

        $rows = DB::select($base_sql, $bindings);

        return array_map(fn($row) => $this->mapRowToTodoModel($row), $rows);
    }

    private function mapRowToTodoModel(object $row): TodoModel
    {
        return new TodoModel(
            $row->id,
            $row->user_id,
            $row->name,
            $row->memo,
            is_null($row->notificate_at) ? null : new DateTimeImmutable($row->notificate_at),
            new DateTimeImmutable($row->created_at),
            new DateTimeImmutable($row->imcompleted_at),
            $row->is_completed,
            is_null($row->completed_at) ? null : new DateTimeImmutable($row->completed_at),
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
