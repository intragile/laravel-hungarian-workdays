<?php

declare(strict_types=1);

namespace IntrAgile\HungarianWorkdays;

use RuntimeException;

/**
 * Thrown when a day is classified in a year for which no work schedule is available.
 */
final class UncoveredYearException extends RuntimeException
{
    public function __construct(public readonly int $year)
    {
        parent::__construct(
            "No Hungarian work schedule is available for $year. "
            .'Add it via ScheduleRepository::with() or the "workdays.schedules" config, '
            .'or opt in with assumingNoTransfers().'
        );
    }
}
