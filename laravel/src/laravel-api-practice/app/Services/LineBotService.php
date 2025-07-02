<?php

declare(strict_types=1);
namespace App\Services;

use App\Repositories\LineUserRelationRepository;
use DateTimeImmutable;

class LineBotService
{
    public function __construct(
        readonly private LineUserRelationRepository $line_user_profile_repository,
        readonly private DateTimeImmutable $now
    ) {}

    public function updateFollowStatus(string $line_user_id, bool $follow_flg): void
    {
        $this->line_user_profile_repository->updateFollowStatus($line_user_id, $follow_flg);
    }
}
