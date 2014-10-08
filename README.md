Period
============

[![Author](http://img.shields.io/badge/author-@nyamsprod-blue.svg?style=flat-square)](https://twitter.com/nyamsprod)
[![Latest Version](https://img.shields.io/github/release/nyamsprod/Period.svg?style=flat-square)](https://github.com/nyamsprod/Period/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)<br>
[![Build Status](https://img.shields.io/travis/nyamsprod/Period/master.svg?style=flat-square)](https://travis-ci.org/nyamsprod/Period)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/nyamsprod/Period.svg?style=flat-square)](https://scrutinizer-ci.com/g/nyamsprod/Period/code-structure)
[![Quality Score](https://img.shields.io/scrutinizer/g/nyamsprod/Period.svg?style=flat-square)](https://scrutinizer-ci.com/g/nyamsprod/Period)
[![Total Downloads](https://img.shields.io/packagist/dt/bakame/period.svg?style=flat-square)](https://packagist.org/packages/bakame/period)

This class is based on [Resolving Feature Envy in the Domain](http://verraes.net/2014/08/resolving-feature-envy-in-the-domain/) by Mathias Verraes and helps resolve many recurrent issues around Date range selection and usage.

This package is compliant with [PSR-2], and [PSR-4].

[PSR-2]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md
[PSR-4]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md

System Requirements
-------

You need **PHP >= 5.3.0** to use `Period` but the latest stable version of PHP is recommended.

Install
-------

Install `Period` using Composer.

```json
{
    "require": {
        "bakame/period": "1.*"
    }
}
```
#### Going Solo

You can also use `Period` without using Composer by downloading the library and registing an autoloader function:

```php
spl_autoload_register(function ($class) {
    $prefix = 'Period\\';
    $base_dir = __DIR__ . '/src/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // no, move to the next registered autoloader
        return;
    }
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});
```

Or, use any other [PSR-4](http://www.php-fig.org/psr/psr-4/) compatible autoloader.

Definitions
-------

- **endpoint** - A period consists of a continuous portion of time between two positions in time called endpoints. This library assumes that the starting endpoint is included into the period. Conversely, the ending endpoint is excluded from the specified period. The endpoints are defined as DateTime objects.

- **duration** - The continuous portion of time between endpoints is called the duration. This duration is defined as a DateInterval object.

Arguments
-------

Unless stated otherwise, whenever a `DateTime` object is expected you can provide:

- a `DateTime` object;
- a string parsable by the `DateTime` constructor.

Unless stated otherwise, whenever a `DateInterval` object is expected you can provide:

- a `DateInterval` object;
- a string parsable by the `DateInterval::createFromDateString` method.
- an integer interpreted as the duration expressed in seconds.

Instantiation
-------

#### Period::__construct($start, $end)

Both `$start` and `$end` parameters represent the period endpoints as `DateTime` objects. 

- The `$start` endpoint represents **the starting included endpoint**.
- The `$end` value represents **the ending excluded endpoint**. 

`$end` **must be** greater or equal to `$start` or the instantiation will failed. 

```php
use Period\Period;

$period = new Period('2012-04-01 08:30:25', new DateTime('2013-09-04 12:35:21'));
```

To ease instantiation the class comes with many named constructors:

#### Period::createFromDuration($start, $duration)

returns a `Period` object which starts at `$start` with a duration equals to `$duration`

- The `$start` represents **the starting included endpoint** expressed as `DateTime` object.
- The `$duration` parameter is a `DateInterval` object;

```php
use Period\Period;

$period = Period::createFromDuration('2012-04-01 08:30:25', '1 DAY');
$alt    = Period::createFromDuration('2012-04-01 08:30:25', new DateInterval('P1D'));
$other  = Period::createFromDuration(new DateTime('2012-04-01 08:30:25'), 86400);
```
#### Period::createFromWeek($year, $week)

returns a `Period` object with a duration of 1 week for a given year and week.

- The `$year` parameter is a valid year;
- The `$week` parameter is a selected week (between 1 and 53);

```php
use Period\Period;

$period  = Period::createFromWeek(2013, 23);
//this period represents the 23rd week of 2013
```

#### Period::createFromMonth($year, $month)

returns a `Period` object with a duration of 1 month for a given year and month. 

- The `$year` parameter is a valid year;
- The `$month` parameter is a selected month (between 1 and 12);

```php
use Period\Period;

$period  = Period::createFromMonth(2013, 7);
//this period represents the month of July 2013
```

#### Period::createFromQuarter($year, $quarter)

returns a `Period` object with a duration of 3 months for a given year and quarter. 

- The `$year` parameter is a valid year;
- The `$quarter` parameter is a selected quarter (between 1 and 4);

```php
use Period\Period;

$period  = Period::createFromQuarter(2013, 2);
//this period represents the second quarter of 2013
```

#### Period::createFromSemester($year, $semester)

returns a `Period` object with a duration of 6 months for a given year and semester. 

- The `$year` parameter is a valid year;
- The `$semester` parameter is a selected semester (between 1 and 2);

```php
use Period\Period;

$period  = Period::createFromSemester(2011, 1);
//this period represents the first semester of 2013
```

#### Period::createFromYear($year)

returns a `Period` object with a duration of 1 year for a given year.

- The `$year` parameter is a valid year;

```php
use Period\Period;

$period  = Period::createFromYear(1971);
//this period represents the year 1971
```

Accessing properties
-------

Once you have a instantiated `Period` object you can access its properties using getter methods:

#### Period::getStart()

Returns the starting **included** endpoint as a `DateTime`.

#### Period::getEnd();

Returns the ending **excluded** endpoint as a `DateTime`.

#### Period::getDuration()

Returns the period duration as a `DateInterval` object.

#### Period::getRange($interval)

Returns a `DatePeriod` object that lists `DateTime` objects inside the period separated by the given `$interval` expressed as a `DateInterval` object.

```php
use Period\Period;

$period  = Period::createFromYear(1971);
foreach ($period->getRange('1 MONTH') as $datetime) {
    echo $datetime->format('Y-m-d H:i:s');
}
//will iterate 12 times
```

#### Period::contains($datetime)

Tells whether a `$datetime` is contained within the `Period` or not.

```php
use Period\Period;

$period = Period::createFromMonth(1983, 4);
$period->getStart(); //returns DateTime('1983-04-01');
$period->getEnd(); //returns DateTime('1983-05-01');
$period->contains('1983-04-15'); //returns true;
$period->contains($period->getEnd()); //returns false because of `getEnd` definition;
```
 
Comparing Period objects
-------

#### Period::sameValueAs(Period $period)

Tells whether two `Period` objects shares the same endpoints.

```php
use Period\Period;

$orig  = Period::createFromMonth(2014, 3);
$other = Period::createFromMonth(2014, 4);
$alt   = Period::createFromDuration('2014-03-01', '1 MONTH');

$orig->sameValueAs($other); //return false
$orig->sameValueAs($alt); //return true
```

#### Period::overlaps(Period $period)

Tells whether two `Period` objects overlap each other or not.

```php
use Period\Period;

$period1 = Period::createFromMonth(2014, 3);
$period2 = Period::createFromMonth(2014, 4);
$period3 = Period::createFromDuration('2014-03-15', '3 WEEKS');

$period1->overlaps($period2); //return false
$period1->overlaps($period3); //return true
$period2->overlaps($period3); //return true
```

#### Period::compareDuration(Period $period)

Compare two `Period` objects according to their duration.

- Return `1` if the current object duration is greater than the submitted `$period` duration;
- Return `-1` if the current object duration is less than the submitted `$period` duration;
- Return `0` if the current object duration is equal to the submitted `$period` duration;

To ease the method usage you can rely on the following aliases methods that only return boolean values:

- **`Period::durationGreaterThan(Period $period)`** return `true` when `Period::compareDuration(Period $period)` returns `1`;
- **`Period::durationLessThan(Period $period)`** return `true` when `Period::compareDuration(Period $period)` returns `-1`;
- **`Period::sameDurationAs(Period $period)`** return `true` when `Period::compareDuration(Period $period)` returns `0`;

```php
$orig  = Period::createFromDuration('2012-01-01', '1 MONTH');
$alt   = Period::createFromDuration('2012-01-01', '1 WEEK');
$other = Period::createFromDuration('2013-01-01', '1 MONTH');

$orig->compareDuration($alt);     //return 1
$orig->durationGreaterThan($alt); //return true
$orig->durationLessThan($alt);    //return false

$alt->compareDuration($other);     //return -1
$alt->durationLessThan($other);    //return true
$alt->durationGreaterThan($other); //return false

$orig->compareDuration($other);   //return 0
$orig->sameDurationAs($other);    //return true
$orig->sameValueAs($other);       //return false
//the duration between $orig and $other are equals but not the endpoints!!
```
 
Period as a Immutable Value Object
-------

The `Period` object is an Immutable Value Object so any change to its property returns a new `Period` class. 

#### Period::startingOn($start)

Returns a new `Period` object with `$start` as the new **starting included endpoint** defined as a `DateTime` object.

```php
use Period\Period;

$period    = Period::createFromMonth(2014, 3);
$newPeriod = $period->startingOn('2014-02-01');
$period->getStart(); //returns DateTime('2014-03-01');
$newPeriod->getStart(); //returns DateTime('2014-02-01');
// $period->getEnd() equals $newPeriod->getEnd();
```

#### Period::endingOn($end)

Returns a new `Period` object with `$end` as the new **ending excluded endpoint** defined as a `DateTime` object.

```php
use Period\Period;

$period    = Period::createFromMonth(2014, 3);
$newPeriod = $period->EndingOn('2014-03-16');
$period->getEnd(); //returns DateTime('2014-04-01');
$newPeriod->getEnd(); //returns DateTime('2014-03-16');
// $period->getStart() equals $newPeriod->getStart();
```

#### Period::withDuration($duration)

Returns a new `Period` object by updating its duration. Only the excluded endpoint is updated.

The `$duration` parameter is expressed as a `DateInterval` object.

```php
use Period\Period;

$period    = Period::createFromMonth(2014, 3);
$newPeriod = $period->withDuration('2 WEEKS');
$period->getEnd(); //returns DateTime('2014-04-01');
$newPeriod->getEnd(); //returns DateTime('2014-03-16');
// $period->getStart() equals $newPeriod->getStart();
```

#### Period::merge(Period $period)

Merge two `Period` objects by returning a new `Period` object which starting endpoint is the smallest and the excluded endpoint is the biggest between both objects.

```php

use Period\Period;

$period    = Period::createFromSemester(2012, 1);
$altPeriod = Period::createFromWeek(2013, 4);
$newPeriod = $period->merge($altPeriod); 
// $newPeriod->getStart() equals $period->getStart();
// $newPeriod->getEnd() equals $altPeriod->getEnd();
```

Testing
-------

``` bash
$ phpunit
```

Contributing
-------

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

Credits
-------

- [ignace nyamagana butera](https://github.com/nyamsprod)
- [All Contributors](https://github.com/nyamsprod/Bakame.Tools/graphs/contributors)