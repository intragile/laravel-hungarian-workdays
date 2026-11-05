<?php

declare(strict_types=1);

namespace IntrAgile\HungarianWorkdays;

use RuntimeException;

/**
 * Thrown when a day is classified in a year the library cannot answer for: either no work
 * schedule is available for it, or it lies before the supported range (2013 onwards).
 */
final class UncoveredYearException extends RuntimeException
{
    public function __construct(public readonly int $year, ?string $message = null)
    {
        parent::__construct($message ??
            "No Hungarian work schedule is available for $year. "
            .'Add it via ScheduleRepository::with() or the "workdays.schedules" config, '
            .'or opt in with assumingNoTransfers().'
        );
    }

    public static function beforeSupportedRange(int $year): self
    {
        return new self($year, "Hungarian work schedules are supported from 2013; $year is outside the supported range.");
    }
}
