<?php

declare(strict_types=1);

namespace IntrAgile\HungarianWorkdays\Tests\Support;

use IntrAgile\HungarianWorkdays\Calendar;
use IntrAgile\HungarianWorkdays\Schedule;
use IntrAgile\HungarianWorkdays\ScheduleRepository;

trait BuildsFixtureCalendar
{
    /**
     * @param  list<string>  $restDays
     * @param  list<string>  $workedSaturdays
     */
    private function schedule(int $year, array $restDays = [], array $workedSaturdays = []): Schedule
    {
        return Schedule::fromArray($year, [
            'source' => 'Test decree',
            'url' => 'https://njt.jog.gov.hu/jogszabaly/test',
            'rest_days' => $restDays,
            'worked_saturdays' => $workedSaturdays,
        ]);
    }

    /** Calendar covering 2026 only, with two moved rest days and two worked Saturdays. */
    private function calendar(): Calendar
    {
        return new Calendar(new ScheduleRepository([
            2026 => $this->schedule(2026, ['2026-01-02', '2026-08-21'], ['2026-01-10', '2026-08-08']),
        ]));
    }
}
