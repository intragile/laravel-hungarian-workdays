# Laravel Hungarian Workdays

[![Tests](https://github.com/intragile/laravel-hungarian-workdays/actions/workflows/tests.yml/badge.svg)](https://github.com/intragile/laravel-hungarian-workdays/actions/workflows/tests.yml)
[![Latest Version](https://img.shields.io/packagist/v/intragile/laravel-hungarian-workdays.svg)](https://packagist.org/packages/intragile/laravel-hungarian-workdays)
[![PHP](https://img.shields.io/packagist/php-v/intragile/laravel-hungarian-workdays.svg)](https://packagist.org/packages/intragile/laravel-hungarian-workdays)
[![License](https://img.shields.io/packagist/l/intragile/laravel-hungarian-workdays.svg)](LICENSE)

The **Hungarian workday calendar**: statutory public holidays (munkaszüneti napok), the bridge
days and working Saturdays decreed each year (áthelyezett munkanapok, ledolgozós szombatok),
workday arithmetic, Carbon macros and a validation rule.

[Magyar nyelvű leírás → README.hu.md](README.hu.md)

- **Official data, not a scraped calendar.** The 2013–2026 schedules are copied from the
  decrees in the Nemzeti Jogszabálytár. Every year cites its decree, see
  [DATA-SOURCES.md](DATA-SOURCES.md).
- **Explicit coverage.** A year without a known schedule throws `UncoveredYearException`
  instead of silently ignoring bridge days. You can add a year yourself or opt in to a
  documented fallback.
- **Framework-independent core.** `Calendar` is plain PHP and needs nothing else. The Laravel
  layer adds a facade, Carbon macros, a validation rule and an Artisan command.
- **Date handling you can reason about.** A day is judged by its own `Y-m-d`; time and timezone
  are ignored. Results are `DateTimeImmutable` at 00:00 in the input's timezone, and inputs are
  never modified.

## What counts as a workday

"Workday" means a working day under the **general work schedule** (általános munkarend): Monday
to Friday, except public holidays and the rest days moved by the yearly decree, plus the
Saturdays worked in exchange. Workplaces with a different schedule (shift work, retail) are
out of scope.

Only the statutory holidays of Mt. 102. § (1) are listed. Easter Sunday and Whit Sunday are not,
as they are Sundays anyway.

**24 December becomes a public holiday from 2026.** Parliament adopted the amendment on
2026-11-03 (bill T/714); its promulgation in the Magyar Közlöny is **pending** as of
2026-11-05. The bundled 2026 data already reflects it: the decree's 2026-12-24 rest day and
2026-12-12 worked Saturday pair is removed, and `2026-12-24` is a public holiday. If the
promulgation fails or changes, this will be revisited, see [DATA-SOURCES.md](DATA-SOURCES.md).

## Requirements

- PHP 8.3+
- Laravel 12 or 13 for the Laravel layer (optional)

## Installation

```bash
composer require intragile/laravel-hungarian-workdays
```

The service provider and the `Workdays` facade are discovered automatically. To publish the
configuration or the validation messages:

```bash
php artisan vendor:publish --tag=workdays-config
php artisan vendor:publish --tag=workdays-lang
```

## Core usage (any PHP project)

The core has no dependencies:

```php
use IntrAgile\HungarianWorkdays\Calendar;

$calendar = new Calendar;

$calendar->isWorkday(new DateTimeImmutable('2026-03-16'));        // true
$calendar->isWorkday(new DateTimeImmutable('2026-01-02'));        // false, moved rest day
$calendar->isWorkday(new DateTimeImmutable('2026-01-10'));        // true, worked Saturday
$calendar->dayType(new DateTimeImmutable('2026-08-20'));          // DayType::PublicHoliday
$calendar->holiday(new DateTimeImmutable('2026-08-20'));          // Holiday::StStephensDay

$calendar->nextWorkday(new DateTimeImmutable('2026-03-13'));      // 2026-03-16 00:00
$calendar->previousWorkday(new DateTimeImmutable('2026-03-16'));  // 2026-03-13 00:00
$calendar->addWorkdays(new DateTimeImmutable('2026-03-13'), 5);   // 2026-03-20 00:00
$calendar->workdaysBetween($from, $to);                           // both ends included
$calendar->workdaysInMonth(2026, 3);                              // 22
$calendar->workingHoursInMonth(2026, 3, dailyHours: 8);           // 176
```

| Method | Returns |
|---|---|
| `dayType($date)` | a `DayType`: `Workday`, `Weekend`, `PublicHoliday`, `RestDay` (moved rest day), `TransferredWorkday` (worked Saturday) |
| `isWorkday($date)` | `true` for `Workday` and `TransferredWorkday` |
| `isPublicHoliday($date)`, `holiday($date)` | the statutory holiday check and the `Holiday` case (or `null`) |
| `holidays($year)` | the year's holidays, keyed by `Y-m-d` |
| `nextWorkday($date, inclusive: false)`, `previousWorkday(...)` | the first workday after (before) the day; `inclusive: true` accepts the day itself |
| `addWorkdays($date, $n)`, `subWorkdays($date, $n)` | moves by `$n` workdays; `0` returns the day itself at 00:00 |
| `workdaysBetween($from, $to)` | workdays from `$from` to `$to`, both included |
| `workdaysInMonth($year, $month)`, `workingHoursInMonth($year, $month, $dailyHours = 8)` | the month's workdays and hours |
| `isCovered($year)`, `coveredYears()` | schedule coverage, see below |

`Holiday` also offers `hungarianName()` and `englishName()`.

## Coverage and `UncoveredYearException`

The package bundles the schedules of **2013–2026**. 2017 and 2023 had no transfer decree; both
are bundled as covered, with no moved days.

Asking about a year without a schedule throws `UncoveredYearException` (its `year` property
carries the year), because a bridge day could otherwise be missed without notice. Years before
2013 are never supported. Calendar arithmetic that walks into an uncovered year throws as well.

```php
$calendar->isCovered(2027);   // false
$calendar->coveredYears();    // [2013, 2014, ..., 2026]
```

**2027 is not covered yet.** The 2027 decree was due by 2026-10-31 but had not been published
when this was written (2026-11-05). Until it is, any 2027 date throws. The Artisan command and
the CI job described below tell you when it is available.

### Adding a year yourself

In Laravel, add the year to the configuration (`config/workdays.php`). Take the dates from
the decree published in the Magyar Közlöny; the lists below are **empty placeholders**, not
real data:

```php
'schedules' => [
    2027 => [
        'source' => 'Title of the 2027 work schedule decree',          // fill in
        'url' => 'https://njt.jog.gov.hu/...',                        // fill in
        'rest_days' => [],   // weekdays that became rest days, e.g. '2027-05-14'
        'workdays' => [],    // Saturdays worked in exchange, e.g. '2027-05-22'
    ],
],
```

In plain PHP:

```php
use IntrAgile\HungarianWorkdays\Calendar;
use IntrAgile\HungarianWorkdays\Schedule;
use IntrAgile\HungarianWorkdays\ScheduleRepository;

$calendar = new Calendar(
    ScheduleRepository::bundled()->with(Schedule::fromArray(2027, [
        'source' => 'Title of the 2027 work schedule decree',
        'url' => 'https://njt.jog.gov.hu/...',
        'rest_days' => [],
        'workdays' => [],
    ])),
);
```

A configured (or `with()`) year replaces a bundled one of the same year. `Schedule::fromArray()`
validates the data and throws `InvalidScheduleException` for a malformed date, a worked day that
is not a Saturday, a rest day on a weekend, or a date that is already a public holiday.

### `assumingNoTransfers()`

As a stopgap you can tell the calendar to treat an uncovered year as having **no** moved rest
days and **no** worked Saturdays:

```php
$calendar = (new Calendar)->assumingNoTransfers();
```

In Laravel, set `'assume_no_transfers' => true` in `config/workdays.php`. Dates that loaded
schedules list still apply, and the statutory holidays always do.

**The risk:** Hungarian decrees regularly move a day for several bridge days a year. Under this
assumption such a rest day is reported as a workday, and the Saturday worked in exchange as a
weekend. Use it only until the decree is published, and not for payroll, deadlines or anything
legally binding. Years before 2013 still throw.

## Laravel

### Facade

```php
use IntrAgile\HungarianWorkdays\Laravel\Facades\Workdays;

Workdays::isWorkday(now());
Workdays::addWorkdays(now(), 8);
```

The facade proxies the `Calendar`, which is also resolvable from the container.

### Carbon macros

Registered on `Carbon` and `CarbonImmutable`:

```php
now()->isHungarianWorkday();
now()->isHungarianPublicHoliday();
now()->addHungarianWorkdays(5);
now()->subHungarianWorkdays(5);
now()->nextHungarianWorkday();                       // nextHungarianWorkday(inclusive: true)
now()->previousHungarianWorkday();
```

Date-returning macros give an instance of the receiver's own class at 00:00 in its timezone
and never modify the receiver.

The macros use the `Calendar` resolved when the application boots. In long-lived workers such
as Octane or queue workers, a change to `workdays.schedules` or `workdays.assume_no_transfers`
therefore needs a worker restart.

### Validation

```php
use IntrAgile\HungarianWorkdays\Laravel\Rules\HungarianWorkday;

$request->validate([
    'delivery_date' => ['required', 'hungarian_workday'],
    'due_date' => ['required', new HungarianWorkday],
]);
```

The value must be a `DateTimeInterface` or a string PHP can parse as a date. Three failure
messages exist, in English and Hungarian: not a date, not a working day, and no schedule
available for the year (the rule fails instead of throwing).

A message you define yourself (inline, or `validation.hungarian_workday` in your own language
files) **replaces all three** failure messages. To customise them separately, publish the
`workdays-lang` files and edit the `hungarian-workdays::validation` entries.

### Artisan command

```bash
php artisan workdays:coverage
```

Prints the covered years (for example `Covered years: 2013–2026`). From 31 October on it exits
with a failure and a warning when the schedule for the next year is missing, so a scheduler or
a deployment check can raise the alarm.

## Data sources

All data comes from primary legal sources (the Nemzeti Jogszabálytár and the Magyar Közlöny);
third-party calendar sites are never used. Decree titles, URLs and the open points (the
pending 24 December promulgation, the 2027 decree) are in [DATA-SOURCES.md](DATA-SOURCES.md).
Legislation is not subject to copyright in Hungary.

`bin/check-coverage.php` fails from 31 October when next year is not bundled. The repository's
weekly CI job runs it, so a missing decree surfaces without anyone having to remember.

## Related packages

- [intragile/laravel-hungarian-validation](https://github.com/intragile/laravel-hungarian-validation):
  tax number, IBAN, TAJ, company registration number and postal code validation.
- [intragile/laravel-nav-online-szamla](https://github.com/intragile/laravel-nav-online-szamla):
  NAV Online Számla client.

## Testing

```bash
composer test      # PHPUnit
composer analyse   # PHPStan, level max
composer format    # Pint
```

## Security

See [SECURITY.md](SECURITY.md).

## License

MIT, see [LICENSE](LICENSE).

---

Developed and maintained by [IntrAgile Kft.](https://intragile.eu).
