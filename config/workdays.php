<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Additional work schedules
    |--------------------------------------------------------------------------
    |
    | Schedules of years the bundled data does not cover yet, or corrections of
    | a bundled year (a configured year replaces the bundled one). Keyed by the
    | decree year, with the same shape as the bundled schedules:
    |
    |   2027 => [
    |       'source' => 'Decree title',
    |       'url' => 'https://njt.jog.gov.hu/...',
    |       'rest_days' => ['2027-05-14'],   // weekdays that became rest days (Y-m-d)
    |       'workdays' => ['2027-05-22'],    // Saturdays worked in exchange (Y-m-d)
    |   ],
    |
    */

    'schedules' => [],

    /*
    |--------------------------------------------------------------------------
    | Assume no transfers
    |--------------------------------------------------------------------------
    |
    | When true, a year without a known schedule is treated as having no moved
    | rest days and no worked Saturdays, instead of throwing. Use it only as a
    | stopgap until the decree for the year is published.
    |
    */

    'assume_no_transfers' => false,

];
