<?php
    return [
        // CORSヘッダーを出力するパスのパターン、任意でワイルドカード(*)が利用できる。
        //全てのルートを対象にする場合: ['*']
       'paths' => ['api/*', 'regist', 'login', 'logout', 'check-login'],

        // マッチするHTTPメソッド。 `[*]` だと全てのリクエストにマッチする。
        //GETとPOSTだけを許可する場合: ['GET', 'POST']
       'allowed_methods' => ['*'],
       'allowed_headers' => ['*'],
        // 許可するリクエストオリジンの設定
        //`*`かオリジンに完全一致、またはワイルドカードが利用可。
       'allowed_origins' => ['http://localhost:5173', "http://dev.practice-react-laravel.site", "https://www.practice-react-laravel.site"],
       'supports_credentials' => true,
    ];
