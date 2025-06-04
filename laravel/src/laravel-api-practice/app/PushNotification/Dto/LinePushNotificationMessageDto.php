<?php
declare(strict_types=1);

namespace App\PushNotification\Dto;

use App\PushNotification\PushNotificationContent;

class LinePushNotificationMessageDto
{
    private function __construct(
        public readonly array $messages,
    ) {}

    public static function createMessage(
        int $todo_id,
        string $todo_name,
    ): self {
        $text = "\${$todo_name}$\nのリマインドです$";
        $first_emoji_index = 0; // 絵文字のインデックスは先頭が0
        $second_emoji_index = mb_strlen($todo_name) + 1;
        $last_emoji_index = mb_strlen($text) - 1;

        $notificated_todo_url = PushNotificationContent::getLink($todo_id);

        return new self([
            [
                'type' => 'text',
                'text' => "{$text}\n{$notificated_todo_url}",
                /**
                 * 絵文字について
                 * @see https://developers.line.biz/ja/docs/messaging-api/emoji-list/#line-emoji-definitions
                 */
                'emojis' => [
                    [
                        // 「 の絵文字
                        'index' => $first_emoji_index,
                        'productId' => '5ac21b4f031a6752fb806d59',
                        'emojiId' => '114',
                    ],
                    [
                        // 」の絵文字
                        'index' => $second_emoji_index,
                        'productId' => '5ac21b4f031a6752fb806d59',
                        'emojiId' => '115',
                    ],
                    [
                        // !の絵文字
                        'index' => $last_emoji_index,
                        'productId' => '5ac21b4f031a6752fb806d59',
                        'emojiId' => '067',
                    ],
                ],
            ],
        ]);
    }
}
