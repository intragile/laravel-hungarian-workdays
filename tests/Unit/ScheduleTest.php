<?php

declare(strict_types=1);

namespace IntrAgile\HungarianWorkdays\Tests\Unit;

use IntrAgile\HungarianWorkdays\InvalidScheduleException;
use IntrAgile\HungarianWorkdays\Schedule;
use IntrAgile\HungarianWorkdays\Tests\TestCase;

final class ScheduleTest extends TestCase
{
    /**
     * @param  array<mixed>  $overrides
     * @return array<mixed>
     */
    private function data(array $overrides = []): array
    {
        return array_merge([
            'source' => 'Test decree',
            'url' => 'https://njt.jog.gov.hu/jogszabaly/test',
            'rest_days' => ['2025-05-02'],
            'worked_saturdays' => ['2025-05-17'],
        ], $overrides);
    }

    public function test_valid_input_yields_a_schedule(): void
    {
        $schedule = Schedule::fromArray(2025, $this->data());

        $this->assertSame(2025, $schedule->year);
        $this->assertSame('Test decree', $schedule->source);
        $this->assertSame('https://njt.jog.gov.hu/jogszabaly/test', $schedule->url);
        $this->assertSame(['2025-05-02'], $schedule->restDays);
        $this->assertSame(['2025-05-17'], $schedule->workedSaturdays);
    }

    public function test_empty_lists_are_valid(): void
    {
        $schedule = Schedule::fromArray(2023, $this->data(['rest_days' => [], 'worked_saturdays' => []]));

        $this->assertSame([], $schedule->restDays);
        $this->assertSame([], $schedule->workedSaturdays);
    }

    public function test_dates_outside_the_decree_year_are_accepted(): void
    {
        $schedule = Schedule::fromArray(2013, $this->data([
            'rest_days' => ['2012-12-31'],
            'worked_saturdays' => ['2012-12-01'],
        ]));

        $this->assertSame(['2012-12-31'], $schedule->restDays);
    }

    public function test_rejects_a_year_before_2013(): void
    {
        $this->expectException(InvalidScheduleException::class);
        $this->expectExceptionMessage('Schedule 2012: schedules are supported from 2013.');
        Schedule::fromArray(2012, $this->data(['rest_days' => [], 'worked_saturdays' => []]));
    }

    public function test_rejects_a_date_more_than_one_year_before_the_decree_year(): void
    {
        $this->expectException(InvalidScheduleException::class);
        $this->expectExceptionMessage("Schedule 2025: '2023-12-29' lies outside 2024 and 2025.");
        Schedule::fromArray(2025, $this->data(['rest_days' => ['2023-12-29']]));
    }

    public function test_rejects_a_date_in_the_year_after_the_decree_year(): void
    {
        $this->expectException(InvalidScheduleException::class);
        $this->expectExceptionMessage("Schedule 2025: '2026-01-02' lies outside 2024 and 2025.");
        Schedule::fromArray(2025, $this->data(['rest_days' => ['2026-01-02']]));
    }

    public function test_rejects_an_invalid_date(): void
    {
        $this->expectExceptionMessage('is not a valid Y-m-d date');
        $this->expectException(InvalidScheduleException::class);
        Schedule::fromArray(2025, $this->data(['worked_saturdays' => ['2025-02-30']]));
    }

    public function test_rejects_a_malformed_date(): void
    {
        $this->expectExceptionMessage('is not a valid Y-m-d date');
        $this->expectException(InvalidScheduleException::class);
        Schedule::fromArray(2025, $this->data(['worked_saturdays' => ['17/05/2025']]));
    }

    public function test_rejects_a_workday_that_is_not_a_saturday(): void
    {
        $this->expectExceptionMessage('is not a Saturday');
        $this->expectException(InvalidScheduleException::class);
        Schedule::fromArray(2025, $this->data(['worked_saturdays' => ['2025-05-18']]));
    }

    public function test_rejects_a_weekend_rest_day(): void
    {
        $this->expectExceptionMessage('falls on a weekend');
        $this->expectException(InvalidScheduleException::class);
        Schedule::fromArray(2025, $this->data(['rest_days' => ['2025-05-03']]));
    }

    public function test_rejects_a_rest_day_that_is_a_public_holiday(): void
    {
        $this->expectExceptionMessage('is a public holiday');
        $this->expectException(InvalidScheduleException::class);
        Schedule::fromArray(2025, $this->data(['rest_days' => ['2025-05-01']]));
    }

    public function test_a_day_listed_in_both_lists_is_rejected_by_the_weekday_rule(): void
    {
        $this->expectExceptionMessage('falls on a weekend');
        $this->expectException(InvalidScheduleException::class);
        Schedule::fromArray(2025, $this->data([
            'rest_days' => ['2025-05-02', '2025-05-17'],
            'worked_saturdays' => ['2025-05-17'],
        ]));
    }

    public function test_rejects_a_workday_that_is_a_public_holiday(): void
    {
        // 2014-03-15 is a Saturday and a public holiday.
        $this->expectException(InvalidScheduleException::class);
        $this->expectExceptionMessage('worked day 2014-03-15 is a public holiday');
        Schedule::fromArray(2014, $this->data(['rest_days' => [], 'worked_saturdays' => ['2014-03-15']]));
    }

    public function test_rejects_missing_keys(): void
    {
        $this->expectExceptionMessage("'url' must be a string");
        $data = $this->data();
        unset($data['url']);

        $this->expectException(InvalidScheduleException::class);
        Schedule::fromArray(2025, $data);
    }

    public function test_rejects_non_string_values(): void
    {
        $this->expectExceptionMessage("'source' must be a string");
        $this->expectException(InvalidScheduleException::class);
        Schedule::fromArray(2025, $this->data(['source' => 42]));
    }

    public function test_rejects_a_non_list_of_dates(): void
    {
        $this->expectExceptionMessage("'worked_saturdays' must be a list of dates");
        $this->expectException(InvalidScheduleException::class);
        Schedule::fromArray(2025, $this->data(['worked_saturdays' => '2025-05-17']));
    }

    public function test_rejects_a_non_string_date(): void
    {
        $this->expectExceptionMessage('must contain only Y-m-d strings');
        $this->expectException(InvalidScheduleException::class);
        Schedule::fromArray(2025, $this->data(['rest_days' => [20250502]]));
    }

    public function test_exception_is_an_invalid_argument_exception(): void
    {
        $this->assertInstanceOf(\InvalidArgumentException::class, new InvalidScheduleException('x'));
    }
}
