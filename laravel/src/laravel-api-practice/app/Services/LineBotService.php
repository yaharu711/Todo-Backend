<?php

declare(strict_types=1);
namespace App\Services;

class LineBotService
{
    public function updateFollowStatus(string $line_user_id): void
    {
        dd('処理OK', $line_user_id);
        
        // TODO: フォロー状態を更新する処理を実装する
        // 本番環境にLINE_BOT_CHANNEL_SECRETという環境変数を設定する
        // LINE連携の状態を取得するAPIを実装する。
        // フロントエンドで表示を切り替える
            // 連携できていない時は通知のトグルを無効にする処理も
        // 通知処理に実際にLINE通知を実装する
    }
}
