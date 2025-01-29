<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateTodoRequest;
use DateTimeImmutable;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreateTodoController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(CreateTodoRequest $request): JsonResponse
    {
        $now = new DateTimeImmutable();
        $user_id = Auth::user()->id;
        $todoName = $request->input('name');
        try {
            DB::beginTransaction();
            // Todoの作成
            $created_todo_id = DB::table('todos')->insertGetId([
                'name' => $todoName,
                'created_at' => $now->format('Y-m-d H:i:s'),
                'imcompleted_at' => $now->format('Y-m-d H:i:s'),
                'user_id' => $user_id,
            ]);
            // Todoの順序を保存→初回のことを考慮してupsertにしている
            DB::statement("
                INSERT INTO imcompleted_todo_orders (user_id, imcompleted_todo_order)
                VALUES (?, ARRAY[?]::int[])
                ON CONFLICT (user_id)
                DO UPDATE SET imcompleted_todo_order = ARRAY[?]::int[] || imcompleted_todo_orders.imcompleted_todo_order
            ", [$user_id, $created_todo_id, $created_todo_id]);
            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }

        return response()->json([
            'message' => 'success'
        ]);
    }
}
