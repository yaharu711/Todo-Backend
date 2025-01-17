<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class GetTodosController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(): JsonResponse
    {
        $imcompletedTodos = DB::select('SELECT * FROM todos where is_completed = false ORDER BY imcompleted_at DESC');
        $completedTodos = DB::select('SELECT * FROM todos where is_completed = true ORDER BY completed_at DESC');

        return response()->json([
            'imcompletedTodos' => $imcompletedTodos,
            'completedTodos' => $completedTodos,
        ]);
    }
}
