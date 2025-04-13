<?php

namespace App\Exception;

use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class CustomExceptionHandlerInHttp
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
            return response()->json(['messege' => '予期しないエラーが発生しました。'], 500);
        });
    }
}
