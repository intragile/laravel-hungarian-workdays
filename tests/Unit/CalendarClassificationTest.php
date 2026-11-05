<?php

declare(strict_types=1);

namespace IntrAgile\HungarianWorkdays\Tests\Unit;

use DateTimeImmutable;
use DateTimeZone;
use IntrAgile\HungarianWorkdays\Calendar;
use IntrAgile\HungarianWorkdays\DayType;
use IntrAgile\HungarianWorkdays\ScheduleRepository;
use IntrAgile\HungarianWorkdays\Tests\Support\BuildsFixtureCalendar;
use IntrAgile\HungarianWorkdays\Tests\TestCase;
use IntrAgile\HungarianWorkdays\UncoveredYearException;

final class CalendarClassificationTest extends TestCase
{
    use BuildsFixtureCalendar;

    public function test_day_types_follow_the_precedence_order(): void
    {
        $calendar = $this->calendar();

        $this->assertSame(DayType::PublicHoliday, $calendar->dayType(new DateTimeImmutable('2026-12-24')));
        $this->assertSame(DayType::RestDay, $calendar->dayType(new DateTimeImmutable('2026-01-02')));
        $this->assertSame(DayType::TransferredWorkday, $calendar->dayType(new DateTimeImmutable('2026-01-10')));
        $this->assertSame(DayType::Weekend, $calendar->dayType(new DateTimeImmutable('2026-01-11')));
        $this->assertSame(DayType::Workday, $calendar->dayType(new DateTimeImmutable('2026-01-12')));
    }

    public function test_is_workday_follows_day_type(): void
    {
        $calendar = $this->calendar();

        $this->assertTrue($calendar->isWorkday(new DateTimeImmutable('2026-01-10')));
        $this->assertTrue($calendar->isWorkday(new DateTimeImmutable('2026-01-12')));
        $this->assertFalse($calendar->isWorkday(new DateTimeImmutable('2026-01-02')));
        $this->assertFalse($calendar->isWorkday(new DateTimeImmutable('2026-01-11')));
        $this->assertFalse($calendar->isWorkday(new DateTimeImmutable('2026-12-24')));
    }

    public function test_public_holiday_lookup(): void
    {
        $calendar = $this->calendar();

        $this->assertTrue($calendar->isPublicHoliday(new DateTimeImmutable('2026-12-24')));
        $this->assertFalse($calendar->isPublicHoliday(new DateTimeImmutable('2026-01-02')));
        $this->assertNotNull($calendar->holiday(new DateTimeImmutable('2026-03-15')));
        $this->assertNull($calendar->holiday(new DateTimeImmutable('2026-03-16')));
    }

    public function test_uncovered_year_throws(): void
    {
        try {
            $this->calendar()->dayType(new DateTimeImmutable('2027-01-04'));
            $this->fail('Expected UncoveredYearException.');
        } catch (UncoveredYearException $e) {
            $this->assertSame(2027, $e->year);
            $this->assertStringContainsString('No Hungarian work schedule is available for 2027.', $e->getMessage());
        }
    }

    public function test_is_workday_throws_for_an_uncovered_year(): void
    {
        $this->expectException(UncoveredYearException::class);

        $this->calendar()->isWorkday(new DateTimeImmutable('2027-01-04'));
    }

    public function test_before_2013_throws_even_when_assuming_no_transfers(): void
    {
        $this->expectException(UncoveredYearException::class);

        $this->calendar()->assumingNoTransfers()->dayType(new DateTimeImmutable('2012-06-06'));
    }

    public function test_assuming_no_transfers_classifies_uncovered_year(): void
    {
        $calendar = $this->calendar();
        $relaxed = $calendar->assumingNoTransfers();

        $this->assertSame(DayType::Workday, $relaxed->dayType(new DateTimeImmutable('2027-01-04')));
        $this->assertSame(DayType::PublicHoliday, $relaxed->dayType(new DateTimeImmutable('2027-03-15')));
        $this->assertSame(DayType::Weekend, $relaxed->dayType(new DateTimeImmutable('2027-01-09')));

        $this->expectException(UncoveredYearException::class);
        $calendar->dayType(new DateTimeImmutable('2027-01-04'));
    }

    public function test_holidays_work_for_uncovered_future_year_and_before_2013(): void
    {
        $calendar = $this->calendar();

        $this->assertArrayHasKey('2030-03-15', $calendar->holidays(2030));
        $this->assertSame([], $calendar->holidays(2012));
        $this->assertFalse($calendar->isPublicHoliday(new DateTimeImmutable('2012-03-15')));
        $this->assertNull($calendar->holiday(new DateTimeImmutable('2012-03-15')));
    }

    public function test_time_and_timezone_are_ignored(): void
    {
        $calendar = $this->calendar();

        $this->assertSame(DayType::PublicHoliday, $calendar->dayType(new DateTimeImmutable('2026-12-24 23:30', new DateTimeZone('UTC'))));
        $this->assertSame(DayType::PublicHoliday, $calendar->dayType(new DateTimeImmutable('2026-12-24 00:10', new DateTimeZone('Europe/Budapest'))));
        $this->assertSame(DayType::Workday, $calendar->dayType(new DateTimeImmutable('2026-12-23 23:30', new DateTimeZone('UTC'))));
    }

    public function test_cross_year_transfer_pair(): void
    {
        $calendar = new Calendar(new ScheduleRepository([
            2029 => $this->schedule(2029),
            2030 => $this->schedule(2030, [], ['2029-12-29']),
        ]));

        $this->assertSame(DayType::TransferredWorkday, $calendar->dayType(new DateTimeImmutable('2029-12-29')));
    }

    public function test_coverage_accessors(): void
    {
        $calendar = $this->calendar();

        $this->assertTrue($calendar->isCovered(2026));
        $this->assertFalse($calendar->isCovered(2027));
        $this->assertSame([2026], $calendar->coveredYears());
    }

    public function test_default_calendar_uses_bundled_schedules(): void
    {
        $calendar = new Calendar;

        $this->assertSame(DayType::TransferredWorkday, $calendar->dayType(new DateTimeImmutable('2026-01-10')));
        $this->assertSame(DayType::PublicHoliday, $calendar->dayType(new DateTimeImmutable('2026-12-24')));
    }
}
