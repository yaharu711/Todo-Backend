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
        $text = "「{$todo_name}」\nのリマインドです！$";
        $emoji_index = mb_strlen($text) - 1; // 絵文字のインデックスは先頭が0なので、文字列の長さから1を引く
        $notificated_todo_url = PushNotificationContent::getLink($todo_id);

        return new self([
            [
                'type' => 'text',
                'text' => "{$text}\n{$notificated_todo_url}",
                'emojis' => [
                    [
                        'index' => $emoji_index,
                        /**
                         * 通知マークの絵文字
                         * @see https://developers.line.biz/ja/docs/messaging-api/emoji-list/#line-emoji-definitions
                         */
                        'productId' => '5ac21a18040ab15980c9b43e',
                        'emojiId' => '009',
                    ],
                ],
            ],
        ]);
    }
}
