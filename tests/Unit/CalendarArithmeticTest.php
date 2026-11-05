<?php

declare(strict_types=1);

namespace IntrAgile\HungarianWorkdays\Tests\Unit;

use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use IntrAgile\HungarianWorkdays\Tests\Support\BuildsFixtureCalendar;
use IntrAgile\HungarianWorkdays\Tests\TestCase;
use IntrAgile\HungarianWorkdays\UncoveredYearException;
use InvalidArgumentException;

final class CalendarArithmeticTest extends TestCase
{
    use BuildsFixtureCalendar;

    private function ymd(DateTimeImmutable $date): string
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function test_next_workday_skips_holidays_and_weekend(): void
    {
        $this->assertSame('2026-12-28 00:00:00', $this->ymd($this->calendar()->nextWorkday(new DateTimeImmutable('2026-12-23'))));
    }

    public function test_next_workday_lands_on_a_worked_saturday(): void
    {
        $this->assertSame('2026-01-10 00:00:00', $this->ymd($this->calendar()->nextWorkday(new DateTimeImmutable('2026-01-09'))));
    }

    public function test_next_workday_skips_a_rest_day(): void
    {
        $this->assertSame('2026-01-05 00:00:00', $this->ymd($this->calendar()->nextWorkday(new DateTimeImmutable('2026-01-01'))));
    }

    public function test_next_workday_inclusive(): void
    {
        $calendar = $this->calendar();

        $this->assertSame('2026-12-23 00:00:00', $this->ymd($calendar->nextWorkday(new DateTimeImmutable('2026-12-23 15:45'), inclusive: true)));
        $this->assertSame('2026-12-28 00:00:00', $this->ymd($calendar->nextWorkday(new DateTimeImmutable('2026-12-24'), inclusive: true)));
    }

    public function test_previous_workday(): void
    {
        $calendar = $this->calendar();

        $this->assertSame('2026-12-23 00:00:00', $this->ymd($calendar->previousWorkday(new DateTimeImmutable('2026-12-28'))));
        $this->assertSame('2026-12-28 00:00:00', $this->ymd($calendar->previousWorkday(new DateTimeImmutable('2026-12-28'), inclusive: true)));
        $this->assertSame('2026-12-23 00:00:00', $this->ymd($calendar->previousWorkday(new DateTimeImmutable('2026-12-24'), inclusive: true)));
        $this->assertSame('2026-01-10 00:00:00', $this->ymd($calendar->previousWorkday(new DateTimeImmutable('2026-01-12'))));
    }

    public function test_add_workdays(): void
    {
        $calendar = $this->calendar();

        $this->assertSame('2026-12-24 00:00:00', $this->ymd($calendar->addWorkdays(new DateTimeImmutable('2026-12-24 13:00'), 0)));
        $this->assertSame('2026-12-28 00:00:00', $this->ymd($calendar->addWorkdays(new DateTimeImmutable('2026-12-24'), 1)));
        $this->assertSame('2026-12-23 00:00:00', $this->ymd($calendar->addWorkdays(new DateTimeImmutable('2026-12-28'), -1)));
        $this->assertSame('2026-12-31 00:00:00', $this->ymd($calendar->addWorkdays(new DateTimeImmutable('2026-12-23'), 4)));
    }

    public function test_sub_workdays_mirrors_add_workdays(): void
    {
        $calendar = $this->calendar();
        $date = new DateTimeImmutable('2026-12-28');

        foreach ([-3, 0, 1, 5] as $n) {
            $this->assertEquals($calendar->addWorkdays($date, -$n), $calendar->subWorkdays($date, $n));
        }
        $this->assertSame('2026-12-22 00:00:00', $this->ymd($calendar->subWorkdays($date, 2)));
    }

    public function test_add_workdays_into_uncovered_year_throws(): void
    {
        try {
            $this->calendar()->addWorkdays(new DateTimeImmutable('2026-12-30'), 5);
            $this->fail('Expected UncoveredYearException.');
        } catch (UncoveredYearException $e) {
            $this->assertSame(2027, $e->year);
        }
    }

