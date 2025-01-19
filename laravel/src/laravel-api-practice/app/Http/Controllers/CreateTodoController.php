<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateTodoRequest;
use DateTimeImmutable;
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
        DB::table('todos')->insert([
            'name' => $todoName,
            'created_at' => $now->format('Y-m-d h:m:s'),
            'imcompleted_at' => $now->format('Y-m-d h:m:s'),
            'user_id' => $user_id,
        ]);

        return response()->json([
            'message' => 'success'
        ]);
    }
}
