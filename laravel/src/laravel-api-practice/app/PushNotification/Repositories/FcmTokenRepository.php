<?php 

namespace App\PushNotification\Repositories;

use DateTimeImmutable;
use Illuminate\Support\Facades\DB;

class FcmTokenRepository
{
    public function __construct(readonly private DateTimeImmutable $now){}

    /**
     * @param FcmPushNotificationErrorDto[] $failed_notification_dto_list
     */
    public function deleteFcmToken(array $failed_notification_dto_list): void
    {
        $values = [];
        $params = [];

        foreach ($failed_notification_dto_list as $failed_notification_dto) {
            $values[] = '(?, ?)';
            $params[] = $failed_notification_dto->user_id;
            $params[] = $failed_notification_dto->token;
        }

        // Postgresでは、複数ペアの削除は VALUES 構文を使用してIN句で実現できる
        $sql = "DELETE FROM fcm WHERE (user_id, token) IN (" . implode(', ', $values) . ")";
        DB::statement($sql, $params);
    }
    
    /**
     * @param FcmPushNotificationErrorDto[] $failed_notification_dto_list
     */
    public function insertInvalidatedFcmToken(array $failed_notification_dto_list): void
    {
        $upsertData = array_map(
            fn($dto) => [
                'user_id'    => $dto->user_id,
                'token'      => $dto->token,
                'created_at' => $this->now,
            ],
            $failed_notification_dto_list
        );
        DB::table('invalidated_fcm')
            ->upsert($upsertData, ['user_id', 'token'], []);
    }
}
