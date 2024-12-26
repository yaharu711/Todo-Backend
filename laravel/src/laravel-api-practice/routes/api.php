<?php

use App\Http\Controllers\CreateTodoController;
use App\Http\Controllers\GetHelloMessageController;
use App\Http\Controllers\GetTodosController;
use App\Http\Controllers\UpdateTodoController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('/hello-message', GetHelloMessageController::class);
    Route::post('/todo', CreateTodoController::class);
    Route::get('/todo', GetTodosController::class);
    Route::post('/todo/{id}', UpdateTodoController::class);
});