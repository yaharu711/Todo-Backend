<?php

namespace App\CustomException;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class HttpExceptionHandler
{
    public function __construct(
        readonly private Exceptions $exceptions,
        readonly private Request $request
    ){}

    public function handle(): void
    {
        $this->report();
        $this->render();
    }

    private function report(): void
    {
        $this->exceptions->report(function (Throwable $e) {
            $context = [
                'User'  => $this->request->user(),
                'Url'       => $this->request->url(),
                'Method'    => $this->request->method(),
                'Request body'   => $this->request->all(),
                'Exception detail' => $e,
            ];
            Log::error('予期しないエラーが発生しました。', $context); 
           
            // デフォルトのエラーハンドラに伝搬しないようにしている
            // https://laravel.com/docs/11.x/errors#reporting-exceptions
            return false;
        });
    }
    
    private function render(): void
    {
        $this->exceptions->render(function (Throwable $e) {
            dd($e->getMessage()); // ここで例外のメッセージをダンプして、デバッグする
            // 認証エラーなど、特定の例外は独自のハンドリングをスキップ
            if ($e instanceof AuthenticationException) {
                return;  // デフォルトのハンドリングに任せる
            }
            return response()->json(['message' => '予期しないエラーが発生しました。'], 500);
        });
    }
}
