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
            'workdays' => ['2025-05-17'],
        ], $overrides);
    }

    public function test_valid_input_yields_a_schedule(): void
    {
        $schedule = Schedule::fromArray(2025, $this->data());

        $this->assertSame(2025, $schedule->year);
        $this->assertSame('Test decree', $schedule->source);
        $this->assertSame('https://njt.jog.gov.hu/jogszabaly/test', $schedule->url);
        $this->assertSame(['2025-05-02'], $schedule->restDays);
        $this->assertSame(['2025-05-17'], $schedule->workdays);
    }

    public function test_empty_lists_are_valid(): void
    {
        $schedule = Schedule::fromArray(2023, $this->data(['rest_days' => [], 'workdays' => []]));

        $this->assertSame([], $schedule->restDays);
        $this->assertSame([], $schedule->workdays);
    }

    public function test_dates_outside_the_decree_year_are_accepted(): void
    {
        $schedule = Schedule::fromArray(2013, $this->data([
            'rest_days' => ['2012-12-31'],
            'workdays' => ['2012-12-01'],
        ]));

        $this->assertSame(['2012-12-31'], $schedule->restDays);
    }

    public function test_rejects_an_invalid_date(): void
    {
        $this->expectException(InvalidScheduleException::class);
        Schedule::fromArray(2025, $this->data(['workdays' => ['2025-02-30']]));
    }

    public function test_rejects_a_malformed_date(): void
    {
        $this->expectException(InvalidScheduleException::class);
        Schedule::fromArray(2025, $this->data(['workdays' => ['17/05/2025']]));
    }

    public function test_rejects_a_workday_that_is_not_a_saturday(): void
    {
        $this->expectException(InvalidScheduleException::class);
        Schedule::fromArray(2025, $this->data(['workdays' => ['2025-05-18']]));
    }

    public function test_rejects_a_weekend_rest_day(): void
    {
        $this->expectException(InvalidScheduleException::class);
        Schedule::fromArray(2025, $this->data(['rest_days' => ['2025-05-03']]));
    }

    public function test_rejects_a_rest_day_that_is_a_public_holiday(): void
    {
        $this->expectException(InvalidScheduleException::class);
        Schedule::fromArray(2025, $this->data(['rest_days' => ['2025-05-01']]));
    }

    public function test_rejects_a_day_listed_as_both_rest_day_and_workday(): void
    {
        $this->expectException(InvalidScheduleException::class);
        Schedule::fromArray(2025, $this->data([
            'rest_days' => ['2025-05-02', '2025-05-17'],
            'workdays' => ['2025-05-17'],
        ]));
    }

    public function test_rejects_missing_keys(): void
    {
        $data = $this->data();
        unset($data['url']);

        $this->expectException(InvalidScheduleException::class);
        Schedule::fromArray(2025, $data);
    }

    public function test_rejects_non_string_values(): void
    {
        $this->expectException(InvalidScheduleException::class);
        Schedule::fromArray(2025, $this->data(['source' => 42]));
    }

    public function test_rejects_a_non_list_of_dates(): void
    {
        $this->expectException(InvalidScheduleException::class);
        Schedule::fromArray(2025, $this->data(['workdays' => '2025-05-17']));
    }

    public function test_rejects_a_non_string_date(): void
    {
        $this->expectException(InvalidScheduleException::class);
        Schedule::fromArray(2025, $this->data(['rest_days' => [20250502]]));
    }

    public function test_exception_is_an_invalid_argument_exception(): void
    {
        $this->assertInstanceOf(\InvalidArgumentException::class, new InvalidScheduleException('x'));
    }
}
