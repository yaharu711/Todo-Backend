<?php
namespace App\PushNotification;

/**
 * プッシュ通知のコンテンツを提供する。全ての通知で共通のコンテンツを提供する。
 */
class PushNotificationContent
{
    const BODY = 'リマインドです！';
    const ICON = 'https://todo-laravel-react.s3.ap-northeast-1.amazonaws.com/icon-512x512.png';
    const URGENCY = 'high';

    public static function getTitle(string $todo_name): string
    {
        return $todo_name;
    }

    public static function getLink(int $todo_id): string
    {
        return env('FRONTEND_APP_URL') . '/todos#' . $todo_id;
    }
}