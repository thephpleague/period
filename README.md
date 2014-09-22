Bakame.Tools
============

Tools to perform simple tasks

[![Latest Version](https://img.shields.io/github/release/nyamsprod/Bakame.Tools.svg?style=flat-square)](https://github.com/nyamsprod/Bakame.Tools/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/nyamsprod/Bakame.Tools/master.svg?style=flat-square)](https://travis-ci.org/nyamsprod/Bakame.Tools)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/nyamsprod/Bakame.Tools.svg?style=flat-square)](https://scrutinizer-ci.com/g/nyamsprod/Bakame.Tools/code-structure)
[![Quality Score](https://img.shields.io/scrutinizer/g/nyamsprod/Bakame.Tools.svg?style=flat-square)](https://scrutinizer-ci.com/g/nyamsprod/Bakame.Tools)
[![Total Downloads](https://img.shields.io/packagist/dt/bakame/tools.svg?style=flat-square)](https://packagist.org/packages/bakame/tools)


This package is compliant with [PSR-2], and [PSR-4].

[PSR-2]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md
[PSR-4]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md

System Requirements
-------

You need **PHP >= 5.3.0** to use `Bakame\Tools` but the latest stable version of PHP is recommended.

Install
-------

Install the `Bakame.Tools` package with Composer.

```json
{
    "require": {
        "Bakame\Tools": "*"
    }
}
```
### Going Solo

You can also use `Bakame\Tools` without using Composer by downloading the library and registing an autoloader function:

```php
spl_autoload_register(function ($class) {
    $prefix = 'Bakame\\Tools\\';
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


## Period

This class is based on [Resolving Feature Envy in the Domain](http://verraes.net/2014/08/resolving-feature-envy-in-the-domain/) by Matthias Verraes and helped me resolve many recurrent issues around Date Range selection and usage.

The `Period` class is a Immutable Value Object so any change to its property returns a new `Period` class.

**Of Note:** The Period::end represent the first DateTime object which is **not** part of the Period.

### Instantiation

This class comes with many named constructors to ease its instantiation:

- `Period::createFromWeek($year, $week)` : `$year` the selected year, `$week` the selected week (from 1 to 53);
- `Period::createFromMonth($year, $month)` : `$year` the selected year, `$month` the selected month (from 1 to 12);
- `Period::createFromQuarter($year, $quarter)` : `$year` the selected year, `$quarter` the selected quarter (from 1 to 4);
- `Period::createFromSemester($year, $semester)` : `$year` the selected year, `$semester` the selected semester (from 1 to 2);
- `Period::createFromDuration($datetime, $ttl)` : `$datetime` a `DateTime` object or a string parsable by the `DateTime` constructor, $ttl a `DateInterval` or a string parsable by the `DateInterval::createFromDateString` method.


### Usage

Once instantiated the `Period` object :

- gives you the starting `DateTime` using the `getStart` method;
- gives you the ending `DateTime` using the `getEnd` method;
- gives you the duration as a `DateInterval` object using the `getDuration` method;
- tells you if a given `DateTime` is contained within the range using the `contains` method;
- gives you the list of `DateTime` object inside the range as a `DatePeriod` object using the `getRange()` method;

You can change:

* the starting `DateTime` using the `setStart` method;
* the ending `DateTime` using the `setEnd` method or the `setDuration` method;

But those methods return a new `Period` object as the object is immutable.

```php

use Bakame\Tools\Period;

$range = Period::createFromSemester(2012, 1);
$range->getStart(); //2012-01-01
$range->getEnd(); //2012-07-01
$range->getDuration(); //returns a DateInterval object
$range->getDatePeriod('1 DAY'); //return a DatePeriod object with an Interval between date of 1 DAY
$range->contains('2012-07-01'); // returns false;
$range->contains('2012-06-30'); // returns true;

$newRange     = $range->setEnd('2012-02-01');
$altRange     = $range->setEnd(new DateTime('2012-02-01'));
$anotherRange = $range->setDuration('1 MONTH');
//$anotherRange and $altRange are equals

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