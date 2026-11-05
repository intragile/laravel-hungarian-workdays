<?php

declare(strict_types=1);

namespace IntrAgile\HungarianWorkdays\Laravel\Console;

use Carbon\Carbon;
use Illuminate\Console\Command;
use IntrAgile\HungarianWorkdays\Calendar;

/**
 * Reports the years the work schedule covers and fails when the current year is not covered
 * or when next year's schedule is still missing at the end of October, so a scheduler or CI
 * job can raise the alarm.
 */
final class CoverageCommand extends Command
{
    protected $signature = 'workdays:coverage';

    protected $description = 'List the years covered by the Hungarian work schedule and warn when the current or next year is missing';

    public function handle(Calendar $calendar): int
    {
        $this->line('Covered years: '.self::describe($calendar->coveredYears()));

        $now = Carbon::now();
        $failed = false;

        if (! $calendar->isCovered($now->year)) {
            $this->warn("No work schedule is available for the current year, {$now->year}; add the decree to the 'workdays.schedules' configuration or update the package.");
            $failed = true;
        }

        $nextYear = $now->year + 1;
        $dueForNextYear = $now->month > 10 || ($now->month === 10 && $now->day >= 31);

        if ($dueForNextYear && ! $calendar->isCovered($nextYear)) {
            $this->warn("No work schedule is available for {$nextYear} yet; add the decree to the 'workdays.schedules' configuration or update the package.");
            $failed = true;
        }

        if ($failed) {
            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    /**
     * Collapses consecutive years into ranges: 2013–2026, 2029.
     *
     * @param  list<int>  $years
     */
    private static function describe(array $years): string
    {
        if ($years === []) {
            return 'none';
        }

        sort($years);
        $ranges = [];
        $start = $previous = $years[0];

        foreach (array_slice($years, 1) as $year) {
            if ($year === $previous + 1) {
                $previous = $year;

                continue;
            }

            $ranges[] = self::range($start, $previous);
            $start = $previous = $year;
        }

        $ranges[] = self::range($start, $previous);

        return implode(', ', $ranges);
    }

    private static function range(int $from, int $to): string
    {
        return $from === $to ? (string) $from : "{$from}–{$to}";
    }
}
