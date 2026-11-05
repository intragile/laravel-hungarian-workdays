<?php

declare(strict_types=1);

namespace IntrAgile\HungarianWorkdays\Tests\Feature;

use DateTimeImmutable;
use DateTimeZone;
use IntrAgile\HungarianWorkdays\Calendar;
use IntrAgile\HungarianWorkdays\Tests\TestCase;

final class BundledCalendarTest extends TestCase
{
    public function test_monthly_counts_match_day_by_day_classification_for_every_covered_year(): void
    {
        $calendar = new Calendar;
        $utc = new DateTimeZone('UTC');

        foreach ($calendar->coveredYears() as $year) {
            $byMonth = 0;
            for ($month = 1; $month <= 12; $month++) {
                $byMonth += $calendar->workdaysInMonth($year, $month);
            }

            $byDay = 0;
            $day = new DateTimeImmutable("$year-01-01", $utc);
            while ((int) $day->format('Y') === $year) {
                $byDay += $calendar->isWorkday($day) ? 1 : 0;
                $day = $day->modify('+1 day');
            }

            $this->assertSame($byDay, $byMonth, "Year $year");
        }
    }

    public function test_december_2026_has_21_workdays(): void
    {
        // 23 weekdays (Dec 1 is a Tuesday), minus Dec 24 and Dec 25 (Dec 26 is a Saturday);
        // the 2026 schedule has no December transfers.
        $calendar = new Calendar;

        $this->assertSame(21, $calendar->workdaysInMonth(2026, 12));
        $this->assertSame(168, $calendar->workingHoursInMonth(2026, 12));
    }
}
