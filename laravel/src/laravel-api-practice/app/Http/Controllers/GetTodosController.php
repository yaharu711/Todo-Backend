<?php

namespace App\Http\Controllers;

use App\Models\TodoModel;
use App\Http\Response\ResponseHelper;
use App\Repositories\TodoRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class GetTodosController extends Controller
{
    public function __construct(private readonly TodoRepository $todo_repository) {}

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $user_id = Auth::user()->id;
        $is_completed_only = $request->query('is_completed_only', 'false') === 'true';
        $filter = $request->query('filter', 'all');

        if ($is_completed_only) {
            return response()->json([
                'todos' => $this->formatTodos(
                    $this->todo_repository->getCompletedTodos($user_id)
                ),
            ]);
        }

        return response()->json([
            'todos' => $this->formatTodos(
                $this->todo_repository->getImcompletedTodosWithOrderAndFilter($user_id, $filter)
            ),
        ]);
    }

    /**
     * レスポンス用に日時をISO 8601（ローカルタイムゾーン）でフォーマットする
     *
     * @param TodoModel[] $todos
     */
    private function formatTodos(array $todos): array
    {
        return array_map(function ($todo) {
            return [
                'id'             => $todo->id,
                'user_id'        => $todo->user_id,
                'name'           => $todo->name,
                'memo'           => $todo->memo ?? '',
                'notificate_at'  => is_null($todo->notificate_at) ? null : ResponseHelper::formatDateTime($todo->notificate_at),
                'created_at'     => ResponseHelper::formatDateTime($todo->created_at),
                'imcompleted_at' => ResponseHelper::formatDateTime($todo->imcompleted_at),
                'is_completed'   => (bool) $todo->is_completed,
                'completed_at'   => is_null($todo->completed_at) ? null : ResponseHelper::formatDateTime($todo->completed_at),
            ];
        }, $todos);
    }
}
