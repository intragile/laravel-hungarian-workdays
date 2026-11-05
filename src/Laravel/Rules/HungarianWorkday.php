<?php

declare(strict_types=1);

namespace IntrAgile\HungarianWorkdays\Laravel\Rules;

use Closure;
use DateTimeImmutable;
use DateTimeInterface;
use Illuminate\Contracts\Validation\ValidationRule;
use IntrAgile\HungarianWorkdays\Calendar;
use IntrAgile\HungarianWorkdays\UncoveredYearException;

/**
 * Passes when the value is a date that is a Hungarian working day.
 *
 * Accepts DateTimeInterface instances and strings PHP can parse as a date. A year without a
 * known work schedule fails the rule instead of throwing.
 */
final class HungarianWorkday implements ValidationRule
{
    public const string TRANSLATION_NAMESPACE = 'hungarian-workdays';

    public const string NOT_A_DATE = 'validation.date';

    public const string NOT_A_WORKDAY = self::TRANSLATION_NAMESPACE.'::validation.hungarian_workday';

    public const string UNCOVERED = self::TRANSLATION_NAMESPACE.'::validation.hungarian_workday_uncovered';

    public function __construct(private readonly ?Calendar $calendar = null) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $key = $this->failureKey($value);

        if ($key !== null) {
            $fail($key)->translate();
        }
    }

    /**
     * The translation key of the failure message for the value, or null when it passes.
     */
    public function failureKey(mixed $value): ?string
    {
        $date = self::toDate($value);

        if ($date === null) {
            return self::NOT_A_DATE;
        }

        try {
            return $this->calendar()->isWorkday($date) ? null : self::NOT_A_WORKDAY;
        } catch (UncoveredYearException) {
            return self::UNCOVERED;
        }
    }

    private function calendar(): Calendar
    {
        return $this->calendar ?? app(Calendar::class);
    }

    private static function toDate(mixed $value): ?DateTimeInterface
    {
        if ($value instanceof DateTimeInterface) {
            return $value;
        }

        if (! is_string($value) || trim($value) === '' || strtotime($value) === false) {
            return null;
        }

        $parsed = date_parse($value);

        if ($parsed['error_count'] > 0 || $parsed['warning_count'] > 0) {
            return null;
        }

        return new DateTimeImmutable($value);
    }
}
