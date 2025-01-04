<?php

use App\Http\Controllers\CheckLoginedController;
use App\Http\Controllers\CreateTodoController;
use App\Http\Controllers\DeleteTodoController;
use App\Http\Controllers\GetHelloMessageController;
use App\Http\Controllers\GetTodosController;
use App\Http\Controllers\UpdateTodoController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('/hello-message', GetHelloMessageController::class);
    Route::post('/todos', CreateTodoController::class);
    Route::get('/todos', GetTodosController::class);
    Route::patch('/todos/{id}', UpdateTodoController::class);
    Route::delete('/todos/{id}', DeleteTodoController::class);
});

Route::post('/check-login', CheckLoginedController::class);
