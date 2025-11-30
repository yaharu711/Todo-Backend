<?php
namespace App\Http\Response;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;

class ResponseHelper
{
    /**
     * 日付／日時をアプリのタイムゾーンでISO8601文字列に整形する
     *
     * @param DateTimeImmutable $value
     */
    public static function formatDateTime(DateTimeImmutable $value): string
    {
        $tz = new DateTimeZone(config('app.timezone'));

        return $value
            ->setTimezone($tz)
            ->format(DateTimeInterface::ATOM);
    }
}
