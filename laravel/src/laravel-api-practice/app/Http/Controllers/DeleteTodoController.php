<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DeleteTodoController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(int $id): JsonResponse
    {
        $todo = DB::select('select * from todos where id = :id', [$id]);
        if (count($todo) === 0) return response()->json(['message' => "指定されたtodo（id: {$id}）は存在していません。"], 404);

        DB::table('todos')->where('id', $id)->delete();
        // 削除されていても成功とみなす
        return response()->json(['message' => 'success']);
    }
}
