<?php
namespace App\Models;

use DateTimeImmutable;

class TodoModel
{
    public function __construct(
        readonly public int $id,
        readonly public int $user_id,
        public string $name,
        public string $memo,
        public DateTimeImmutable|null $notificate_at,
        readonly public DateTimeImmutable $created_at,
        public DateTimeImmutable $imcompleted_at,
        public bool $is_completed,
        public DateTimeImmutable|null $completed_at
    ) {}
}
