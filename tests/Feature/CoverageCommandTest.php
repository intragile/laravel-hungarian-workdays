<?php

declare(strict_types=1);

namespace IntrAgile\HungarianWorkdays\Tests\Feature;

use Carbon\Carbon;
use IntrAgile\HungarianWorkdays\ScheduleRepository;
use IntrAgile\HungarianWorkdays\Tests\TestCase;

final class CoverageCommandTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    /**
     * The bundled range as the command prints it; the bundled years are contiguous from 2013.
     */
    private static function bundledRange(): string
    {
        return '2013–'.max([2013, ...ScheduleRepository::bundled()->years()]);
    }

    /**
     * Adds empty schedules for the given years, as an application would via configuration.
     */
    private function cover(int ...$years): void
    {
        $schedules = [];

        foreach ($years as $year) {
            $schedules[$year] = ['source' => 'Test', 'url' => 'https://example.test', 'rest_days' => [], 'worked_saturdays' => []];
        }

        config()->set('workdays.schedules', $schedules);
    }

    public function test_prints_covered_years_and_succeeds_early_in_the_year(): void
    {
        Carbon::setTestNow('2040-06-01');
        $this->cover(2040);

        $this->command('workdays:coverage')
            ->expectsOutputToContain('Covered years: '.self::bundledRange())
            ->assertExitCode(0);
    }

    public function test_fails_with_a_warning_from_october_31_when_next_year_is_missing(): void
    {
        Carbon::setTestNow('2040-11-05');

        $this->command('workdays:coverage')
            ->expectsOutputToContain('Covered years: '.self::bundledRange())
            ->expectsOutputToContain('2041')
            ->assertExitCode(1);
    }

    public function test_boundary_day_october_31_fails_and_october_30_passes(): void
    {
        $this->cover(2040);

        Carbon::setTestNow('2040-10-30');
        $this->command('workdays:coverage')->assertExitCode(0);

        Carbon::setTestNow('2040-10-31');
        $this->command('workdays:coverage')->assertExitCode(1);
    }

    public function test_fails_in_january_when_the_current_year_is_not_covered(): void
    {
        Carbon::setTestNow('2041-01-15');

        $this->command('workdays:coverage')
            ->expectsOutputToContain('current year, 2041')
            ->assertExitCode(1);
    }

    public function test_succeeds_in_january_when_the_current_year_is_covered(): void
    {
        Carbon::setTestNow('2041-01-15');
        $this->cover(2041);

        $this->command('workdays:coverage')->assertExitCode(0);
    }

    public function test_reports_both_gaps_in_november_when_nothing_is_covered(): void
    {
        Carbon::setTestNow('2041-11-05');

        $this->command('workdays:coverage')
            ->expectsOutputToContain('current year, 2041')
            ->expectsOutputToContain('2042 yet')
            ->assertExitCode(1);
    }

    public function test_succeeds_when_next_year_is_covered(): void
    {
        Carbon::setTestNow('2040-11-05');
        $this->cover(2040, 2041);

        $this->command('workdays:coverage')->assertExitCode(0);
    }

    public function test_lists_non_contiguous_years(): void
    {
        Carbon::setTestNow('2040-06-01');
        $this->cover(2040, 2099);

        $this->command('workdays:coverage')
            ->expectsOutputToContain('Covered years: '.self::bundledRange().', 2040, 2099')
            ->assertExitCode(0);
    }
}
