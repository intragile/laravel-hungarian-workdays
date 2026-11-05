<?php

declare(strict_types=1);

namespace IntrAgile\HungarianWorkdays;

use DateTimeInterface;

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

    private bool $assumeNoTransfers = false;

    public function __construct(?ScheduleRepository $schedules = null)
    {
        $this->schedules = $schedules ?? ScheduleRepository::bundled();
    }

    /**
     * Returns a calendar that treats years without a schedule (2013 onwards) as having no
     * moved rest days or worked Saturdays.
     */
    public function assumingNoTransfers(): self
    {
        $copy = new self($this->schedules);
        $copy->assumeNoTransfers = true;

        return $copy;
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

        if (isset($this->schedules->workdays()[$ymd])) {
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
}
