<?php

namespace App\Http\Controllers;

class CheckHealthController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke()
    {
        return response()->json(['message' => 'success']);
    }
}
