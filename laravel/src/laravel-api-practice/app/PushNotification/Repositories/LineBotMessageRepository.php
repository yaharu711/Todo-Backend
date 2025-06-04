<?php
declare(strict_types=1);
namespace App\PushNotification\Repositories;

use App\PushNotification\Dto\LinePushNotificationMessageDto;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class LineBotMessageRepository
{
    private const API_BASE_URL = 'https://api.line.me/v2/bot/message';

    public function __construct(
        readonly private string $channel_access_token
    ) {}

    /**
     * @see https://developers.line.biz/ja/reference/messaging-api/#send-push-message
     * @throws RuntimeException
     */
    public function pushNotification(
        string $line_user_id,
        LinePushNotificationMessageDto $message_dto,
    ): void {
        $payload = [
            'to'       => $line_user_id,
            'messages' => $message_dto->messages,
        ];

        $response = Http::withHeaders([
            'Authorization'    => "Bearer {$this->channel_access_token}",
            'Content-Type'     => 'application/json',
        ])->post(self::API_BASE_URL . '/push', $payload);

        if ($response->status() !== 200) {
            throw new RuntimeException('LINE Bot API request failed: ' . $response->body() . 'status_code: ' . $response->status() . 'payload: ' . json_encode($payload, JSON_UNESCAPED_UNICODE));
        }
    }
}
