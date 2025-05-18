<?php
declare(strict_types=1);
namespace App\Repositories;

use Illuminate\Support\Facades\Http;

class LineUserProfileRepository
{
    /**
     * @see https://developers.line.biz/ja/reference/line-login/#get-user-profile
     */
    public function getLineUserIdByLoginApi(string $access_token)
    {
        $profile = Http::withToken($access_token)
        ->get('https://api.line.me/v2/profile')
        ->throw()
        ->json();

        return $profile['userId'];
    }
}
