<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CallbackLineAuthController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        dd('ok');
        // クエリパラメータにあるstateをセッションに保存されているstateと比較する
            // stateが一致しない場合は、403エラーを返す
        // アクセストークンを取得する
            //https://developers.line.biz/ja/reference/line-login/#issue-access-token
        // アクセストークンを使用して、ユーザーのプロフィール情報を取得する（profileのscopeがついているから大丈夫）
            // https://developers.line.biz/ja/reference/line-login/#get-user-profile
        // Auth::id()がnullの場合はusersテーブルに新規レコードを作成する
            // すでにレコードが存在する場合は、何もしない
            // これは設定画面からLINEログインを行うユーザもいるため（既存ユーザー用）
        // 取得したユーザーのプロフィール情報を元に、user_line_relationテーブルにレコードを作成する
        // Auth::id()がnullの場合、Auth::login()を使用して、ユーザーをログインさせる
    }
}
