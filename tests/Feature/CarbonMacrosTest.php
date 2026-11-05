<?php

declare(strict_types=1);

namespace IntrAgile\HungarianWorkdays\Tests\Feature;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use IntrAgile\HungarianWorkdays\Tests\TestCase;
use IntrAgile\HungarianWorkdays\UncoveredYearException;
use PHPUnit\Framework\Attributes\DataProvider;

final class CarbonMacrosTest extends TestCase
{
    /**
     * @return array<string, array{class-string<Carbon|CarbonImmutable>}>
     */
    public static function classes(): array
    {
        return ['mutable' => [Carbon::class], 'immutable' => [CarbonImmutable::class]];
    }

    /**
     * @param  class-string<Carbon|CarbonImmutable>  $class
     */
    #[DataProvider('classes')]
    public function test_predicates(string $class): void
    {
        $this->assertTrue($class::parse('2026-03-02')->isHungarianWorkday());
        $this->assertFalse($class::parse('2026-03-14')->isHungarianWorkday());
        $this->assertFalse($class::parse('2026-01-02')->isHungarianWorkday());
        $this->assertTrue($class::parse('2026-01-10')->isHungarianWorkday());
        $this->assertTrue($class::parse('2026-01-01')->isHungarianPublicHoliday());
        $this->assertFalse($class::parse('2026-01-02')->isHungarianPublicHoliday());
    }

    /**
     * @param  class-string<Carbon|CarbonImmutable>  $class
     */
    #[DataProvider('classes')]
    public function test_arithmetic_returns_receiver_class_at_midnight_without_mutation(string $class): void
    {
        $date = $class::parse('2026-03-13 15:45:10', 'Europe/Budapest');

        $results = [
            'add' => [$date->addHungarianWorkdays(1), '2026-03-16'],
            'sub' => [$date->subHungarianWorkdays(1), '2026-03-12'],
            'next' => [$date->nextHungarianWorkday(), '2026-03-16'],
            'nextInclusive' => [$date->nextHungarianWorkday(true), '2026-03-13'],
            'previous' => [$date->previousHungarianWorkday(), '2026-03-12'],
            'previousInclusive' => [$date->previousHungarianWorkday(true), '2026-03-13'],
        ];

        foreach ($results as $name => [$result, $expected]) {
            $this->assertInstanceOf($class, $result, $name);
            $this->assertSame($class, $result::class, $name);
            $this->assertSame($expected.' 00:00:00', $result->format('Y-m-d H:i:s'), $name);
            $this->assertSame('Europe/Budapest', $result->getTimezone()->getName(), $name);
        }

        $this->assertSame('2026-03-13 15:45:10', $date->format('Y-m-d H:i:s'));
    }

    public function test_default_argument_of_inclusive_is_false(): void
    {
        $next = Carbon::parse('2026-03-13')->nextHungarianWorkday();
        $previous = Carbon::parse('2026-03-13')->previousHungarianWorkday();

        $this->assertInstanceOf(Carbon::class, $next);
        $this->assertInstanceOf(Carbon::class, $previous);
        $this->assertSame('2026-03-16', $next->toDateString());
        $this->assertSame('2026-03-12', $previous->toDateString());
    }

    public function test_uncovered_year_throws(): void
    {
        $this->expectException(UncoveredYearException::class);

        Carbon::parse('2030-01-02')->isHungarianWorkday();
    }
}
