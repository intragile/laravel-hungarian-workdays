<?php

declare(strict_types=1);

namespace IntrAgile\HungarianWorkdays\Tests\Unit;

use IntrAgile\HungarianWorkdays\ScheduleRepository;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CheckCoverageScriptTest extends TestCase
{
    private static function lastBundledYear(): int
    {
        return max([2013, ...ScheduleRepository::bundled()->years()]);
    }

    /**
     * @return array{0: int, 1: string}
     */
    private function runScript(string $date): array
    {
        $output = [];
        $code = 0;
        exec(escapeshellarg(PHP_BINARY).' '.escapeshellarg(__DIR__.'/../../bin/check-coverage.php').' '.escapeshellarg($date).' 2>&1', $output, $code);

        return [$code, implode("\n", $output)];
    }

    public function test_passes_early_in_a_covered_year(): void
    {
        $last = self::lastBundledYear();

        [$code, $output] = $this->runScript("$last-06-01");

        $this->assertSame(0, $code, $output);
    }

    public function test_fails_in_january_when_the_current_year_is_not_bundled(): void
    {
        $uncovered = self::lastBundledYear() + 1;

        [$code, $output] = $this->runScript("$uncovered-01-05");

        $this->assertSame(1, $code);
        $this->assertStringContainsString("current year, $uncovered", $output);
    }

    public function test_fails_from_october_31_when_next_year_is_not_bundled(): void
    {
        $last = self::lastBundledYear();

        [$code, $output] = $this->runScript("$last-10-31");

        $this->assertSame(1, $code);
        $this->assertStringContainsString('but the decree was due by 31 October', $output);
    }

    public function test_october_30_still_passes(): void
    {
        $last = self::lastBundledYear();

        [$code] = $this->runScript("$last-10-30");

        $this->assertSame(0, $code);
    }

    #[DataProvider('invalidDates')]
    public function test_rejects_an_invalid_date_argument(string $date): void
    {
        [$code] = $this->runScript($date);

        $this->assertSame(2, $code);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function invalidDates(): array
    {
        return ['text' => ['tomorrow'], 'wrong format' => ['05/11/2026']];
    }
}
