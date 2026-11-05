<?php

declare(strict_types=1);

namespace IntrAgile\HungarianWorkdays\Tests\Unit;

use IntrAgile\HungarianWorkdays\Schedule;
use IntrAgile\HungarianWorkdays\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class BundledSchedulesTest extends TestCase
{
    /**
     * @return array<mixed>
     */
    private static function bundled(): array
    {
        $data = require __DIR__.'/../../resources/schedules.php';
        self::assertIsArray($data);

        return $data;
    }

    public function test_covers_every_year_from_2013_to_2026(): void
    {
        $this->assertSame(range(2013, 2026), array_keys(self::bundled()));
    }

    public function test_every_bundled_year_is_a_valid_schedule(): void
    {
        foreach (range(2013, 2026) as $year) {
            $this->assertSame($year, $this->schedule($year)->year);
        }
    }

    public function test_every_year_has_a_source_and_https_url(): void
    {
        foreach (range(2013, 2026) as $year) {
            $schedule = $this->schedule($year);
            $this->assertNotSame('', $schedule->source, "year $year");
            $this->assertStringStartsWith('https://', $schedule->url, "year $year");
        }
    }

    public function test_2026_has_no_december_12_workday(): void
    {
        $this->assertNotContains('2026-12-12', $this->schedule(2026)->workedSaturdays);
    }

    public function test_2026_has_no_december_24_rest_day(): void
    {
        $this->assertNotContains('2026-12-24', $this->schedule(2026)->restDays);
    }

    public function test_2017_and_2023_are_covered_without_transfers(): void
    {
        foreach ([2017, 2023] as $year) {
            $schedule = $this->schedule($year);
            $this->assertSame([], $schedule->restDays);
            $this->assertSame([], $schedule->workedSaturdays);
        }
    }

    public function test_2013_keeps_the_pair_dated_in_the_previous_year(): void
    {
        $schedule = $this->schedule(2013);
        $this->assertContains('2012-12-31', $schedule->restDays);
        $this->assertContains('2012-12-01', $schedule->workedSaturdays);
    }

    /**
     * Pairs hand-copied from the text of each decree.
     *
     * @see https://njt.jog.gov.hu/jogszabaly/2012-28-20-2X 28/2012. (IX. 4.) NGM rendelet, 2. § b)
     * @see https://njt.jog.gov.hu/jogszabaly/2013-33-20-2X 33/2013. (VIII. 30.) NGM rendelet, 2. § a)
     * @see https://njt.jog.gov.hu/jogszabaly/2014-28-20-2X 28/2014. (IX. 24.) NGM rendelet, 2. § b)
     * @see https://njt.jog.gov.hu/jogszabaly/2015-18-20-2X 18/2015. (VI. 29.) NGM rendelet, 2. § a)
     * @see https://njt.jog.gov.hu/jogszabaly/2017-9-20-2X 9/2017. (V. 19.) NGM rendelet, 2. § d)
     * @see https://njt.jog.gov.hu/jogszabaly/2018-6-20-53 6/2018. (VIII. 23.) PM rendelet, 2. § a)
     * @see https://njt.jog.gov.hu/jogszabaly/2019-7-20-53 7/2019. (VI. 25.) PM rendelet, 2. § a)
     * @see https://njt.jog.gov.hu/jogszabaly/2020-14-20-7Q 14/2020. (V. 13.) ITM rendelet
     * @see https://njt.jog.gov.hu/jogszabaly/2021-23-20-7Q 23/2021. (VI. 1.) ITM rendelet, 2. § a)
     * @see https://njt.jog.gov.hu/jogszabaly/2023-15-20-8P 15/2023. (VII. 13.) GFM rendelet, 2. § a)
     * @see https://njt.jog.gov.hu/jogszabaly/2024-11-20-2X 11/2024. (IV. 8.) NGM rendelet, 2. § a)
     * @see https://njt.jog.gov.hu/jogszabaly/2025-10-20-2X 10/2025. (IV. 30.) NGM rendelet, 2. § b)
     *
     * @return array<int, array{int, string, string}>
     */
    public static function decreePairs(): array
    {
        return [
            '2013' => [2013, '2013-08-19', '2013-08-24'],
            '2014' => [2014, '2014-05-02', '2014-05-10'],
            '2015' => [2015, '2015-08-21', '2015-08-08'],
            '2016' => [2016, '2016-03-14', '2016-03-05'],
            '2018' => [2018, '2018-11-02', '2018-11-10'],
            '2019' => [2019, '2019-08-19', '2019-08-10'],
            '2020' => [2020, '2020-08-21', '2020-08-29'],
            '2021' => [2021, '2021-12-24', '2021-12-11'],
            '2022' => [2022, '2022-03-14', '2022-03-26'],
            '2024' => [2024, '2024-08-19', '2024-08-03'],
            '2025' => [2025, '2025-05-02', '2025-05-17'],
            '2026' => [2026, '2026-08-21', '2026-08-08'],
        ];
    }

    #[DataProvider('decreePairs')]
    public function test_decree_pair_is_bundled(int $year, string $restDay, string $workday): void
    {
        $schedule = $this->schedule($year);
        $this->assertContains($restDay, $schedule->restDays);
        $this->assertContains($workday, $schedule->workedSaturdays);
    }

    public function test_2026_keeps_the_two_remaining_pairs(): void
    {
        $schedule = $this->schedule(2026);
        $this->assertSame(['2026-01-02', '2026-08-21'], $schedule->restDays);
        $this->assertSame(['2026-01-10', '2026-08-08'], $schedule->workedSaturdays);
    }

    private function schedule(int $year): Schedule
    {
        $data = self::bundled()[$year] ?? null;
        $this->assertIsArray($data);

        return Schedule::fromArray($year, $data);
    }
}
