<?php
namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class ImcompletedTodoOrderRepository
{
    public function insertAsFirst(int $user_id, int $todo_id): void
    {
        DB::statement('
            UPDATE imcompleted_todo_orders 
            SET imcompleted_todo_order = ARRAY[?]::int[] || imcompleted_todo_orders.imcompleted_todo_order
            WHERE user_id = ?
        ', [$todo_id, $user_id]);
    }

    public function delete(int $user_id, int $todo_id): void
    {
        // 特定要素へのリクエストなので、配列の規模が大きくなるほどパフォーマンス低下する。。
        DB::statement('
            UPDATE imcompleted_todo_orders
            SET imcompleted_todo_order = array_remove(imcompleted_todo_order, ?)
            WHERE user_id = ?
        ', [$todo_id, $user_id]);
    }
}
