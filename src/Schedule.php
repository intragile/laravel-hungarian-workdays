<?php

declare(strict_types=1);

namespace IntrAgile\HungarianWorkdays;

use DateTimeImmutable;
use DateTimeZone;

/**
 * The work schedule decreed for one calendar year: moved rest days and the Saturdays
 * worked in exchange.
 *
 * Dates may lie outside the decree year; a decree can legitimately reach into the
 * preceding year.
 */
final readonly class Schedule
{
    /**
     * @param  list<string>  $restDays  Moved rest days (Y-m-d)
     * @param  list<string>  $workdays  Worked Saturdays (Y-m-d)
     */
    private function __construct(
        public int $year,
        public string $source,
        public string $url,
        public array $restDays,
        public array $workdays,
    ) {}

    /**
     * @param  array<mixed>  $data  Keys: source, url, rest_days, workdays
     *
     * @throws InvalidScheduleException
     */
    public static function fromArray(int $year, array $data): self
    {
        $source = self::string($year, $data, 'source');
        $url = self::string($year, $data, 'url');
        $restDays = self::dates($year, $data, 'rest_days');
        $workdays = self::dates($year, $data, 'workdays');

        foreach ($workdays as $date) {
            if (self::day($date)->format('N') !== '6') {
                throw new InvalidScheduleException("Schedule $year: worked day $date is not a Saturday.");
            }
        }

        foreach ($restDays as $date) {
            $day = self::day($date);

            if ((int) $day->format('N') >= 6) {
                throw new InvalidScheduleException("Schedule $year: rest day $date falls on a weekend.");
            }

            if (isset(Holiday::forYear((int) $day->format('Y'))[$date])) {
                throw new InvalidScheduleException("Schedule $year: rest day $date is a public holiday.");
            }
        }

        $overlap = array_intersect($restDays, $workdays);

        if ($overlap !== []) {
            throw new InvalidScheduleException(
                "Schedule $year: ".reset($overlap).' is both a rest day and a worked day.',
            );
        }

        return new self($year, $source, $url, $restDays, $workdays);
    }

    /**
     * @param  array<mixed>  $data
     */
    private static function string(int $year, array $data, string $key): string
    {
        $value = $data[$key] ?? null;

        if (! is_string($value)) {
            throw new InvalidScheduleException("Schedule $year: '$key' must be a string.");
        }

        return $value;
    }

    /**
     * @param  array<mixed>  $data
     * @return list<string>
     */
    private static function dates(int $year, array $data, string $key): array
    {
        $value = $data[$key] ?? null;

        if (! is_array($value) || ! array_is_list($value)) {
            throw new InvalidScheduleException("Schedule $year: '$key' must be a list of dates.");
        }

        $dates = [];

        foreach ($value as $date) {
            if (! is_string($date)) {
                throw new InvalidScheduleException("Schedule $year: '$key' must contain only Y-m-d strings.");
            }

            self::day($date, $year);
            $dates[] = $date;
        }

        return $dates;
    }

    private static function day(string $date, ?int $year = null): DateTimeImmutable
    {
        $day = DateTimeImmutable::createFromFormat('!Y-m-d', $date, new DateTimeZone('UTC'));

        if ($day === false || $day->format('Y-m-d') !== $date) {
            throw new InvalidScheduleException('Schedule'.($year === null ? '' : " $year").": '$date' is not a valid Y-m-d date.");
        }

        return $day;
    }
}
