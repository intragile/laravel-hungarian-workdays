<?php

declare(strict_types=1);

namespace IntrAgile\HungarianWorkdays\Tests\Feature;

use DateTimeImmutable;
use Illuminate\Filesystem\Filesystem;
use IntrAgile\HungarianWorkdays\Calendar;
use IntrAgile\HungarianWorkdays\InvalidScheduleException;
use IntrAgile\HungarianWorkdays\Laravel\Facades\Workdays;
use IntrAgile\HungarianWorkdays\Tests\TestCase;

final class ServiceProviderTest extends TestCase
{
    public function test_calendar_is_a_singleton_with_bundled_schedules(): void
    {
        $calendar = app(Calendar::class);

        $this->assertSame($calendar, app(Calendar::class));
        $this->assertTrue($calendar->isCovered(2026));
        $this->assertFalse($calendar->isCovered(2099));
    }

    public function test_configured_schedule_is_added_to_the_calendar(): void
    {
        config()->set('workdays.schedules', [
            2027 => [
                'source' => 'Test decree',
                'url' => 'https://njt.jog.gov.hu/jogszabaly/test',
                'rest_days' => ['2027-05-14'],
                'worked_saturdays' => ['2027-05-22'],
            ],
        ]);

        $calendar = app(Calendar::class);

        $this->assertTrue($calendar->isCovered(2027));
        $this->assertFalse($calendar->isWorkday(new DateTimeImmutable('2027-05-14')));
        $this->assertTrue($calendar->isWorkday(new DateTimeImmutable('2027-05-22')));
    }

    public function test_configured_schedule_overrides_the_bundled_year(): void
    {
        config()->set('workdays.schedules', [
            2026 => ['source' => 'Override', 'url' => 'https://example.test', 'rest_days' => [], 'worked_saturdays' => []],
        ]);

        $this->assertTrue(app(Calendar::class)->isWorkday(new DateTimeImmutable('2026-01-02')));
    }

    public function test_invalid_configured_schedule_throws(): void
    {
        config()->set('workdays.schedules', [
            2027 => ['source' => 'Bad', 'url' => 'https://example.test', 'rest_days' => ['2027-05-15'], 'worked_saturdays' => []],
        ]);

        $this->expectException(InvalidScheduleException::class);

        app(Calendar::class);
    }

    public function test_non_year_config_key_throws_naming_the_key(): void
    {
        config()->set('workdays.schedules', [
            'next' => ['source' => 'Bad', 'url' => 'https://example.test', 'rest_days' => [], 'worked_saturdays' => []],
        ]);

        $this->expectException(InvalidScheduleException::class);
        $this->expectExceptionMessage("Config 'workdays.schedules' key 'next' is not a year");

        app(Calendar::class);
    }

    public function test_non_array_config_entry_throws_naming_the_key(): void
    {
        config()->set('workdays.schedules', [2027 => 'not an array']);

        $this->expectException(InvalidScheduleException::class);
        $this->expectExceptionMessage("Config 'workdays.schedules' entry for 2027 must be an array.");

        app(Calendar::class);
    }

    public function test_non_array_schedules_config_throws(): void
    {
        config()->set('workdays.schedules', 'oops');

        $this->expectException(InvalidScheduleException::class);
        $this->expectExceptionMessage("Config 'workdays.schedules' must be an array keyed by year.");

        app(Calendar::class);
    }

    public function test_integer_like_string_key_is_accepted(): void
    {
        config()->set('workdays.schedules', [
            '2027' => ['source' => 'Test', 'url' => 'https://example.test', 'rest_days' => [], 'worked_saturdays' => []],
        ]);

        $this->assertTrue(app(Calendar::class)->isCovered(2027));
    }

    public function test_assume_no_transfers_defaults_to_false_and_can_be_enabled(): void
    {
        $this->assertFalse(config()->get('workdays.assume_no_transfers'));
        $this->assertSame([], config()->get('workdays.schedules'));

        config()->set('workdays.assume_no_transfers', true);
        $calendar = app(Calendar::class);

        $this->assertTrue($calendar->isWorkday(new DateTimeImmutable('2099-03-03')));
    }

    public function test_facade_proxies_the_calendar(): void
    {
        $this->assertTrue(Workdays::isCovered(2026));
        $this->assertFalse(Workdays::isWorkday(new DateTimeImmutable('2026-01-01')));
    }

    public function test_config_and_lang_are_publishable(): void
    {
        $this->command('vendor:publish', ['--tag' => 'workdays-config', '--force' => true])->assertSuccessful();
        $this->assertFileExists(config_path('workdays.php'));
        unlink(config_path('workdays.php'));

        $this->command('vendor:publish', ['--tag' => 'workdays-lang', '--force' => true])->assertSuccessful();
        $this->assertFileExists(lang_path('vendor/hungarian-workdays/hu/validation.php'));
        $this->assertFileExists(lang_path('vendor/hungarian-workdays/en/validation.php'));
        (new Filesystem)->deleteDirectory(lang_path('vendor/hungarian-workdays'));
    }
}
