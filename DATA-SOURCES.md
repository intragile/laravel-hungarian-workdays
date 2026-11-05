# Data sources

All bundled data comes from primary legal sources: the official Nemzeti Jogszabálytár
(`njt.jog.gov.hu`, formerly `njt.hu`) and the Magyar Közlöny. Third-party calendar sites were
used only to locate decrees, never as a data source. Every pair below was copied from the
decree text fetched on 2026-11-05.

## Copyright status

Legislation is not protected by copyright in Hungary (Act LXXVI of 1999 on Copyright,
Szjt. 1. § (4): the law, state directives and similar official texts are not subject to
copyright). Dates taken from the decrees can therefore be redistributed freely.

## Statutory public holidays

- Labour Code (2012. évi I. törvény, Mt.) 102. § (1):
  https://njt.jog.gov.hu/jogszabaly/2012-1-00-00
- Easter Sunday and Whit Sunday are not listed in Mt. 102. § (1) and are not treated as
  holidays; they are Sundays anyway.
- Transfer rules, Mt. 102. § (5): the minister regulates transfers by decree by 31 October of
  the preceding year; a Sunday cannot become a working day and both days must fall in the same
  calendar month.

### Good Friday

2017. évi XIII. törvény (https://njt.jog.gov.hu/jogszabaly/2017-13-00-00), 4. §, replaced Mt.
102. § (1) to include Good Friday. Adopted 2017-03-07, promulgated 2017-03-16, in force
2017-03-24. First applied to Good Friday 2017-04-14, so the library treats Good Friday as a
holiday from 2017.

### 24 December (pending promulgation)

Bill T/714, "Egyes törvényeknek december 24-e munkaszüneti nappá nyilvánításával összefüggő
módosításáról", was adopted by Parliament on 2026-11-03 (193 yes, 0 no, 0 abstentions), source:
https://kormany.hu/hirek/munkaszuneti-nappa-nyilvanitotta-az-orszaggyules-december-24-et

**The act has NOT been promulgated as of 2026-11-05.** It has no law number yet, and the Magyar
Közlöny issues up to 165/2026 (2026-11-04) do not contain it. The Nemzeti Jogszabálytár still
shows Mt. 102. § (1) without 24 December. The exact statutory wording and entry into force could
not be verified (the bill PDF on parlament.hu is behind a CAPTCHA); the government's own
documents say it applies from 2026-12-24.

The library nevertheless treats 24 December as a public holiday from 2026, and the 2026
schedule omits the 24 December rest day / 12 December worked Saturday pair of the decree
(see 2026 below). Once the act is promulgated, this file should cite its number and Magyar
Közlöny issue. If promulgation fails or changes, the 2026 data and `Holiday::ChristmasEve`
must be revisited.

A draft amendment of 10/2025. (IV. 30.) NGM rendelet (repealing its 2. § c)) was in public
consultation until 2026-11-05:
https://kormany.hu/dokumentumtar/tarsadalmi-egyeztetes-a-2026-evi-munkaszuneti-napok-koruli-munkarendrol-szolo-10-2025-iv-30-ngm-rendelet-modositasarol

## Work schedule decrees (munkaszüneti napok körüli munkarend)

Rest day = moved rest day, worked = Saturday worked in exchange. No amendment has been
promulgated for any of the 2013-2025 decrees; the 2026 decree is unchanged on NJT (see above).

