<?php

namespace App\Http\Controllers;

use App\Model\UserModel;
use App\Repositories\LineLoginAccessTokenRepository;
use App\Repositories\LineUserProfileRepository;
use App\Repositories\LineUserRelationRepository;
use App\Repositories\UserRepository;
use DateTimeImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CallbackLineAuthController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(
        Request $request,
        LineLoginAccessTokenRepository $line_login_access_token_repository,
        LineUserProfileRepository $line_user_profile_repository,
        LineUserRelationRepository $line_user_relation_repository,
        UserRepository $user_repository,
        DateTimeImmutable $now
    ) {
        $code = $request->get('code');
        $state = $request->get('state');
        
        if (!$code || !$state) {
            // codeまたはstateが存在しない場合は、403エラーを返す
            return response()->json(['error' => 'query parameter is invalid'], 403);
            return response()->json(['error' => 'query parameter is invalid'], 403);
        }
        if (!$this->isValidState($request, $now, $state)) {
            // セッションにline_loginが存在しない場合は、403エラーを返す
            return response()->json(['error' => 'csrf attack'], 403);
        }

        // TODO: 毎回アクセストークンを発行しているが、アクセストークンの有効期限が切れた場合にのみ発行するようにする。
        $access_token = $line_login_access_token_repository->issueAccessToken($code);
        $line_user_profile = $line_user_profile_repository->getLineUserIdByLoginApi($access_token);
        $user_id = Auth::id();
        // ログイン済みであり、設定画面からのLINE連携の場合
        if ($user_id !== null) {
            // TODO: 同じLINEアカウントで別のユーザーとしてログインする場合はエラーになる。
            // その時は、Slackに通知してユーザーには500ページを表示するようにする。
            $line_user_relation_repository->upsert($user_id, $line_user_profile->line_user_id, $now);
            // (デフォルトで302リダイレクト)。
            return redirect()->away(config('app.frontend_setting_page'));
        }

        $line_user_relation = $line_user_relation_repository->getLineUserRelationByLineUserId($line_user_profile->line_user_id);
        // ログインしていないかつ、連携やLINEログインしたことないユーザーもいるが、
        // 注意書きとして既存アカウントとのLINE連携は設定画面からするようお願いする
        if (is_null($line_user_relation)) { // 新規登録
            $user = new UserModel(
                0,
                $line_user_profile->user_name,
                '', // メールアドレスは不要
                '' // パスワードはLINEログインなので不要
            );
            $user_id = $user_repository->createAndReturnId($user);
            // 作成したユーザーとLineユーザーを連携させる
            $line_user_relation_repository->upsert($user_id, $line_user_profile->line_user_id, $now);

            Auth::loginUsingId($user_id);
            return redirect()->away(config('app.frontend_home_page'));
        } else { // 新規登録済み、連携済みのログイン
            Auth::loginUsingId($line_user_relation->user_id);
            return redirect()->away(config('app.frontend_home_page'));
        }
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
