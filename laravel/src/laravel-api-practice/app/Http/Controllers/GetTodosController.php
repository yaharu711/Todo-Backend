<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GetTodosController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(): JsonResponse
    {
        $user_id = Auth::user()->id;
        $imcompletedTodos = DB::select('SELECT * FROM todos WHERE user_id = ? AND is_completed = false ORDER BY imcompleted_at DESC', [$user_id]);
        $completedTodos = DB::select('SELECT * FROM todos WHERE user_id = ? AND is_completed = true ORDER BY completed_at DESC', [$user_id]);

        return response()->json([
            'imcompletedTodos' => $imcompletedTodos,
            'completedTodos' => $completedTodos,
        ]);
    }
}
