<?php

declare(strict_types=1);

namespace IntrAgile\HungarianWorkdays\Tests\Unit;

use IntrAgile\HungarianWorkdays\Schedule;
use IntrAgile\HungarianWorkdays\ScheduleRepository;
use IntrAgile\HungarianWorkdays\Tests\TestCase;

final class ScheduleRepositoryTest extends TestCase
{
    /**
     * @param  list<string>  $restDays
     * @param  list<string>  $workdays
     */
    private function schedule(int $year, array $restDays = [], array $workdays = []): Schedule
    {
        return Schedule::fromArray($year, [
            'source' => 'Test decree',
            'url' => 'https://njt.jog.gov.hu/jogszabaly/test',
            'rest_days' => $restDays,
            'workdays' => $workdays,
        ]);
    }

    public function test_years_are_sorted_and_has_reports_coverage(): void
    {
        $repository = new ScheduleRepository([
            2030 => $this->schedule(2030),
            2026 => $this->schedule(2026),
        ]);

        $this->assertSame([2026, 2030], $repository->years());
        $this->assertTrue($repository->has(2026));
        $this->assertFalse($repository->has(2027));
    }

    public function test_with_replaces_the_same_year_and_is_immutable(): void
    {
        $original = new ScheduleRepository([2026 => $this->schedule(2026, ['2026-01-02'], ['2026-01-10'])]);

        $replaced = $original->with($this->schedule(2026, ['2026-08-21'], ['2026-08-08']), $this->schedule(2027));

        $this->assertSame(['2026-01-02' => true], $original->restDays());
        $this->assertSame([2026], $original->years());
        $this->assertSame(['2026-08-21' => true], $replaced->restDays());
        $this->assertSame(['2026-08-08' => true], $replaced->workdays());
        $this->assertSame([2026, 2027], $replaced->years());
    }

    public function test_date_index_is_built_by_actual_date_not_by_schedule_year(): void
    {
        $repository = new ScheduleRepository([2030 => $this->schedule(2030, [], ['2029-12-29'])]);

        $this->assertSame(['2029-12-29' => true], $repository->workdays());
    }

    public function test_bundled_repository_covers_a_contiguous_range_from_2013_to_at_least_2026(): void
    {
        $repository = ScheduleRepository::bundled();
        $years = $repository->years();

        $this->assertSame(2013, $years[0]);
        $this->assertGreaterThanOrEqual(2026, max([2013, ...$years]));
        $this->assertSame(range(2013, max([2013, ...$years])), $years);
        $this->assertArrayHasKey('2026-01-10', $repository->workdays());
    }
}
