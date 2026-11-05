<?php

declare(strict_types=1);

namespace IntrAgile\HungarianWorkdays\Laravel\Facades;

use DateTimeImmutable;
use DateTimeInterface;
use Illuminate\Support\Facades\Facade;
use IntrAgile\HungarianWorkdays\Calendar;
use IntrAgile\HungarianWorkdays\DayType;
use IntrAgile\HungarianWorkdays\Holiday;

/**
 * @method static Calendar assumingNoTransfers()
 * @method static DayType dayType(DateTimeInterface $date)
 * @method static bool isWorkday(DateTimeInterface $date)
 * @method static bool isPublicHoliday(DateTimeInterface $date)
 * @method static Holiday|null holiday(DateTimeInterface $date)
 * @method static array<string, Holiday> holidays(int $year)
 * @method static bool isCovered(int $year)
 * @method static list<int> coveredYears()
 * @method static DateTimeImmutable nextWorkday(DateTimeInterface $date, bool $inclusive = false)
 * @method static DateTimeImmutable previousWorkday(DateTimeInterface $date, bool $inclusive = false)
 * @method static DateTimeImmutable addWorkdays(DateTimeInterface $date, int $days)
 * @method static DateTimeImmutable subWorkdays(DateTimeInterface $date, int $days)
 * @method static int workdaysBetween(DateTimeInterface $from, DateTimeInterface $to)
 * @method static int workdaysInMonth(int $year, int $month)
 * @method static int workingHoursInMonth(int $year, int $month, int $dailyHours = 8)
 *
 * @see Calendar
 */
final class Workdays extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return Calendar::class;
    }
}
