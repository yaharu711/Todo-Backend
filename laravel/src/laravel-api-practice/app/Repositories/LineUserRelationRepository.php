<?php

declare(strict_types=1);
namespace App\Repositories;

class LineUserRelationRepository
{
    public function getLineUserRelation(int $user_id): LineUserRelationModel|null
    {
        $result = DB::select('
            SELECT 
                user_id,
                line_user_id,
                friend_flag,
                created_at,
                updated_at
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
        );
    }
}
