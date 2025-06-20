<?php

namespace App\Http\Controllers;

use App\Repositories\LineUserRelationRepository;
use App\Services\LineBotService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class HandleLineWebhookController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(
        Request $request,
        LineBotService $line_bot_service
    ): JsonResponse {
        $raw_body   = $request->getContent();
        /**
         * x-line-signatureは大文字小文字を区別せずに検証して欲しいとのこと。
         * @see https://developers.line.biz/ja/reference/messaging-api/#request-headers
         */
        $signature = $request->header('X-Line-Signature')
            ?? $request->header('x-line-signature')
            ?? '';  

        // Webhookの署名検証
        $channel_secret = Config::get('services.line_bot.secret', env('LINE_BOT_CHANNEL_SECRET'));
        $hash = base64_encode(hash_hmac('sha256', $raw_body, $channel_secret, true));

        if (!hash_equals($hash, $signature ?? '')) {
            Log::error('LINE Webhook: invalid signature');
            return response()->json(['message' => 'invalid signature'], 400);
        }

        $payload = json_decode($raw_body, true, 512, JSON_THROW_ON_ERROR);

        /**
         * 本当はイベントは非同期処理にして、後続のイベントが遅延しないようにするのが良い
         * @see https://developers.line.biz/ja/reference/messaging-api/#webhooks
         */
        foreach ($payload['events'] ?? [] as $event) {
            $type   = $event['type']    ?? null;
            $source = $event['source']  ?? [];
            $user_id = $source['userId'] ?? null;

            // グループやルームは無視
            if (($source['type'] ?? null) !== 'user') {
                continue;
            }
            if ($user_id === null) {
                continue;   // 念のため null ガード
            }
            $this->routeWebhookEvent($line_bot_service, $type, $user_id);
        }

        return response()->json(['message' => 'ok'], 200);
    }

    private function routeWebhookEvent(
        LineBotService $line_bot_service,
        string $type,
        string $line_user_id
    ): void {
        if ($type === 'follow' || $type === 'unfollow') {
            try {
                $line_bot_service->updateFollowStatus($line_user_id, $type === 'follow');
            } catch (Exception $e) {
                Log::error('LINE Webhook: ' . $e->getMessage(), [
                    'line_user_id' => $line_user_id,
                    'type'         => $type,
                ]);
            }
        }
    }
}
