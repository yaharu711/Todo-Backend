<?php
declare(strict_types=1);
namespace App\Repositories;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;

class LineLoginAccessTokenRepository
{
    /**
     * @see https://developers.line.biz/ja/reference/line-login/#issue-access-token
     */
    public function iussueAccessToken(string $code): string
    {
        // TODO: リプレイアタック防止のため、stateだけだと弱いので、PKCEを使うようにする。
        $response = Http::asForm()->post('https://api.line.me/oauth2/v2.1/token', [
            'grant_type'    => 'authorization_code',
            'code'          => $code,
            'redirect_uri'  => Config::get('services.line_login.redirect_uri'),
            'client_id'     => Config::get('services.line_login.client_id'),
            'client_secret' => Config::get('services.line_login.client_secret'),
        ])->throw()->json(); // throw()を使用することで、HTTPエラーが発生した場合に例外をスローします。

        return $response['access_token'];
    }
}