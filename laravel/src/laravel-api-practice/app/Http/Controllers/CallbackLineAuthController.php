<?php

namespace App\Http\Controllers;

use App\Repositories\LineLoginAccessTokenRepository;
use App\Repositories\LineUserProfileRepository;
use App\Repositories\UserLineRelationRepository;
use DateTimeImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CallbackLineAuthController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $code = $request->get('code');
        $state = $request->get('state');
        $now = new DateTimeImmutable();
        
        if (!$code || !$state) {
            // codeまたはstateが存在しない場合は、403エラーを返す
            return response()->json(['error' => 'query parameter is invalid'], 403);
        }
        if (!$this->isValidState($request, $now, $state)) {
            // セッションにline_loginが存在しない場合は、403エラーを返す
            return response()->json(['error' => 'csrf attack'], 403);
        }

        $line_login_access_token_repository = new LineLoginAccessTokenRepository();
        $line_user_profile_repository = new LineUserProfileRepository();
        $user_line_relation_repository = new UserLineRelationRepository();

        $access_token = $line_login_access_token_repository->iussueAccessToken($code);
        $line_user_id = $line_user_profile_repository->getLineUserIdByLoginApi($access_token);
        $user_id = Auth::id();
        if ($user_id !== null) { // 設定画面からのLINE連携の場合
            $user_line_relation_repository->upsert($user_id, $line_user_id, $now);
            // (デフォルトで302リダイレクト)。設定画面に戻るようにする。
            return redirect()->away(config('app.frontend_url').'/setting');
        }

        // 本番環境にLINE周りとfrontend_app_urlの環境変数を設定する。
        // - 新規登録
            // usersテーブルに新規レコードを作成する
        // - 新規登録済み、連携済みのログイン
            // 何もしないで、Auth::login()を使用して、ユーザーをログインさせる
        // Auth::id()がnullの場合はusersテーブルに新規レコードを作成する
            // すでにレコードが存在する場合は、何もしない
            // これは設定画面からLINEログインを行うユーザもいるため（既存ユーザー用）
        // 取得したユーザーのプロフィール情報を元に、user_line_relationテーブルにレコードを作成する

        // Auth::id()がnullの場合、Auth::login()を使用して、ユーザーをログインさせる
    }

    private function isValidState(Request $request, DateTimeImmutable $now, string $state): bool
    {
        // pullにより、もうセッションから削除される。2回目のリクエストは何かおかしいため、失敗するようにできる。
        // ↑により、実質これnonceと同じようなものかな？
        $line_login = $request->session()->pull('line_login');
        if (!$line_login || $now > $line_login['expires_at']) {
            return false;
        }
        if ($state !== $line_login['state']) {
            return false;
        }
        return true;
    }
}
