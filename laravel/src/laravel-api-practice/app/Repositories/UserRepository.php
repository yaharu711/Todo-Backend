<?php
declare(strict_types=1);
namespace App\Repositories;

use App\Model\UserModel;
use App\Models\User;

class UserRepository
{
    public function createAndReturnId(UserModel $user): int 
    {
        $user = User::create([
            'name' =>  $user->name,
            'email' => $user->email, 
            'password' => $user->password,
        ]);

        return $user->id;
    }
}
