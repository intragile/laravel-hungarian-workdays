<?php

declare(strict_types=1);

/*
 * Work schedules around public holidays (munkaszüneti napok körüli munkarend), keyed by
 * the year the decree applies to. Every entry is taken from the decree text published in
 * the Nemzeti Jogszabálytár; see DATA-SOURCES.md.
 *
 * rest_days: weekdays that became rest days. workdays: Saturdays worked in exchange.
 * Both lists are Y-m-d and sorted ascending.
 *
 * @return array<int, array{source: string, url: string, rest_days: list<string>, workdays: list<string>}>
 */

$noDecree = 'No transfer decree was issued for this year.';
$labourCode = 'https://njt.jog.gov.hu/jogszabaly/2012-1-00-00';

return [
    2013 => [
        'source' => '28/2012. (IX. 4.) NGM rendelet a 2013. évi munkaszüneti napok körüli munkarendről',
        'url' => 'https://njt.jog.gov.hu/jogszabaly/2012-28-20-2X',
        'rest_days' => ['2012-12-31', '2013-08-19', '2013-12-24', '2013-12-27'],
        'workdays' => ['2012-12-01', '2013-08-24', '2013-12-07', '2013-12-21'],
    ],
    2014 => [
        'source' => '33/2013. (VIII. 30.) NGM rendelet a 2014. évi munkaszüneti napok körüli munkarendről',
        'url' => 'https://njt.jog.gov.hu/jogszabaly/2013-33-20-2X',
        'rest_days' => ['2014-05-02', '2014-10-24', '2014-12-24'],
        'workdays' => ['2014-05-10', '2014-10-18', '2014-12-13'],
    ],
    2015 => [
        'source' => '28/2014. (IX. 24.) NGM rendelet a 2015. évi munkaszüneti napok körüli munkarendről',
        'url' => 'https://njt.jog.gov.hu/jogszabaly/2014-28-20-2X',
        'rest_days' => ['2015-01-02', '2015-08-21', '2015-12-24'],
        'workdays' => ['2015-01-10', '2015-08-08', '2015-12-12'],
    ],
    2016 => [
        'source' => '18/2015. (VI. 29.) NGM rendelet a 2016. évi munkaszüneti napok körüli munkarendről',
        'url' => 'https://njt.jog.gov.hu/jogszabaly/2015-18-20-2X',
        'rest_days' => ['2016-03-14', '2016-10-31'],
        'workdays' => ['2016-03-05', '2016-10-15'],
    ],
    2017 => [
        'source' => $noDecree,
        'url' => $labourCode,
        'rest_days' => [],
        'workdays' => [],
    ],
    2018 => [
        'source' => '9/2017. (V. 19.) NGM rendelet a 2018. évi munkaszüneti napok körüli munkarendről',
        'url' => 'https://njt.jog.gov.hu/jogszabaly/2017-9-20-2X',
        'rest_days' => ['2018-03-16', '2018-04-30', '2018-10-22', '2018-11-02', '2018-12-24', '2018-12-31'],
        'workdays' => ['2018-03-10', '2018-04-21', '2018-10-13', '2018-11-10', '2018-12-01', '2018-12-15'],
    ],
    2019 => [
        'source' => '6/2018. (VIII. 23.) PM rendelet a 2019. évi munkaszüneti napok körüli munkarendről',
        'url' => 'https://njt.jog.gov.hu/jogszabaly/2018-6-20-53',
        'rest_days' => ['2019-08-19', '2019-12-24', '2019-12-27'],
        'workdays' => ['2019-08-10', '2019-12-07', '2019-12-14'],
    ],
    2020 => [
        'source' => '7/2019. (VI. 25.) PM rendelet a 2020. évi munkaszüneti napok körüli munkarendről',
        'url' => 'https://njt.jog.gov.hu/jogszabaly/2019-7-20-53',
        'rest_days' => ['2020-08-21', '2020-12-24'],
        'workdays' => ['2020-08-29', '2020-12-12'],
    ],
    2021 => [
        'source' => '14/2020. (V. 13.) ITM rendelet a 2021. évi munkaszüneti napok körüli munkarendről',
        'url' => 'https://njt.jog.gov.hu/jogszabaly/2020-14-20-7Q',
        'rest_days' => ['2021-12-24'],
        'workdays' => ['2021-12-11'],
    ],
    2022 => [
        'source' => '23/2021. (VI. 1.) ITM rendelet a 2022. évi munkaszüneti napok körüli munkarendről',
        'url' => 'https://njt.jog.gov.hu/jogszabaly/2021-23-20-7Q',
        'rest_days' => ['2022-03-14', '2022-10-31'],
        'workdays' => ['2022-03-26', '2022-10-15'],
    ],
    2023 => [
        'source' => $noDecree,
        'url' => $labourCode,
        'rest_days' => [],
        'workdays' => [],
    ],
    2024 => [
        'source' => '15/2023. (VII. 13.) GFM rendelet a 2024. évi munkaszüneti napok körüli munkarendről',
        'url' => 'https://njt.jog.gov.hu/jogszabaly/2023-15-20-8P',
        'rest_days' => ['2024-08-19', '2024-12-24', '2024-12-27'],
        'workdays' => ['2024-08-03', '2024-12-07', '2024-12-14'],
    ],
    2025 => [
        'source' => '11/2024. (IV. 8.) NGM rendelet a 2025. évi munkaszüneti napok körüli munkarendről',
        'url' => 'https://njt.jog.gov.hu/jogszabaly/2024-11-20-2X',
        'rest_days' => ['2025-05-02', '2025-10-24', '2025-12-24'],
        'workdays' => ['2025-05-17', '2025-10-18', '2025-12-13'],
    ],
    2026 => [
        'source' => '10/2025. (IV. 30.) NGM rendelet a 2026. évi munkaszüneti napok körüli munkarendről, '
            .'without its 24 December rest day / 12 December worked Saturday pair: 24 December is a public holiday under the '
            .'Labour Code amendment adopted by Parliament on 2026-11-03 (bill T/714, not yet promulgated).',
        'url' => 'https://njt.jog.gov.hu/jogszabaly/2025-10-20-2X',
        'rest_days' => ['2026-01-02', '2026-08-21'],
        'workdays' => ['2026-01-10', '2026-08-08'],
    ],
];
