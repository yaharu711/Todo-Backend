<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetHellowMessageRequest;

class GetHelloMessageController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(GetHellowMessageRequest $request)
    {
        return response()->json([
            'message' => 'hellow world'
        ], 200);
    }
}
