<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateTodoRequest;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use Throwable;

class CreateTodoController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(CreateTodoRequest $request)
    {
        $now = new DateTimeImmutable();
        $todoName = $request->input('name');
        DB::table('todos')->insert([
            'name' => $todoName,
            'created_at' => $now->format('Y-m-d h:m:s'),
            'imcompleted_at' => $now->format('Y-m-d h:m:s'),
        ]);

        return response()->json([
            'message' => 'success'
        ]);
    }
}
