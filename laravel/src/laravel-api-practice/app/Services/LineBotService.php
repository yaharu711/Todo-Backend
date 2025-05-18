<?php

declare(strict_types=1);
namespace App\Services;

use App\Repositories\LineUserProfileRepository;

class LineBotService
{
    public function updateFollowStatus(string $line_user_id, bool $follow_flg): void
    {
        
        $line_user_profile_repository = new LineUserProfileRepository();
        $line_user_profile_repository->updateFollowStatus($line_user_id, $follow_flg);
        // TODO: フォロー状態を更新する処理を実装する
        // 本番環境にLINE_BOT_CHANNEL_SECRETという環境変数を設定する
        // どうしても責務を分けたいと思ってしまうので、line_user_relationとは別のテーブルで、LINEアカウントと友達かの状態を管理しようかな
            // line_user_idとcreated_atだけで良いかな、テーブル名をline_user_bot_follow_listとする
            // line_user_idはline_user_relationのline_user_idを参照して、cascadeにする
            // 連携を外した場合に、line_user_bot_follow_listから削除したいため
            // とか考えていくと、なんか余計に管理するものが増えているし、LINE連携に関する関心をline_user_relationテーブルに押さえ込むことのほうが良い気がしてきて、
            // ステータスなだけだからこれ以上カラムも増えなさそうだし、line_user_relationテーブルにis_friendを追加しようかな、、
        // LINE連携の状態を取得するAPIを実装する。
            // どうやら、ブロックされた時にunfollowイベントが来るため、UI的な表示は、友達はブロックされています。ブロックを解除すると通知を受け取れます。と表示した方が良さそう
        // フロントエンドで表示を切り替える
            // 連携できていない時は通知のトグルを無効にする処理も
        // 通知処理に実際にLINE通知を実装する
    }
}
