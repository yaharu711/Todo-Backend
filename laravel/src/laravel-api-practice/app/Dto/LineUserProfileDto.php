<?php
declare(strict_types=1);
namespace App\Dto;

class LineUserProfileDto
{
    public function __construct(
        public readonly string $line_user_id,
        public readonly string $user_name,
        public readonly ?string $picture_url = null,
        public readonly ?string $status_message = null
    ) {}
}
