<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class CheckLoginedController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(): JsonResponse
    {
        if (is_null(Auth::user())) $is_logined = false;
        $is_logined = !is_null(Auth::user());

        return response()->json(['is_logined' => $is_logined], 200);
    }
}
