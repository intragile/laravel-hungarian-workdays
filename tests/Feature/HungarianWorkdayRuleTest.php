<?php

declare(strict_types=1);

namespace IntrAgile\HungarianWorkdays\Tests\Feature;

use DateTimeImmutable;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Support\Facades\Validator;
use IntrAgile\HungarianWorkdays\Laravel\Rules\HungarianWorkday;
use IntrAgile\HungarianWorkdays\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class HungarianWorkdayRuleTest extends TestCase
{
    /**
     * @return array<string, array{string, string, string}>
     */
    public static function locales(): array
    {
        return [
            'hu' => ['hu', 'A(z) d mezőnek munkanapnak kell lennie.', 'A(z) d évére még nincs elérhető munkarend.'],
            'en' => ['en', 'The d must be a Hungarian working day.', 'No Hungarian work schedule is available yet for the year of d.'],
        ];
    }

    public function test_workday_passes(): void
    {
        foreach ([true, false] as $useString) {
            $this->assertFalse($this->make('2026-03-02', $useString)->fails());
            $this->assertFalse($this->make('2026-01-10', $useString)->fails());
            $this->assertFalse($this->make(new DateTimeImmutable('2026-03-02'), $useString)->fails());
        }
    }

    #[DataProvider('locales')]
    public function test_three_failure_branches(string $locale, string $workday, string $uncovered): void
    {
        app()->setLocale($locale);
        $notDate = trans('validation.date', ['attribute' => 'd']);
        $this->assertNotSame('validation.date', $notDate);

        foreach ([true, false] as $useString) {
            $this->assertSame([$workday], $this->make('2026-03-14', $useString)->errors()->get('d'), 'weekend');
            $this->assertSame([$workday], $this->make('2026-01-02', $useString)->errors()->get('d'), 'moved rest day');
            $this->assertSame([$uncovered], $this->make('2099-03-04', $useString)->errors()->get('d'), 'uncovered');
            $this->assertSame([$notDate], $this->make('not a date', $useString)->errors()->get('d'), 'not a date');
            $this->assertSame([$notDate], $this->make(['x'], $useString)->errors()->get('d'), 'array');
        }
    }

    public function test_string_rule_uses_application_override_message(): void
    {
        $validator = Validator::make(['d' => '2026-03-14'], ['d' => 'hungarian_workday'], ['hungarian_workday' => 'Custom :attribute']);

        $this->assertSame(['Custom d'], $validator->errors()->get('d'));
    }

    public function test_year_without_schedule_passes_when_transfers_are_assumed_absent(): void
    {
        config()->set('workdays.assume_no_transfers', true);

        $this->assertFalse($this->make('2099-03-04', false)->fails());
    }

    private function make(mixed $value, bool $useString): ValidatorContract
    {
        return Validator::make(['d' => $value], ['d' => $useString ? 'hungarian_workday' : [new HungarianWorkday]]);
    }
}
