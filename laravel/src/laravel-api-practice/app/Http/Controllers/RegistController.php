<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegistRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class RegistController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(RegistRequest $request): JsonResponse
    {
        $user = DB::select('select * from users where email = ?', [$request->email]);
        if (count($user) !== 0) return response()->json(['errors' => ['email' => ['duplicated email']]], 422);
  
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        Auth::logout();
        Session::invalidate();

        return response()->json(['message' => 'success'], 200);
    }
}
