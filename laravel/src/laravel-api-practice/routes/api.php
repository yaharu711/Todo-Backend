<?php

use App\Http\Controllers\GetHelloMessageController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('/hello-message', GetHelloMessageController::class);
});