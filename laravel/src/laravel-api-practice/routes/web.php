<?php

use App\Http\Controllers\CheckLoginedController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\RegistController;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// なぜかLaravelの初期画面が表示されるんだけど？？
Route::post('/regist', RegistController::class)
    ->withoutMiddleware(ValidateCsrfToken::class);
Route::post('/login', LoginController::class)
    ->withoutMiddleware(ValidateCsrfToken::class);
Route::post('/logout', LogoutController::class)
    ->withoutMiddleware(ValidateCsrfToken::class);
// 認証系のミドルウェアを使っていないから？挙動がおかしい？でも、だとしたらセッション管理について、どのようなロジックが適応されている？
Route::post('/check-login', CheckLoginedController::class)
    ->withoutMiddleware(ValidateCsrfToken::class);
