<?php

use App\CustomMiddleware\CustomEnsureFrontendRequestsAreStateful;
use App\CustomException\HttpExceptionHandler;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append(CustomEnsureFrontendRequestsAreStateful::class);
        // 以下をONにすると、Auth系で認証を扱う時に、トークン認証も必須となってしまうので注意
        // $middleware->statefulApi();
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $request = request();
        if ($request instanceof Request) {
            $handler = new HttpExceptionHandler($exceptions, $request);
            $handler->handle();
        } else {
            $exceptions->report(function (Throwable $e) {
                Log::error('予期しないエラーが発生しました。', [
                    'Exception detail' => $e,
                ]);
                return false;
            });
        }
    })->create();
