<?php

namespace App\Http\Controllers;

use App\Services\LineBotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class HandleLineWebhookController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
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
            Log::warning('LINE Webhook: invalid signature');
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

            if (!Str::startsWith($user_id, 'U')) {
                continue;  // 現在（2025/05/18時点）はグループ・ルームは対象外
            }
            
            $this->routeWebhookEvent($type, $user_id);
        }

    }

    private function routeWebhookEvent(string $type, string $line_user_id)
    {
        $line_bot_service = new LineBotService();
        if ($type === 'follow' || $type === 'follow') {
            $line_bot_service->updateFollowStatus($line_user_id);
        }
    }
}
