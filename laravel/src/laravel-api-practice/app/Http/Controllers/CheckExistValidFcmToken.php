<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckExistValidFcmToken extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(): JsonResponse
    {
        $user_id = Auth::id();
        $row = DB::select('select * from fcm where user_id = ?', [$user_id]);
        $is_exist = count($row) !== 0;
        
        return response()->json(['is_exist' => $is_exist]);
    }
}
