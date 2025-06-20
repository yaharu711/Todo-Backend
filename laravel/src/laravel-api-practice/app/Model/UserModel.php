<?php
declare(strict_types=1);
namespace App\Model;

class UserModel
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $email,
        public readonly string $password, // パスワードはハッシュ化して保存することを想定
    ) {}
}
