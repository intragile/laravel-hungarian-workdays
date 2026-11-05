<?php

declare(strict_types=1);

namespace IntrAgile\HungarianWorkdays;

/**
 * An immutable set of work schedules, indexed by year and by the actual dates they list.
 */
final class ScheduleRepository
{
    private static ?self $bundled = null;

    /** @var array<int, Schedule> */
    private readonly array $schedules;

    /** @var array<string, true> */
    private readonly array $restDays;

    /** @var array<string, true> */
    private readonly array $workedSaturdays;

    /**
     * @param  array<int, Schedule>  $schedules
     */
    public function __construct(array $schedules)
    {
        $byYear = [];

        foreach ($schedules as $schedule) {
            $byYear[$schedule->year] = $schedule;
        }

        ksort($byYear);

        $restDays = [];
        $workedSaturdays = [];

        // Indexed by the date itself: a decree may list days outside its own year.
        foreach ($byYear as $schedule) {
            foreach ($schedule->restDays as $date) {
                $restDays[$date] = true;
            }

            foreach ($schedule->workedSaturdays as $date) {
                $workedSaturdays[$date] = true;
            }
        }

        $this->schedules = $byYear;
        $this->restDays = $restDays;
        $this->workedSaturdays = $workedSaturdays;
    }

    /**
     * The schedules shipped with the package.
     *
     * @throws InvalidScheduleException
     */
    public static function bundled(): self
    {
        if (self::$bundled === null) {
            /** @var array<int, array<mixed>> $data */
            $data = require __DIR__.'/../resources/schedules.php';

            $schedules = [];

            foreach ($data as $year => $entry) {
                $schedules[$year] = Schedule::fromArray($year, $entry);
            }

            self::$bundled = new self($schedules);
        }

        return self::$bundled;
    }

    /**
     * Returns a new repository with the given schedules added; a schedule replaces any
     * existing one for the same year.
     */
    public function with(Schedule ...$schedules): self
    {
        $merged = $this->schedules;

        foreach ($schedules as $schedule) {
            $merged[$schedule->year] = $schedule;
        }

        return new self($merged);
    }

    public function has(int $year): bool
    {
        return isset($this->schedules[$year]);
    }

    /**
     * @return list<int>
     */
    public function years(): array
    {
        return array_keys($this->schedules);
    }

    /**
     * Index of moved rest days by date.
     *
     * @internal
     *
     * @return array<string, true>
     */
    public function restDays(): array
    {
        return $this->restDays;
    }

    /**
     * Index of worked Saturdays by date.
     *
     * @internal
     *
     * @return array<string, true>
     */
    public function workedSaturdays(): array
    {
        return $this->workedSaturdays;
    }
}
