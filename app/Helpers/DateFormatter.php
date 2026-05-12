<?php

namespace App\Helpers;

use Carbon\Carbon;

class DateFormatter
{
    public static function id($date): ?string
    {
        return $date ? Carbon::parse($date)->locale('id')->translatedFormat('l, d F Y') : null;
    }

    public static function idWithTime($date): ?string
    {
        return $date
            ? Carbon::parse($date)
                ->setTimezone('Asia/Jakarta')
                ->locale('id')
                ->translatedFormat('l, d F Y H:i:s.u')
            : null;
    }
}
