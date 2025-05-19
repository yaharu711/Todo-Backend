<?php
declare(strict_types=1);
namespace App\Repositories;

use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class LineUserProfileRepository
{
    public function __construct(readonly private DateTimeImmutable $now) {}

    /**
     * @see https://developers.line.biz/ja/reference/line-login/#get-user-profile
     */
    public function getLineUserIdByLoginApi(string $access_token)
    {
        $profile = Http::withToken($access_token)
        ->get('https://api.line.me/v2/profile')
        ->throw()
        ->json();

        return $profile['userId'];
    }

    public function updateFollowStatus(string $line_user_id, bool $follow_flg): void
    {
        DB::statement('
            UPDATE line_user_relation 
            SET friend_flg = ?, updated_at = ? 
            WHERE line_user_id = ?', 
            [
                $follow_flg, 
                $line_user_id,
                $this->now,
            ]
        );
    }
}
