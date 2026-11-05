<?php

declare(strict_types=1);

namespace IntrAgile\HungarianWorkdays\Laravel;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Validation\Factory;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Validator;
use IntrAgile\HungarianWorkdays\Calendar;
use IntrAgile\HungarianWorkdays\Laravel\Console\CoverageCommand;
use IntrAgile\HungarianWorkdays\Laravel\Rules\HungarianWorkday;
use IntrAgile\HungarianWorkdays\Schedule;
use IntrAgile\HungarianWorkdays\ScheduleRepository;

final class HungarianWorkdaysServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/workdays.php', 'workdays');

        // Resolved lazily so configuration set after boot is still honoured.
        $this->app->singleton(Calendar::class, static function (Application $app): Calendar {
            $config = $app->make(Repository::class);

            $schedules = [];
            $configured = $config->get('workdays.schedules', []);

            foreach (is_array($configured) ? $configured : [] as $year => $data) {
                $schedules[] = Schedule::fromArray((int) $year, is_array($data) ? $data : []);
            }

            return new Calendar(
                ScheduleRepository::bundled()->with(...$schedules),
                (bool) $config->get('workdays.assume_no_transfers', false),
            );
        });
    }

    public function boot(): void
    {
        $lang = __DIR__.'/../../lang';

        $this->loadTranslationsFrom($lang, HungarianWorkday::TRANSLATION_NAMESPACE);

        CarbonMacros::register($this->app);

        $this->callAfterResolving('validator', static function (Factory $factory): void {
            self::registerStringRule($factory);
        });

        if ($this->app->runningInConsole()) {
            $this->commands([CoverageCommand::class]);

            $this->publishes([__DIR__.'/../../config/workdays.php' => $this->app->configPath('workdays.php')], 'workdays-config');
            $this->publishes([$lang => $this->app->langPath('vendor/'.HungarianWorkday::TRANSLATION_NAMESPACE)], 'workdays-lang');
        }
    }

    /**
     * Registers `hungarian_workday`. The extension runs the rule object and remembers why it
     * failed; the replacer then swaps the placeholder message for the matching translation.
     * A message the application defines is never the placeholder and is kept as is.
     */
    private static function registerStringRule(Factory $factory): void
    {
        /** @var \WeakMap<Validator, string|null> $reasons */
        $reasons = new \WeakMap;
        $placeholder = HungarianWorkday::TRANSLATION_NAMESPACE.'::validation.hungarian_workday';

        $factory->extend(
            'hungarian_workday',
            static function (string $attribute, mixed $value, array $parameters, Validator $validator) use ($reasons): bool {
                $key = (new HungarianWorkday)->failureKey($value);
                $reasons[$validator] = $key;

                return $key === null;
            },
            $placeholder,
        );

        $factory->replacer(
            'hungarian_workday',
            static function (string $message, string $attribute, string $rule, array $parameters, Validator $validator) use ($reasons, $placeholder): string {
                if ($message !== $placeholder) {
                    return $message;
                }

                $translated = $validator->getTranslator()->get($reasons[$validator] ?? HungarianWorkday::NOT_A_WORKDAY);

                return $validator->makeReplacements(is_string($translated) ? $translated : $message, $attribute, $rule, $parameters);
            },
        );
    }
}
