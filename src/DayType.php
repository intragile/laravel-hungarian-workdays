<?php

declare(strict_types=1);

namespace IntrAgile\HungarianWorkdays;

/**
 * Classification of a calendar day under the Hungarian general work schedule.
 */
enum DayType: string
{
    case Workday = 'workday';
    case Weekend = 'weekend';
    case PublicHoliday = 'public-holiday';
    /** A weekday turned into a rest day by the yearly work schedule decree. */
    case RestDay = 'rest-day';
    /** A Saturday worked in exchange for a moved rest day. */
    case TransferredWorkday = 'transferred-workday';

    public function isWorkday(): bool
    {
        return $this === self::Workday || $this === self::TransferredWorkday;
    }
}
