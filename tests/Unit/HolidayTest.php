<?php

declare(strict_types=1);

namespace IntrAgile\HungarianWorkdays\Tests\Unit;

use IntrAgile\HungarianWorkdays\Holiday;
use IntrAgile\HungarianWorkdays\Tests\TestCase;

final class HolidayTest extends TestCase
{
    public function test_good_friday_exists_only_from_its_effective_year(): void
    {
        $this->assertNull(Holiday::GoodFriday->dateIn(2016));
        $this->assertSame('2017-04-14', Holiday::GoodFriday->dateIn(2017)?->format('Y-m-d'));
    }

    public function test_christmas_eve_exists_from_2026(): void
    {
        $this->assertNull(Holiday::ChristmasEve->dateIn(2025));
        $this->assertSame('2026-12-24', Holiday::ChristmasEve->dateIn(2026)?->format('Y-m-d'));
    }

    public function test_whit_monday_is_fifty_days_after_easter(): void
    {
        $this->assertSame('2026-05-25', Holiday::WhitMonday->dateIn(2026)?->format('Y-m-d'));
    }

    public function test_dates_are_midnight_utc(): void
    {
        $date = Holiday::NewYear->dateIn(2026);

        $this->assertNotNull($date);
        $this->assertSame('UTC', $date->getTimezone()->getName());
        $this->assertSame('00:00:00', $date->format('H:i:s'));
    }

    public function test_nothing_is_a_holiday_before_2013(): void
    {
        foreach (Holiday::cases() as $holiday) {
            $this->assertNull($holiday->dateIn(2012));
        }
        $this->assertSame([], Holiday::forYear(2012));
    }

    public function test_for_year_2026_lists_twelve_sorted_holidays(): void
    {
        $this->assertSame([
            '2026-01-01', '2026-03-15', '2026-04-03', '2026-04-06', '2026-05-01', '2026-05-25',
            '2026-08-20', '2026-10-23', '2026-11-01', '2026-12-24', '2026-12-25', '2026-12-26',
        ], array_keys(Holiday::forYear(2026)));
        $this->assertSame(Holiday::GoodFriday, Holiday::forYear(2026)['2026-04-03']);
    }

    public function test_for_year_2016_has_ten_holidays(): void
    {
        $holidays = Holiday::forYear(2016);

        $this->assertCount(10, $holidays);
        $this->assertNotContains(Holiday::GoodFriday, $holidays);
        $this->assertNotContains(Holiday::ChristmasEve, $holidays);
    }

    public function test_names_are_provided_for_every_case(): void
    {
        foreach (Holiday::cases() as $holiday) {
            $this->assertNotSame('', $holiday->hungarianName());
            $this->assertNotSame('', $holiday->englishName());
        }
        $this->assertSame('Nagypéntek', Holiday::GoodFriday->hungarianName());
        $this->assertSame('Szenteste', Holiday::ChristmasEve->hungarianName());
        $this->assertSame('Nemzeti ünnep', Holiday::NationalDay1848->hungarianName());
    }
}
