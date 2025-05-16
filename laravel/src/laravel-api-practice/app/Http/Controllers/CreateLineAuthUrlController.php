<?php

namespace App\Http\Controllers;

use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

class CreateLineAuthUrlController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        // LINE Developersのコンソールで作成したChannel IDとChannel Secretを使用して、LINE LoginのURLを生成する

        $client_id = Config::get('services.line_login.client_id');
        $redirect = Config::get('services.line_login.redirect_uri');
        $scope = Config::get('services.line_login.scope');
        $auth_base_url  = Config::get('services.line_login.auth_endpoint');
        // LINEログインの認証画面で許可ボタンを押した後に、LINE公式アカウントの追加を促されるため、追加してもらいやすくなる
        $bot_prompt = "&bot_prompt=aggressive";
        $state = Str::uuid()->toString();
        
        // stateはCSRF対策のために使用する、callback APIでアクセストークンを取得する前に検証する時に使う
        $request->session()->put('line_login', [
            'state'         => $state,
            'expires_at'    => CarbonImmutable::now()->addMinutes(10),
        ]);

        $params = http_build_query([
            'response_type'         => 'code',
            'client_id'             => $client_id,
            'redirect_uri'          => $redirect,
            'scope'                 => $scope,
            'state'                 => $state,
            'bot_prompt'           => $bot_prompt,
        ]);

        return response()->json([
            'url' => $auth_base_url . '?' . $params,
        ]);
    }
}
