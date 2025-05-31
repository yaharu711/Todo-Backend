<?php

declare(strict_types=1);
namespace App\Repositories;

use App\Model\LineUserRelationModel;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;

class LineUserRelationRepository
{
    public function __construct(readonly private DateTimeImmutable $now) {}

    public function getLineUserRelation(int $user_id): LineUserRelationModel|null
    {
        $result = DB::select('
            SELECT 
                user_id,
                line_user_id,
                friend_flag,
                created_at,
                updated_at,
                is_notification
            FROM line_user_relation 
            WHERE user_id = ?',
            [$user_id]
        );

        if (empty($result)) {
            return null;
        }
        return new LineUserRelationModel(
            user_id: $result[0]->user_id,
            line_user_id: $result[0]->line_user_id,
            friend_flag: $result[0]->friend_flag,
            created_at: new DateTimeImmutable($result[0]->created_at),
            updated_at: new DateTimeImmutable($result[0]->updated_at),
            is_notification: $result[0]->is_notification
        );
    }

    public function updateFollowStatus(string $line_user_id, bool $follow_flg): void
    {
        DB::statement('
            UPDATE line_user_relation 
            SET friend_flag = ?, updated_at = ? 
            WHERE line_user_id = ?',
            [
                $follow_flg, 
                $this->now,
                $line_user_id,
            ]
        );
    }

    public function updateNotificationStatus(int $user_id, bool $is_notification): void
    {
        DB::statement('
            UPDATE line_user_relation 
            SET is_notification = ?, updated_at = ? 
            WHERE user_id = ?',
            [
                $is_notification, 
                $this->now,
                $user_id,
            ]
        );
    }
}
