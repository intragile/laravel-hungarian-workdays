<?php

declare(strict_types=1);

/*
 * Fails when the bundled work schedules do not yet cover next year although the
 * decree is due (the minister must publish it by 31 October of the preceding year).
 *
 * Usage: php bin/check-coverage.php [Y-m-d]   (the date defaults to today, UTC)
 */

$argument = $argv[1] ?? null;
$today = $argument !== null
    ? DateTimeImmutable::createFromFormat('!Y-m-d', $argument, new DateTimeZone('UTC'))
    : new DateTimeImmutable('today', new DateTimeZone('UTC'));

if ($today === false) {
    fwrite(STDERR, "Invalid date, expected Y-m-d.\n");

    exit(2);
}

/** @var array<int, mixed> $schedules */
$schedules = require __DIR__.'/../resources/schedules.php';

$year = (int) $today->format('Y');
$nextYear = $year + 1;
$month = (int) $today->format('n');
$day = (int) $today->format('j');
$due = $month > 10 || ($month === 10 && $day >= 31);

echo 'Bundled years: '.implode(', ', array_keys($schedules))."\n";

if ($due && ! array_key_exists($nextYear, $schedules)) {
    fwrite(STDERR, "No work schedule is bundled for {$nextYear}, but the decree was due by 31 October {$year}.\n");

    exit(1);
}

echo "OK: {$nextYear} is ".(array_key_exists($nextYear, $schedules) ? 'covered' : 'not due yet').".\n";
