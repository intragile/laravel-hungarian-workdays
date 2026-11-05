# Changelog

All notable changes to this project are documented here. The format follows
[Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and the project adheres to
[Semantic Versioning](https://semver.org/).

## [Unreleased]

### Added

- `Calendar` for classifying days under the Hungarian general work schedule: public holiday,
  moved rest day, worked Saturday (transferred workday), weekend or workday.
- Statutory public holidays computed per year, including Good Friday (from 2017) and
  24 December (from 2026).
- Bundled work schedules for 2013-2026 taken from the official decrees, with the sources
  listed in `DATA-SOURCES.md`.
- Workday arithmetic: `nextWorkday()`, `previousWorkday()`, `addWorkdays()`, `subWorkdays()`,
  `workdaysBetween()`, `workdaysInMonth()` and `workingHoursInMonth()`.
- Explicit coverage: `UncoveredYearException` for years without a schedule, `isCovered()`,
  `coveredYears()`, and an opt-in `assumingNoTransfers()`.
- `ScheduleRepository` and `Schedule` for adding or correcting years in plain PHP.
- Laravel integration: service provider with auto-discovery, `Workdays` facade, the
  `workdays.schedules` and `workdays.assume_no_transfers` configuration, Carbon and
  CarbonImmutable macros, the `hungarian_workday` validation rule (rule object and string
  rule) with English and Hungarian messages, and the `workdays:coverage` Artisan command.
- `bin/check-coverage.php` and a weekly CI job that fails from 31 October when next year's
  schedule is not bundled yet.
- English and Hungarian documentation.
