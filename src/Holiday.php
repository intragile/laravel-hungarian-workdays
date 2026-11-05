<?php

declare(strict_types=1);

namespace IntrAgile\HungarianWorkdays;

use DateTimeImmutable;
use DateTimeZone;

/**
 * Hungarian statutory public holidays (munkaszüneti napok).
 */
enum Holiday: string
{
    case NewYear = 'new-year';
    case NationalDay1848 = 'national-day-1848';
    case GoodFriday = 'good-friday';
    case EasterMonday = 'easter-monday';
    case LabourDay = 'labour-day';
    case WhitMonday = 'whit-monday';
    case StStephensDay = 'st-stephens-day';
    case NationalDay1956 = 'national-day-1956';
    case AllSaintsDay = 'all-saints-day';
    case ChristmasEve = 'christmas-eve';
    case ChristmasDay = 'christmas-day';
    case SecondDayOfChristmas = 'second-day-of-christmas';

    /**
     * Returns the date of this holiday in the given year at 00:00 UTC, or null when it
     * is not a public holiday in that year.
     */
    public function dateIn(int $year): ?DateTimeImmutable
    {
        if ($year < $this->effectiveFrom()) {
            return null;
        }

        $utc = new DateTimeZone('UTC');
        $fixed = fn (int $month, int $day): DateTimeImmutable => new DateTimeImmutable(
            sprintf('%04d-%02d-%02d 00:00:00', $year, $month, $day),
            $utc,
        );

        return match ($this) {
            self::NewYear => $fixed(1, 1),
            self::NationalDay1848 => $fixed(3, 15),
            self::GoodFriday => Easter::sunday($year)->modify('-2 days'),
            self::EasterMonday => Easter::sunday($year)->modify('+1 day'),
            self::LabourDay => $fixed(5, 1),
            self::WhitMonday => Easter::sunday($year)->modify('+50 days'),
            self::StStephensDay => $fixed(8, 20),
            self::NationalDay1956 => $fixed(10, 23),
            self::AllSaintsDay => $fixed(11, 1),
            self::ChristmasEve => $fixed(12, 24),
            self::ChristmasDay => $fixed(12, 25),
            self::SecondDayOfChristmas => $fixed(12, 26),
        };
    }

    /**
     * Returns the holidays of the given year keyed by Y-m-d and sorted by date.
     *
     * @return array<string, Holiday>
     */
    public static function forYear(int $year): array
    {
        $holidays = [];

        foreach (self::cases() as $holiday) {
            $date = $holiday->dateIn($year);

            if ($date !== null) {
                $holidays[$date->format('Y-m-d')] = $holiday;
            }
        }

        ksort($holidays);

        return $holidays;
    }

    public function hungarianName(): string
    {
        return match ($this) {
            self::NewYear => 'Újév',
            self::NationalDay1848 => 'Nemzeti ünnep',
            self::GoodFriday => 'Nagypéntek',
            self::EasterMonday => 'Húsvét hétfő',
            self::LabourDay => 'A munka ünnepe',
            self::WhitMonday => 'Pünkösdhétfő',
            self::StStephensDay => 'Az államalapítás ünnepe',
            self::NationalDay1956 => 'Nemzeti ünnep',
            self::AllSaintsDay => 'Mindenszentek',
            self::ChristmasEve => 'Szenteste',
            self::ChristmasDay => 'Karácsony',
            self::SecondDayOfChristmas => 'Karácsony másnapja',
        };
    }

    public function englishName(): string
    {
        return match ($this) {
            self::NewYear => "New Year's Day",
            self::NationalDay1848 => 'National Day (1848 Revolution)',
            self::GoodFriday => 'Good Friday',
            self::EasterMonday => 'Easter Monday',
            self::LabourDay => 'Labour Day',
            self::WhitMonday => 'Whit Monday',
            self::StStephensDay => "St Stephen's Day",
            self::NationalDay1956 => 'National Day (1956 Revolution)',
            self::AllSaintsDay => "All Saints' Day",
            self::ChristmasEve => 'Christmas Eve',
            self::ChristmasDay => 'Christmas Day',
            self::SecondDayOfChristmas => 'Second Day of Christmas',
        };
    }

    /**
     * First year in which this day is a statutory holiday. Granularity is a whole year
     * because both later additions take effect on 1 January.
     */
    private function effectiveFrom(): int
    {
        return match ($this) {
            self::GoodFriday => 2017,
            self::ChristmasEve => 2026,
            default => 2013,
        };
    }
}
