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

    public function test_prints_covered_years_and_succeeds_early_in_the_year(): void
    {
        Carbon::setTestNow('2040-06-01');

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
        Carbon::setTestNow('2040-10-30');
        $this->command('workdays:coverage')->assertExitCode(0);

        Carbon::setTestNow('2040-10-31');
        $this->command('workdays:coverage')->assertExitCode(1);
    }

    public function test_succeeds_when_next_year_is_covered(): void
    {
        Carbon::setTestNow('2040-11-05');
        config()->set('workdays.schedules', [
            2041 => ['source' => 'Test', 'url' => 'https://example.test', 'rest_days' => [], 'workdays' => []],
        ]);

        $this->command('workdays:coverage')->assertExitCode(0);
    }

    public function test_lists_non_contiguous_years(): void
    {
        Carbon::setTestNow('2040-06-01');
        config()->set('workdays.schedules', [
            2099 => ['source' => 'Test', 'url' => 'https://example.test', 'rest_days' => [], 'workdays' => []],
        ]);

        $this->command('workdays:coverage')
            ->expectsOutputToContain('Covered years: '.self::bundledRange().', 2099')
            ->assertExitCode(0);
    }
}
