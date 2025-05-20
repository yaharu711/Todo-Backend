<?php

use App\Http\Controllers\CheckExistValidFcmToken;
use App\Http\Controllers\CheckHealthController;
use App\Http\Controllers\CheckLienBotFriendController;
use App\Http\Controllers\CreateTodoController;
use App\Http\Controllers\DeleteTodoController;
use App\Http\Controllers\GetTodosController;
use App\Http\Controllers\ForTestPushNotificationToWebController;
use App\Http\Controllers\InvalidateLatestFcmTokenController;
use App\Http\Controllers\PushNotificationController;
use App\Http\Controllers\SaveFcmTokenController;
use App\Http\Controllers\SortTodosController;
use App\Http\Controllers\UpdateTodoController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::post('/todos', CreateTodoController::class);
    Route::get('/todos', GetTodosController::class);
    Route::patch('/todos/{id}', UpdateTodoController::class);
    Route::delete('/todos/{id}', DeleteTodoController::class);
    Route::put('/todos/sort', SortTodosController::class); // 並び順はリクエストのたびにリクエストされたもので置き換えるので冪等という意味でput
    Route::post('/fcm', SaveFcmTokenController::class);
    Route::post('fcm/invalidate', InvalidateLatestFcmTokenController::class); // fcmトークンは一応残しておきたいから論理削除みたいにしている。
    Route::get('/fcm/check-exist-valid-token', CheckExistValidFcmToken::class);
    Route::get('/line/check-friend', CheckLienBotFriendController::class);
});

# この通知APIは知らない人に叩かれたところでAPIを叩く人起点で何か変わることはなく、アプリケーションのロジックで通知が実行されるかされないか決まるだけなので、認証は不要
# rate limitは入れた方が良いかもしれないが、今回は省略
Route::post('/notification/push-test', ForTestPushNotificationToWebController::class);
Route::post('/notification/push', PushNotificationController::class);
Route::get('/health-check', CheckHealthController::class);