| Year | Decree | URL | Pairs (rest day / worked Saturday) |
|---|---|---|---|
| 2013 | 28/2012. (IX. 4.) NGM rendelet | https://njt.jog.gov.hu/jogszabaly/2012-28-20-2X | 2012-12-31 / 2012-12-01; 2013-08-19 / 2013-08-24; 2013-12-24 / 2013-12-07; 2013-12-27 / 2013-12-21 |
| 2014 | 33/2013. (VIII. 30.) NGM rendelet | https://njt.jog.gov.hu/jogszabaly/2013-33-20-2X | 05-02 / 05-10; 10-24 / 10-18; 12-24 / 12-13 |
| 2015 | 28/2014. (IX. 24.) NGM rendelet | https://njt.jog.gov.hu/jogszabaly/2014-28-20-2X | 01-02 / 01-10; 08-21 / 08-08; 12-24 / 12-12 |
| 2016 | 18/2015. (VI. 29.) NGM rendelet | https://njt.jog.gov.hu/jogszabaly/2015-18-20-2X | 03-14 / 03-05; 10-31 / 10-15 |
| 2017 | none issued | https://njt.jog.gov.hu/jogszabaly/2012-1-00-00 | none |
| 2018 | 9/2017. (V. 19.) NGM rendelet | https://njt.jog.gov.hu/jogszabaly/2017-9-20-2X | 03-16 / 03-10; 04-30 / 04-21; 10-22 / 10-13; 11-02 / 11-10; 12-24 / 12-01; 12-31 / 12-15 |
| 2019 | 6/2018. (VIII. 23.) PM rendelet | https://njt.jog.gov.hu/jogszabaly/2018-6-20-53 | 08-19 / 08-10; 12-24 / 12-07; 12-27 / 12-14 |
| 2020 | 7/2019. (VI. 25.) PM rendelet | https://njt.jog.gov.hu/jogszabaly/2019-7-20-53 | 08-21 / 08-29; 12-24 / 12-12 |
| 2021 | 14/2020. (V. 13.) ITM rendelet | https://njt.jog.gov.hu/jogszabaly/2020-14-20-7Q | 12-24 / 12-11 |
| 2022 | 23/2021. (VI. 1.) ITM rendelet | https://njt.jog.gov.hu/jogszabaly/2021-23-20-7Q | 03-14 / 03-26; 10-31 / 10-15 |
| 2023 | none issued | https://njt.jog.gov.hu/jogszabaly/2012-1-00-00 | none |
| 2024 | 15/2023. (VII. 13.) GFM rendelet | https://njt.jog.gov.hu/jogszabaly/2023-15-20-8P | 08-19 / 08-03; 12-24 / 12-07; 12-27 / 12-14 |
| 2025 | 11/2024. (IV. 8.) NGM rendelet | https://njt.jog.gov.hu/jogszabaly/2024-11-20-2X | 05-02 / 05-17; 10-24 / 10-18; 12-24 / 12-13 |
| 2026 | 10/2025. (IV. 30.) NGM rendelet | https://njt.jog.gov.hu/jogszabaly/2025-10-20-2X | 01-02 / 01-10; 08-21 / 08-08 (see below) |

Dates without a year belong to the decree year.

### Notes

- **2013:** the decree's pair 2012-12-31 / 2012-12-01 lies entirely in 2012. It is kept in the
  2013 schedule exactly as the decree lists it.
- **2017 and 2023:** no transfer decree was issued. This is an absence finding (the NGM, ITM and
  GFM decree series of the surrounding years on NJT were scanned and contain none), not a
  statement from a primary source. Both years are bundled as covered with empty lists. The
  calendar agrees: no bridge day exists in either year.
- **2026:** the decree lists three pairs. The third (rest day 2026-12-24 / worked Saturday
  2026-12-12) is omitted because of the Labour Code amendment adopted on 2026-11-03 (bill T/714,
  **promulgation pending**, see above); the other two pairs are unchanged.
- **2027:** no decree was found when checked on 2026-11-05 (statutory deadline 2026-10-31;
  NJT and Magyar Közlöny issues 157-165/2026 searched). 2027 is therefore not bundled. Dates
  of 2027 are outside the covered range until a decree is published.

## Not verified

- Magyar Közlöny PDFs were not cross-checked against the NJT texts of 2013-2026.
- The absence of 2017/2023 decrees is inferred from scanning, see above.
