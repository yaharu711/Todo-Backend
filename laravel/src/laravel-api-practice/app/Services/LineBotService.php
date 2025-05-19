<?php

declare(strict_types=1);
namespace App\Services;

use App\Repositories\LineUserProfileRepository;
use DateTimeImmutable;

class LineBotService
{
    public function __construct(private readonly DateTimeImmutable $now) {}

    public function updateFollowStatus(string $line_user_id, bool $follow_flg): void
    {
        
        $line_user_profile_repository = new LineUserProfileRepository($this->now);
        $line_user_profile_repository->updateFollowStatus($line_user_id, $follow_flg);
        // LINE連携の状態を取得するAPIを実装する。
            // どうやら、ブロックされた時にunfollowイベントが来るため、UI的な表示は、友達はブロックされています。ブロックを解除すると通知を受け取れます。と表示した方が良さそう
        // フロントエンドで表示を切り替える
            // 連携できていない時は通知のトグルを無効にする処理も
        // 通知処理に実際にLINE通知を実装する
    }
}
