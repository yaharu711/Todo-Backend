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
        $values = [];
        $params = [];
        
        foreach ($failed_notification_dto_list as $dto) {
            $values[] = '(?, ?, ?)';
            array_push($params, $dto->user_id, $dto->token, $this->now);
        }
        
        if (!empty($values)) {
            $sql = "
                INSERT INTO invalidated_fcm (user_id, token, created_at)
                VALUES " . implode(', ', $values) . "
                ON CONFLICT (user_id, token) DO NOTHING
            ";
        
            DB::statement($sql, $params);
        }
    }
}
