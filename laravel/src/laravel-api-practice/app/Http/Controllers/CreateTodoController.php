<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateTodoRequest;
use App\Repositories\ImcompletedTodoOrderRepository;
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
    public function __invoke(
        CreateTodoRequest $request,
        DateTimeImmutable $now,
        ImcompletedTodoOrderRepository $imcompleted_todo_order_repository
    ): JsonResponse {
        $user_id = Auth::user()->id;
        $todoName = $request->input('name');
        try {
            DB::beginTransaction();
            // Todoの作成
            $created_todo_id = DB::table('todos')->insertGetId([
                'name' => $todoName,
                'memo' => '',
                'created_at' => $now->format('Y-m-d H:i:s'),
                'imcompleted_at' => $now->format('Y-m-d H:i:s'),
                'user_id' => $user_id,
            ]);
            $imcompleted_todo_order_repository->upsertAsFirst(
                $user_id,
                $created_todo_id
            );
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
