<?php

use App\Http\Controllers\GetHelloMessageController;
use Illuminate\Support\Facades\Route;

Route::get('/hello-message', GetHelloMessageController::class);