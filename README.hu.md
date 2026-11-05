# Laravel Hungarian Workdays

[![Tests](https://github.com/intragile/laravel-hungarian-workdays/actions/workflows/tests.yml/badge.svg)](https://github.com/intragile/laravel-hungarian-workdays/actions/workflows/tests.yml)
[![Latest Version](https://img.shields.io/packagist/v/intragile/laravel-hungarian-workdays.svg)](https://packagist.org/packages/intragile/laravel-hungarian-workdays)
[![PHP](https://img.shields.io/packagist/php-v/intragile/laravel-hungarian-workdays.svg)](https://packagist.org/packages/intragile/laravel-hungarian-workdays)
[![License](https://img.shields.io/packagist/l/intragile/laravel-hungarian-workdays.svg)](LICENSE)

A **magyar munkanap-naptár**: munkaszüneti napok, az évente rendeletben áthelyezett munkanapok
és ledolgozós szombatok, munkanap-számítás, Carbon-makrók és egy validációs szabály.

[English documentation → README.md](README.md)

- **Hivatalos adat, nem kigyűjtött naptár.** A 2013–2026-os munkarendek a Nemzeti Jogszabálytár
  rendeleteiből származnak. Minden év megjelöli a rendeletét, lásd
  [DATA-SOURCES.md](DATA-SOURCES.md).
- **Kimondott lefedettség.** A munkarend nélküli évre `UncoveredYearException` a válasz ahelyett,
  hogy a csomag szó nélkül figyelmen kívül hagyná az áthelyezett napokat. Az évet felveheted
  magad, vagy bekapcsolhatsz egy dokumentált tartalékot.
- **Keretrendszer-független mag.** A `Calendar` tiszta PHP, nem kér semmit. A Laravel-réteg
  facade-ot, Carbon-makrókat, validációs szabályt és Artisan-parancsot ad hozzá.
- **Kiszámítható dátumkezelés.** A napot a bemenet saját `Y-m-d` értéke határozza meg, az időt és
  az időzónát figyelmen kívül hagyja. Az eredmény `DateTimeImmutable`, a bemenet időzónájában
  00:00-kor, a bemenetet a csomag sosem módosítja.

## Mi számít munkanapnak

A „munkanap” az **általános munkarend** szerinti munkanap: hétfőtől péntekig, kivéve a
munkaszüneti napokat és a rendelettel áthelyezett pihenőnapokat, továbbá a cserébe ledolgozott
szombatok. Más munkarendet (műszak, kereskedelem) a csomag nem kezel.

Csak az Mt. 102. § (1) bekezdésében felsorolt munkaszüneti napok szerepelnek. Húsvét- és
pünkösdvasárnap nincs köztük, mert amúgy is vasárnapra esnek.

**December 24. 2026-tól munkaszüneti nap.** Az Országgyűlés 2026. november 3-án fogadta el a
módosítást (T/714. számú törvényjavaslat). A Magyar Közlönyben való kihirdetése 2026. november
5-én még **függőben** van. A csomag 2026-os adata már ezt követi: a rendelet 2026. december 24-i
pihenőnap / 2026. december 12-i ledolgozós szombat párja kimaradt, és `2026-12-24` munkaszüneti
nap. Ha a kihirdetés elmarad vagy módosul, ezt felül kell vizsgálni, lásd
[DATA-SOURCES.md](DATA-SOURCES.md).

## Követelmények

- PHP 8.3+
- Laravel 12 vagy 13 a Laravel-réteghez (opcionális)

## Telepítés

```bash
composer require intragile/laravel-hungarian-workdays
```

A service provider és a `Workdays` facade automatikusan települ. A konfiguráció vagy az
üzenetek közzétételéhez:

```bash
php artisan vendor:publish --tag=workdays-config
php artisan vendor:publish --tag=workdays-lang
```

## Használat a magban (bármely PHP-projektben)

A mag függőség nélküli:

```php
use IntrAgile\HungarianWorkdays\Calendar;

$calendar = new Calendar;

$calendar->isWorkday(new DateTimeImmutable('2026-03-16'));        // true
$calendar->isWorkday(new DateTimeImmutable('2026-01-02'));        // false, áthelyezett pihenőnap
$calendar->isWorkday(new DateTimeImmutable('2026-01-10'));        // true, ledolgozós szombat
$calendar->dayType(new DateTimeImmutable('2026-08-20'));          // DayType::PublicHoliday
$calendar->holiday(new DateTimeImmutable('2026-08-20'));          // Holiday::StStephensDay

$calendar->nextWorkday(new DateTimeImmutable('2026-03-13'));      // 2026-03-16 00:00
$calendar->previousWorkday(new DateTimeImmutable('2026-03-16'));  // 2026-03-13 00:00
$calendar->addWorkdays(new DateTimeImmutable('2026-03-13'), 5);   // 2026-03-20 00:00
$calendar->workdaysBetween($from, $to);                           // mindkét végpont számít
$calendar->workdaysInMonth(2026, 3);                              // 22
$calendar->workingHoursInMonth(2026, 3, dailyHours: 8);           // 176
```

| Metódus | Visszatérés |
|---|---|
| `dayType($date)` | `DayType`: `Workday`, `Weekend`, `PublicHoliday`, `RestDay` (áthelyezett pihenőnap), `TransferredWorkday` (ledolgozós szombat) |
| `isWorkday($date)` | `true` a `Workday` és a `TransferredWorkday` esetén |
| `isPublicHoliday($date)`, `holiday($date)` | munkaszüneti nap vizsgálata és a `Holiday` érték (vagy `null`) |
| `holidays($year)` | az év munkaszüneti napjai `Y-m-d` kulcsokkal |
| `nextWorkday($date, inclusive: false)`, `previousWorkday(...)` | a nap utáni (előtti) első munkanap; `inclusive: true` esetén maga a nap is jó |
| `addWorkdays($date, $n)`, `subWorkdays($date, $n)` | `$n` munkanappal arrébb lép; `0` esetén a napot adja vissza 00:00-kor |
| `workdaysBetween($from, $to)` | a munkanapok száma `$from`-tól `$to`-ig, mindkettőt beleértve |
| `workdaysInMonth($year, $month)`, `workingHoursInMonth($year, $month, $dailyHours = 8)` | a hónap munkanapjai és munkaórái |
| `isCovered($year)`, `coveredYears()` | munkarend-lefedettség, lásd lent |

A `Holiday` ezenfelül `hungarianName()` és `englishName()` metódust kínál.

A `Holiday::dateIn($year)` éjfélt ad **UTC**-ben, míg a `Calendar` eredményei a bemenet
időzónáját őrzik. A dátumokat `format('Y-m-d')` alapján hasonlítsd össze, ne az objektumokon
végzett `==`-vel. A `Holiday::forYear()` és a `dateIn()` tartományra engedékeny segédek: 2013
előtti évre `[]`-t vagy `null`-t adnak, ami azt jelenti, hogy "a támogatott tartományon kívül",
nem azt, hogy "nem ünnepnap". Tartományellenőrzött választ a `Calendar` metódusai adnak.

## Lefedettség és `UncoveredYearException`

A csomag a **2013–2026-os** munkarendeket tartalmazza. 2017-ben és 2023-ban nem volt áthelyezési
rendelet, ezek üres listával, lefedettként szerepelnek.

A munkarend nélküli évre kérdezve `UncoveredYearException` keletkezik (az `year` tulajdonsága
az évet adja), mert különben egy áthelyezett nap észrevétlenül kimaradhatna. A 2013 előtti évek
sosem támogatottak: minden `Calendar`-lekérdezés kivételt dob rájuk, a `holiday()`,
`isPublicHoliday()` és `holidays()` is, és a kivétel üzenete jelzi, hogy az év a támogatott
tartományon kívül esik. A lefedetlen évbe átlépő számítás is kivételt dob.

Az ünnepnapi szabályokhoz nem kell munkarend, ezért ez a három metódus 2013-tól bármely évre
válaszol, lefedett-e vagy sem.

```php
$calendar->isCovered(2027);   // false
$calendar->coveredYears();    // [2013, 2014, ..., 2026]
```

**2027 még nincs lefedve.** A 2027-es rendelet határideje 2026. október 31. volt, de az írás
idején (2026. november 5.) még nem jelent meg. Amíg meg nem jelenik, minden 2027-es dátum
kivételt dob. Az alábbi Artisan-parancs és CI-feladat figyelmeztet, amíg hiányzik.

### Saját év felvétele

Laravelben vedd fel az évet a konfigurációban (`config/workdays.php`). A dátumokat a Magyar
Közlönyben megjelent rendeletből vedd; az alábbi listák **üres helyőrzők**, nem valódi adatok:

```php
'schedules' => [
    2027 => [
        'source' => 'A 2027. évi munkarendi rendelet címe',            // töltsd ki
        'url' => 'https://njt.jog.gov.hu/...',                        // töltsd ki
        'rest_days' => [],          // pihenőnappá vált hétköznapok, pl. '2027-05-14'
        'worked_saturdays' => [], // cserébe ledolgozott szombatok, pl. '2027-05-22'
    ],
],
```

Sima PHP-ban:

```php
use IntrAgile\HungarianWorkdays\Calendar;
use IntrAgile\HungarianWorkdays\Schedule;
use IntrAgile\HungarianWorkdays\ScheduleRepository;

$calendar = new Calendar(
    ScheduleRepository::bundled()->with(Schedule::fromArray(2027, [
        'source' => 'A 2027. évi munkarendi rendelet címe',
        'url' => 'https://njt.jog.gov.hu/...',
        'rest_days' => [],
        'worked_saturdays' => [],
    ])),
);
```

A konfigurált (vagy `with()`-tel megadott) év felülírja az azonos évű beépítettet. A
`Schedule::fromArray()` ellenőrzi az adatot, és `InvalidScheduleException`-t dob 2013 előtti évre, hibás dátumra,
a rendelet évén és az azt megelőző éven kívüli dátumra, nem szombatra eső ledolgozós napra,
hétvégére eső pihenőnapra, vagy olyan dátumra, amely már munkaszüneti nap. A Laravel-konfigurációt
ugyanígy ellenőrzi, és a kulcsoknak éveknek kell lenniük.

### `assumingNoTransfers()`

Átmeneti megoldásként megmondhatod a naptárnak, hogy a lefedetlen évben **nincs** áthelyezett
pihenőnap és **nincs** ledolgozós szombat:

```php
$calendar = (new Calendar)->assumingNoTransfers();
```

Laravelben állítsd be a `'assume_no_transfers' => true` értéket a `config/workdays.php`-ban. A
betöltött munkarendekben szereplő dátumok továbbra is érvényesek, a munkaszüneti napok pedig
mindig.

**A kockázat:** a rendeletek évente többször is áthelyeznek egy napot. Ezzel a feltevéssel az
ilyen pihenőnapot munkanapnak, a cserébe ledolgozott szombatot hétvégének jelzi a naptár. Csak a
rendelet megjelenéséig használd, bérszámfejtéshez, határidőkhöz és jogilag kötelező számításhoz
ne. A 2013 előtti évek ekkor is kivételt dobnak.

## Laravel

### Facade

```php
use IntrAgile\HungarianWorkdays\Laravel\Facades\Workdays;

Workdays::isWorkday(now());
Workdays::addWorkdays(now(), 8);
```

A facade a `Calendar`-t továbbítja, amely a service containerből is kérhető.

### Carbon-makrók

A `Carbon` és a `CarbonImmutable` osztályon regisztrálva:

```php
now()->isHungarianWorkday();
now()->isHungarianPublicHoliday();
now()->addHungarianWorkdays(5);
now()->subHungarianWorkdays(5);
now()->nextHungarianWorkday();                       // nextHungarianWorkday(inclusive: true)
now()->previousHungarianWorkday();
```

A dátumot visszaadó makrók a címzett saját osztályának példányát adják, a címzett időzónájában
00:00-kor, és a címzettet nem módosítják.

A `Calendar` konténer-singleton, az első használatkor épül fel az akkori konfigurációból. A
makrók minden híváskor az induláskor rögzített konténerből kérik le, így ugyanazt a példányt
használják. Hosszú életű workerekben (Octane, queue worker) a `workdays.schedules` vagy a
`workdays.assume_no_transfers` első használat utáni módosítása ezért worker-újraindítást igényel.

### Validáció

```php
use IntrAgile\HungarianWorkdays\Laravel\Rules\HungarianWorkday;

$request->validate([
    'delivery_date' => ['required', 'hungarian_workday'],
    'due_date' => ['required', new HungarianWorkday],
]);
```

Az érték `DateTimeInterface` vagy olyan szöveg lehet, amelyet a PHP dátumként értelmez. Három
hibaüzenet van, magyarul és angolul: nem dátum, nem munkanap, és nincs elérhető munkarend az
évhez (ilyenkor a szabály megbukik, nem dob kivételt).

Az üzenet testreszabása attól függ, hogyan használod a szabályt:

- **Szöveges szabály** (`'hungarian_workday'`): az általad definiált üzenet, inline vagy
  `validation.hungarian_workday` a saját nyelvi fájljaidban, **mindhárom** hibaüzenetet felülírja.
- **Szabályobjektum** (`new HungarianWorkday`): a Laravel az egyedi üzeneteket mező és
  szabályosztály szerint keresi, ezért a nyelvi fájlok `validation.hungarian_workday` sora
  **nem** érvényes. Az üzenetet a validatornak add át `'due_date.hungarian_workday'` (vagy
  `'due_date.'.HungarianWorkday::class`, esetleg a puszta osztálynév) kulccsal, például a
  `FormRequest::messages()`-ből. Ez is mindhárom üzenetet felülírja az adott mezőre.

A három üzenet külön testreszabásához mindkét formában tedd közzé a `workdays-lang` fájlokat, és
szerkeszd a `hungarian-workdays::validation` bejegyzéseket.

### Artisan-parancs

```bash
php artisan workdays:coverage
```

Kiírja a lefedett éveket (például `Covered years: 2013–2026`). Hibával és
figyelmeztetéssel tér vissza, ha az aktuális év nincs lefedve, október 31-től pedig akkor is, ha
a következő év munkarendje hiányzik, így egy ütemező vagy egy telepítési ellenőrzés riaszthat.

## Adatforrások

Minden adat elsődleges jogforrásból származik (Nemzeti Jogszabálytár, Magyar Közlöny),
harmadik fél naptáraiból soha. A rendeletek címe, URL-je és a nyitott pontok (a december 24.
kihirdetése és a 2027-es rendelet) a [DATA-SOURCES.md](DATA-SOURCES.md) fájlban találhatók. A
jogszabályok Magyarországon nem esnek szerzői jogi védelem alá.

A `bin/check-coverage.php` hibával tér vissza, ha az aktuális év nincs beépítve, október 31-től
pedig akkor is, ha a következő év hiányzik. A tárhely különálló `schedule-coverage` workflow-ja
hetente futtatja, így a hiányzó rendelet magától kiderül, a tesztjelvényt pedig nem teszi pirossá.

## Kapcsolódó csomagok

- [intragile/laravel-hungarian-validation](https://github.com/intragile/laravel-hungarian-validation):
  adószám, IBAN, TAJ-szám, cégjegyzékszám és irányítószám validációja.
- [intragile/laravel-nav-online-szamla](https://github.com/intragile/laravel-nav-online-szamla):
  NAV Online Számla kliens.

## Tesztelés

```bash
composer test      # PHPUnit
composer analyse   # PHPStan, level max
composer format    # Pint
```

## Biztonság

Lásd: [SECURITY.md](SECURITY.md).

## Licenc

MIT, lásd: [LICENSE](LICENSE).

---

Fejleszti és karbantartja az [IntrAgile Kft.](https://intragile.eu).
