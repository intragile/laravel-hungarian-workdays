# Changelog

Developed and maintained by [IntrAgile Kft.](https://intragile.eu)

All notable changes to this project are documented here. The format follows
[Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and the project adheres to
[Semantic Versioning](https://semver.org/).

## [1.0.0] - 2026-11-05

First public release.

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
  `coveredYears()`, and an opt-in `assumingNoTransfers()`. `holiday()`, `isPublicHoliday()` and
  `holidays()` throw `UncoveredYearException` for years before 2013.
- `ScheduleRepository` and `Schedule` for adding or correcting years in plain PHP. Schedule
  data uses the keys `rest_days` and `worked_saturdays`; dates must lie in the decree year or
  the year before it, and the configured `workdays.schedules` are validated.
- Laravel integration: service provider with auto-discovery, `Workdays` facade, the
  `workdays.schedules` and `workdays.assume_no_transfers` configuration, Carbon and
  CarbonImmutable macros, the `hungarian_workday` validation rule (rule object and string
  rule) with English and Hungarian messages, and the `workdays:coverage` Artisan command.
- `bin/check-coverage.php`, the `workdays:coverage` command and a separate weekly
  `schedule-coverage` workflow that fail when the current year is not covered, and from
  31 October when next year's schedule is missing.
- English and Hungarian documentation.

---

Developed and maintained by [IntrAgile Kft.](https://intragile.eu).
