<?php

namespace Tests\Unit;

use App\Helpers\DateFormatter;
use PHPUnit\Framework\TestCase;

class DateFormatterTest extends TestCase
{
    public function test_id_returns_null_when_date_is_null(): void
    {
        $this->assertNull(DateFormatter::id(null));
    }

    public function test_id_formats_date_to_indonesian(): void
    {
        $date = '2026-07-05';
        $formatted = DateFormatter::id($date);

        $this->assertEquals('Minggu, 05 Juli 2026', $formatted);
    }

    public function test_id_with_time_returns_null_when_date_is_null(): void
    {
        $this->assertNull(DateFormatter::idWithTime(null));
    }

    public function test_id_with_time_formats_datetime_to_indonesian_with_timezone(): void
    {
        $datetime = '2026-07-05 13:00:00.123456';
        $formatted = DateFormatter::idWithTime($datetime);

        $this->assertStringContainsString('Minggu, 05 Juli 2026 13:00:00.123456', $formatted);
    }
}
