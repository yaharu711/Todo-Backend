<?php

use App\Http\Controllers\LoginController;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/login', LoginController::class)
    ->withoutMiddleware(ValidateCsrfToken::class);