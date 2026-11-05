<?php

declare(strict_types=1);

namespace IntrAgile\HungarianWorkdays\Tests\Unit;

use IntrAgile\HungarianWorkdays\Easter;
use IntrAgile\HungarianWorkdays\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class EasterTest extends TestCase
{
    /**
     * @return array<int, array{int, string}>
     */
    public static function knownSundays(): array
    {
        return [
            [2013, '2013-03-31'],
            [2016, '2016-03-27'],
            [2019, '2019-04-21'],
            [2024, '2024-03-31'],
            [2025, '2025-04-20'],
            [2026, '2026-04-05'],
            [2027, '2027-03-28'],
            [2038, '2038-04-25'],
        ];
    }

    #[DataProvider('knownSundays')]
    public function test_known_easter_sundays(int $year, string $expected): void
    {
        $this->assertSame($expected, Easter::sunday($year)->format('Y-m-d'));
    }

    public function test_sunday_is_midnight_utc(): void
    {
        $date = Easter::sunday(2026);

        $this->assertSame('UTC', $date->getTimezone()->getName());
        $this->assertSame('00:00:00', $date->format('H:i:s'));
    }
}
