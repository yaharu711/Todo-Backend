<?php
namespace App\Models;

use DateTimeImmutable;

class TodoModel
{
    public function __construct(
        readonly public int $id,
        readonly public string $name,
        readonly public string $memo,
        readonly public DateTimeImmutable $notificate_at,
        readonly public DateTimeImmutable $created_at,
        readonly public DateTimeImmutable $imcompleted_at,
        readonly public bool $is_completed,
        readonly public DateTimeImmutable|null $completed_at
    ) {}
}
