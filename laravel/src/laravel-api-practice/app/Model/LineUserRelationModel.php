<?php

declare(strict_types=1);
namespace App\Model;

use DateTimeImmutable;

class LineUserRelationModel
{
    public function __construct(
        readonly public int $user_id,
        readonly public string $line_user_id,
        readonly public bool $friend_flag,
        readonly public DateTimeImmutable $created_at,
        readonly public DateTimeImmutable $updated_at,
    ) {
    }
}
