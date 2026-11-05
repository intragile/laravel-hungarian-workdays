<?php

declare(strict_types=1);

namespace IntrAgile\HungarianWorkdays\Laravel;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Container\Container;
use IntrAgile\HungarianWorkdays\Calendar;
use LogicException;

/**
 * Registers the Hungarian workday macros on Carbon and CarbonImmutable.
 *
 * Date-returning macros yield an instance of the receiver's own class at 00:00 in the
 * receiver's timezone and never modify the receiver.
 */
final readonly class CarbonMacros
{
    private function __construct(private Container $app) {}

    public static function register(Container $app): void
    {
        (new self($app))->registerOn(Carbon::class, CarbonImmutable::class);
    }

    /**
     * @param  class-string<Carbon|CarbonImmutable>  ...$classes
     */
    private function registerOn(string ...$classes): void
    {
        $calendar = $this->calendar(...);

        // Carbon rebinds $this of each macro closure to the receiver when it is called.
        $macros = [
            'isHungarianWorkday' => function () use ($calendar): bool {
                return $calendar()->isWorkday(CarbonMacros::receiver($this));
            },
            'isHungarianPublicHoliday' => function () use ($calendar): bool {
                return $calendar()->isPublicHoliday(CarbonMacros::receiver($this));
            },
            'addHungarianWorkdays' => function (int $days) use ($calendar) {
                $date = CarbonMacros::receiver($this);

                return $date::instance($calendar()->addWorkdays($date, $days));
            },
            'subHungarianWorkdays' => function (int $days) use ($calendar) {
                $date = CarbonMacros::receiver($this);

                return $date::instance($calendar()->subWorkdays($date, $days));
            },
            'nextHungarianWorkday' => function (bool $inclusive = false) use ($calendar) {
                $date = CarbonMacros::receiver($this);

                return $date::instance($calendar()->nextWorkday($date, $inclusive));
            },
            'previousHungarianWorkday' => function (bool $inclusive = false) use ($calendar) {
                $date = CarbonMacros::receiver($this);

                return $date::instance($calendar()->previousWorkday($date, $inclusive));
            },
        ];

        foreach ($classes as $class) {
            foreach ($macros as $name => $macro) {
                $class::macro($name, $macro);
            }
        }
    }

    private function calendar(): Calendar
    {
        return $this->app->make(Calendar::class);
    }

    /**
     * The object a macro was called on. Carbon binds it as `$this` of the macro closure and
     * scopes the closure to its own class, hence the public visibility.
     *
     * @internal
     */
    public static function receiver(object $bound): Carbon|CarbonImmutable
    {
        if (! $bound instanceof Carbon && ! $bound instanceof CarbonImmutable) {
            throw new LogicException('Hungarian workday macros can only be called on Carbon or CarbonImmutable.');
        }

        return $bound;
    }
}
