<?php

namespace App\Http\Controllers;

use App\Repositories\TodoRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;

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
            $todos = DB::select('SELECT * FROM todos WHERE user_id = ? AND is_completed = true ORDER BY completed_at DESC', [$user_id]);
            return response()->json([
                'todos' => $this->formatTodos($todos),
            ]);
        }

        $todos = $this->todo_repository->getImcompletedTodosWithOrderAndFilter($user_id, $filter);

        return response()->json([
            'todos' => $this->formatTodos($todos),
        ]);
    }

    /**
     * レスポンス用に日時をISO 8601（ローカルタイムゾーン）でフォーマットする
     */
    private function formatTodos(array $todos): array
    {
        $tz = new DateTimeZone(config('app.timezone'));

        $formatDate = function ($value) use ($tz) {
            if (is_null($value)) return null;
            if ($value instanceof DateTimeImmutable) {
                return $value->setTimezone($tz)->format(DateTimeInterface::ATOM);
            }
            // DB::select の結果は文字列なのでパース
            return (new DateTimeImmutable($value))->setTimezone($tz)->format(DateTimeInterface::ATOM);
        };

        return array_map(function ($todo) use ($formatDate) {
            return [
                'id'             => $todo->id,
                'user_id'        => $todo->user_id,
                'name'           => $todo->name,
                'memo'           => $todo->memo ?? '',
                'notificate_at'  => $formatDate($todo->notificate_at ?? null),
                'created_at'     => $formatDate($todo->created_at ?? null),
                'imcompleted_at' => $formatDate($todo->imcompleted_at ?? null),
                'is_completed'   => (bool) $todo->is_completed,
                'completed_at'   => $formatDate($todo->completed_at ?? null),
            ];
        }, $todos);
    }
}