    public function test_add_workdays_into_uncovered_year_with_assumed_no_transfers(): void
    {
        $result = $this->calendar()->assumingNoTransfers()->addWorkdays(new DateTimeImmutable('2026-12-30'), 5);

        $this->assertSame('2027-01-07 00:00:00', $this->ymd($result));
    }

    public function test_stepping_before_2013_throws(): void
    {
        $this->expectException(UncoveredYearException::class);

        $this->calendar()->assumingNoTransfers()->previousWorkday(new DateTimeImmutable('2013-01-01'));
    }

    public function test_mutable_input_is_not_modified(): void
    {
        $calendar = $this->calendar();
        $input = new DateTime('2026-12-23 15:45:10', new DateTimeZone('Europe/Budapest'));
        $before = $input->format(DATE_ATOM);

        $calendar->nextWorkday($input);
        $calendar->previousWorkday($input);
        $calendar->addWorkdays($input, 2);
        $calendar->subWorkdays($input, 2);
        $calendar->workdaysBetween($input, new DateTime('2026-12-31'));
        $calendar->workdaysBetween(new DateTime('2026-12-01'), $input);
        $calendar->nextWorkday($input, inclusive: true);

        $this->assertSame($before, $input->format(DATE_ATOM));
    }

    public function test_output_keeps_the_input_timezone(): void
    {
        $zone = new DateTimeZone('Pacific/Auckland');
        $result = $this->calendar()->nextWorkday(new DateTimeImmutable('2026-12-23 22:00', $zone));

        $this->assertSame('Pacific/Auckland', $result->getTimezone()->getName());
        $this->assertSame('2026-12-28 00:00:00', $this->ymd($result));
    }

    public function test_workdays_between_is_inclusive(): void
    {
        $this->assertSame(7, $this->calendar()->workdaysBetween(new DateTimeImmutable('2026-12-21'), new DateTimeImmutable('2026-12-31')));
    }

    public function test_workdays_between_single_day(): void
    {
        $calendar = $this->calendar();

        $this->assertSame(1, $calendar->workdaysBetween(new DateTimeImmutable('2026-12-23'), new DateTimeImmutable('2026-12-23 23:59')));
        $this->assertSame(0, $calendar->workdaysBetween(new DateTimeImmutable('2026-12-24'), new DateTimeImmutable('2026-12-24')));
    }

    public function test_workdays_between_rejects_reversed_range(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->calendar()->workdaysBetween(new DateTimeImmutable('2026-12-31'), new DateTimeImmutable('2026-12-21'));
    }

    public function test_workdays_in_month_counts_transfers(): void
    {
        $calendar = $this->calendar();

        // January 2026: 22 weekdays, minus Jan 1 (holiday) and Jan 2 (rest day), plus Saturday Jan 10.
        $this->assertSame(21, $calendar->workdaysInMonth(2026, 1));
    }

    public function test_workdays_in_month_rejects_invalid_month(): void
    {
        $calendar = $this->calendar();

        foreach ([0, 13, -1] as $month) {
            try {
                $calendar->workdaysInMonth(2026, $month);
                $this->fail('Expected InvalidArgumentException.');
            } catch (InvalidArgumentException) {
                $this->addToAssertionCount(1);
            }
        }
    }

    public function test_workdays_in_uncovered_month_throws(): void
    {
        $this->expectException(UncoveredYearException::class);

        $this->calendar()->workdaysInMonth(2027, 1);
    }

    public function test_working_hours_in_month(): void
    {
        $calendar = $this->calendar();

        $this->assertSame($calendar->workdaysInMonth(2026, 1) * 8, $calendar->workingHoursInMonth(2026, 1));
        $this->assertSame($calendar->workdaysInMonth(2026, 1) * 6, $calendar->workingHoursInMonth(2026, 1, 6));
    }

    public function test_working_hours_rejects_non_positive_daily_hours(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->calendar()->workingHoursInMonth(2026, 1, 0);
    }
}
