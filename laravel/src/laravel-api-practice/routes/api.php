<?php

use App\Http\Controllers\CheckHealthController;
use App\Http\Controllers\CreateTodoController;
use App\Http\Controllers\DeleteTodoController;
use App\Http\Controllers\GetTodosController;
use App\Http\Controllers\UpdateTodoController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::post('/todos', CreateTodoController::class);
    Route::get('/todos', GetTodosController::class);
    Route::patch('/todos/{id}', UpdateTodoController::class);
    Route::delete('/todos/{id}', DeleteTodoController::class);
});

Route::get('/health-check', CheckHealthController::class);
