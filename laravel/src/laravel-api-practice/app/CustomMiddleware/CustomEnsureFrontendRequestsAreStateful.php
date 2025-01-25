<?php
namespace App\CustomMiddleware;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

/**
 * なぜか、Postmanからだとweb.phpで ->withoutMiddleware(ValidateCsrfToken::class);としているのでCSRFトークン検証されず419は返されないが、
 * フロントエンドからのリクエストだと、それだけじゃ足りず以下のCSRFトークン検証をコメントアウト
 * なので、frontendMiddlewareをオーバーライドするためのカスタムクラス
 */
class CustomEnsureFrontendRequestsAreStateful extends EnsureFrontendRequestsAreStateful
{
    /**
     * Get the middleware that should be applied to requests from the "frontend".
     *
     * @return array
     */
    protected function frontendMiddleware()
    {
        $middleware = array_values(array_filter(array_unique([
            config('sanctum.middleware.encrypt_cookies', \Illuminate\Cookie\Middleware\EncryptCookies::class),
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            // config('sanctum.middleware.validate_csrf_token', config('sanctum.middleware.verify_csrf_token', \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class)),
            config('sanctum.middleware.authenticate_session'),
        ])));

        array_unshift($middleware, function ($request, $next) {
            $request->attributes->set('sanctum', true);

            return $next($request);
        });

        return $middleware;
    }

    /**
     * Configure secure cookie sessions.
     *
     * @return void
     */
    protected function configureSecureCookieSessions()
    {
        config([
            'session.lifetime' => 10080,
            'session.http_only' => true,
            'session.same_site' => 'lax',
        ]);
    }
}
