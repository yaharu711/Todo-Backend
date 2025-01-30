<?php

namespace App\Http\Controllers;

use App\Http\Requests\SortTodosRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SortTodosController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(SortTodosRequest $request): JsonResponse
    {
        $user_id = Auth::user()->id;
        $todos_order = $request->input('todos_order');
        // ちゃんとしたいななら、存在確認や重複していないことを確認もできるがそこまでしなくて良いかな
        DB::table('imcompleted_todo_orders')
            ->where('user_id', $user_id)
            // Postgresが扱える配列の形{}に変換する
            ->update(['imcompleted_todo_order' => DB::raw("'" . '{' . implode(',', $todos_order) . '}' . "'::int[]")]);

        return response()->json(['message' => 'success'], 200);
    }
}
