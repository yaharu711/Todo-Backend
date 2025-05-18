<?php
declare(strict_types=1);
namespace App\Repositories;

use DateTimeImmutable;
use Illuminate\Support\Facades\DB;

class UserLineRelationRepository
{
    public function upsert(int $user_id, string $line_user_id, DateTimeImmutable $now): void
    {
        DB::statement('INSERT INTO line_user_relation (user_id, line_user_id, created_at)
            VALUES (?, ?, ?)
            ON CONFLICT (user_id) DO NOTHING', [$user_id, $line_user_id, $now]);
    }
}
