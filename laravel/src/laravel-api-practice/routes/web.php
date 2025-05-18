<?php

use App\Http\Controllers\CallbackLineAuthController;
use App\Http\Controllers\CheckLoginedController;
use App\Http\Controllers\CreateLineAuthUrlController;
use App\Http\Controllers\HandleLineWebhookController;
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

// LINEログインのためのURLを生成するAPI
Route::get('line-auth/url', CreateLineAuthUrlController::class);
// Callback先のAPIを実装
Route::get('line-auth/callback', CallbackLineAuthController::class);
// プロフィールを取得するAPIを実装

// Line WebhookのためのAPI
Route::post('line/webhook', HandleLineWebhookController::class)
    ->withoutMiddleware(ValidateCsrfToken::class);
