<?php

namespace App\Helpers;

use Carbon\Carbon;

class DateFormatter
{
    public static function id($date): ?string
    {
        return $date ? Carbon::parse($date)->locale('id')->translatedFormat('l, d F Y') : null;
    }
}
