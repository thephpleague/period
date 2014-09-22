Period
============

This class is based on [Resolving Feature Envy in the Domain](http://verraes.net/2014/08/resolving-feature-envy-in-the-domain/) by Matthias Verraes and helps resolve many recurrent issues around Date range selection and usage.

[![Latest Version](https://img.shields.io/github/release/nyamsprod/Period.svg?style=flat-square)](https://github.com/nyamsprod/Period/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/nyamsprod/Period/master.svg?style=flat-square)](https://travis-ci.org/nyamsprod/Period)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/nyamsprod/Period.svg?style=flat-square)](https://scrutinizer-ci.com/g/nyamsprod/Period/code-structure)
[![Quality Score](https://img.shields.io/scrutinizer/g/nyamsprod/Period.svg?style=flat-square)](https://scrutinizer-ci.com/g/nyamsprod/Period)
[![Total Downloads](https://img.shields.io/packagist/dt/bakame/period.svg?style=flat-square)](https://packagist.org/packages/bakame/period)


This package is compliant with [PSR-2], and [PSR-4].

[PSR-2]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md
[PSR-4]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md

System Requirements
-------

You need **PHP >= 5.3.0** to use `Bakame\Period` but the latest stable version of PHP is recommended.

Install
-------

Install `Period` using Composer.

```json
{
    "require": {
        "bakame/period": "*"
    }
}
```
#### Going Solo

You can also use `Bakame\Period` without using Composer by downloading the library and registing an autoloader function:

```php
spl_autoload_register(function ($class) {
    $prefix = 'Bakame\\Period\\';
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

Instantiation
-------

#### Period::__construct($datetime, $interval)

You can instantiate a period which starts at `$datetime` with a duration equals to `$interval`

- The `$datetime` parameter is a `DateTime` object or a string parsable by the `DateTime` constructor;
- The `$interval` parameter is a `DateInterval` or a string parsable by the `DateInterval::createFromDateString` method.

But this class comes with many named constructors to ease its instantiation:

#### Period::createFromWeek($year, $week)

returns a `Period` object with a duration of 1 week for a given year and week. 
The `$week` parameter is a selected week (between 1 to 53);

#### Period::createFromMonth($year, $month)

returns a `Period` object with a duration of 1 month for a given year and month. 
The `$month` parameter is a selected month (between 1 to 12);

#### Period::createFromQuarter($year, $quarter)

returns a `Period` object with a duration of 3 months for a given year and quarter. 
The `$quarter` parameter is a selected quarter (between 1 to 4);

#### Period::createFromSemester($year, $semester)

returns a `Period` object with a duration of 6 months for a given year and semester. 
The `$semester` parameter is a selected semester (between 1 and 2);

#### Period::createFromYear($year)

returns a `Period` object with a duration of 1 year for a given year. 

Usage
-------

The `Period` class is a Immutable Value Object so any change to its property returns a new `Period` class.

#### getStart()

returns the starting `DateTime`;

#### setStart($datetime)

set the starting `DateTime` and returns a new `Period` object

#### getEnd();

return the ending `DateTime`. **This object represents the first `DateTime` which is not part of the `Period`**.

#### setEnd($datetime)

set the ending `DateTime` and returns a new `Period` object;

#### getDuration()

return the current Period Duration as a `DateInterval` object.

#### setDuration($interval)

modify the Period duration and change the ending `DateTime` value. The methods returns a new `Period` object.

#### getRange($interval)

returns a DatePeriod object that lists `DateTime` objects inside the Period separeted by the given `$interval`. The `$interval` parameter is a `DateInterval` or a string parsable by the `DateInterval::createFromDateString` method.

#### contains($datetime)

Tells whether a `$datetime` is contained within the `Period` or not. The `$datetime` parameter is a `DateTime` object or a string parsable by the `DateTime` constructor

#### overlaps(Period $period)

Tells whether two `Period` object overlaps each other or not.

#### merge(Period $period)

Merge two `Period` object by return a new `Period` object which starting DateTime is the smallest of both objects and the ending DateTime is the biggest between bith objects.

Examples
-------

```php

use Bakame\Period;

$range = Period::createFromSemester(2012, 1);
$range->getStart(); //2012-01-01
$range->getEnd(); //2012-07-01
$range->getDuration(); //returns a DateInterval object
$range->contains('2012-07-01'); // returns false;
$range->contains('2012-06-30'); // returns true;

$newRange     = $range->setEnd('2012-02-01');
$altRange     = $range->setEnd(new DateTime('2012-02-01'));
$anotherRange = $range->setDuration('1 MONTH');

//$anotherRange->getDuration() is equal to $altRange->getDuration();

$newRange->overlaps($range) //return true;
$newPeriod = $range->merge($newRange); //$newPeriod will have the smallest start and the biggest end value

foreach ($range->getRange('1 DAY') as $datetime) {
    echo $datetime->format('Y-m-d H:i:s'); 
    //each $datetime is separated by the $interval parameter
    //in this example '1 DAY'
}
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