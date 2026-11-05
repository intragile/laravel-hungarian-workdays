<?php

declare(strict_types=1);

namespace IntrAgile\HungarianWorkdays;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use InvalidArgumentException;

/**
 * Classifies days under the Hungarian general work schedule.
 *
 * A day is judged by its own calendar date (Y-m-d as given); time and timezone play no part.
 */
final class Calendar
{
    /** First year covered by the statutory holiday rules and the schedule data. */
    private const FIRST_YEAR = 2013;

    private readonly ScheduleRepository $schedules;

    /**
     * Passing $assumeNoTransfers = true is equivalent to calling assumingNoTransfers(); the
     * default is strict and throws for years without a schedule.
     */
    public function __construct(?ScheduleRepository $schedules = null, private readonly bool $assumeNoTransfers = false)
    {
        $this->schedules = $schedules ?? ScheduleRepository::bundled();
    }

    /**
     * Returns a calendar that treats years without a schedule (2013 onwards) as having no
     * moved rest days or worked Saturdays. Dates listed in loaded schedules still apply.
     */
    public function assumingNoTransfers(): self
    {
        return new self($this->schedules, true);
    }

    /**
     * @throws UncoveredYearException
     */
    public function dayType(DateTimeInterface $date): DayType
    {
        $year = (int) $date->format('Y');
        $ymd = $date->format('Y-m-d');

        if ($year < self::FIRST_YEAR || (! $this->assumeNoTransfers && ! $this->schedules->has($year))) {
            throw new UncoveredYearException($year);
        }

        if (isset(Holiday::forYear($year)[$ymd])) {
            return DayType::PublicHoliday;
        }

        if (isset($this->schedules->restDays()[$ymd])) {
            return DayType::RestDay;
        }

        if (isset($this->schedules->workedSaturdays()[$ymd])) {
            return DayType::TransferredWorkday;
        }

        return (int) $date->format('N') >= 6 ? DayType::Weekend : DayType::Workday;
    }

    /**
     * @throws UncoveredYearException
     */
    public function isWorkday(DateTimeInterface $date): bool
    {
        return $this->dayType($date)->isWorkday();
    }

    public function isPublicHoliday(DateTimeInterface $date): bool
    {
        return $this->holiday($date) !== null;
    }

    public function holiday(DateTimeInterface $date): ?Holiday
    {
        return Holiday::forYear((int) $date->format('Y'))[$date->format('Y-m-d')] ?? null;
    }

    /**
     * @return array<string, Holiday>
     */
    public function holidays(int $year): array
    {
        return Holiday::forYear($year);
    }

    public function isCovered(int $year): bool
    {
        return $this->schedules->has($year);
    }

    /**
     * @return list<int>
     */
    public function coveredYears(): array
    {
        return $this->schedules->years();
    }

    /**
     * Returns the first workday after the given day, or the day itself when $inclusive is set
     * and it is a workday.
     *
     * @throws UncoveredYearException
     */
    public function nextWorkday(DateTimeInterface $date, bool $inclusive = false): DateTimeImmutable
    {
        return $this->seek($date, 1, $inclusive);
    }

    /**
     * Returns the last workday before the given day, or the day itself when $inclusive is set
     * and it is a workday.
     *
     * @throws UncoveredYearException
     */
    public function previousWorkday(DateTimeInterface $date, bool $inclusive = false): DateTimeImmutable
    {
        return $this->seek($date, -1, $inclusive);
    }

    /**
     * Moves by the given number of workdays (negative goes backwards). Zero returns the day
     * itself at midnight, whether or not it is a workday.
     *
     * @throws UncoveredYearException
     */
    public function addWorkdays(DateTimeInterface $date, int $days): DateTimeImmutable
    {
        $current = $this->midnight($date);
        $step = $days < 0 ? -1 : 1;

        for ($remaining = abs($days); $remaining > 0; $remaining--) {
            $current = $this->seek($current, $step, false);
        }

        return $current;
    }

    /**
     * @throws UncoveredYearException
     */
    public function subWorkdays(DateTimeInterface $date, int $days): DateTimeImmutable
    {
        return $this->addWorkdays($date, -$days);
    }

    /**
     * Counts the workdays from $from to $to, both ends included.
     *
     * @throws InvalidArgumentException when $from is after $to
     * @throws UncoveredYearException
     */
    public function workdaysBetween(DateTimeInterface $from, DateTimeInterface $to): int
    {
        // Both ends are reduced to their own Y-m-d in one fixed timezone, so offsets cannot matter.
        $utc = new DateTimeZone('UTC');
        $current = new DateTimeImmutable($from->format('Y-m-d'), $utc);
        $end = new DateTimeImmutable($to->format('Y-m-d'), $utc);

        if ($current > $end) {
            throw new InvalidArgumentException('The start date must not be after the end date.');
        }

        $count = 0;
        while ($current <= $end) {
            $count += $this->isWorkday($current) ? 1 : 0;
            $current = $current->modify('+1 day');
        }

        return $count;
    }

    /**
     * @throws InvalidArgumentException when the month is outside 1..12
     * @throws UncoveredYearException
     */
    public function workdaysInMonth(int $year, int $month): int
    {
        if ($month < 1 || $month > 12) {
            throw new InvalidArgumentException("Month must be between 1 and 12, {$month} given.");
        }

        $first = new DateTimeImmutable(sprintf('%04d-%02d-01', $year, $month), new DateTimeZone('UTC'));

        return $this->workdaysBetween($first, $first->modify('last day of this month'));
    }

    /**
     * @throws InvalidArgumentException when the month is outside 1..12 or $dailyHours is below 1
     * @throws UncoveredYearException
     */
    public function workingHoursInMonth(int $year, int $month, int $dailyHours = 8): int
    {
        if ($dailyHours < 1) {
            throw new InvalidArgumentException("Daily hours must be at least 1, {$dailyHours} given.");
        }

        return $this->workdaysInMonth($year, $month) * $dailyHours;
    }

    /**
     * The day's own Y-m-d at 00:00 in the input's timezone.
     */
    private function midnight(DateTimeInterface $date): DateTimeImmutable
    {
        return new DateTimeImmutable($date->format('Y-m-d'), $date->getTimezone());
    }

    /**
     * Steps day by day in one direction until a workday is found. Every step is classified, so
     * running out of coverage (or before 2013) raises UncoveredYearException instead of looping.
     */
    private function seek(DateTimeInterface $date, int $direction, bool $inclusive): DateTimeImmutable
    {
        $current = $this->midnight($date);

        if (! $inclusive) {
            $current = $current->modify($direction > 0 ? '+1 day' : '-1 day');
        }

        while (! $this->isWorkday($current)) {
            $current = $current->modify($direction > 0 ? '+1 day' : '-1 day');
        }

        return $current;
    }
}
