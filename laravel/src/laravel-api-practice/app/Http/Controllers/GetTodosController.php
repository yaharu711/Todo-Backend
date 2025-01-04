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
        $todoArray = DB::select('SELECT * FROM todos ORDER BY id DESC');

        return response()->json([
            'imcompletedTodos' => array_values(array_filter($todoArray, function($todo) {
               return !$todo->is_completed;
            })),
            'completedTodos' => array_values(array_filter($todoArray, function($todo) {
               return $todo->is_completed;
            })),
        ]);
    }
}
