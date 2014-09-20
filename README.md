Bakame.Tools
============

Tools to perform simple tasks


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


## DateRange

This class is based on [Resolving Feature Envy in the Domain](http://verraes.net/2014/08/resolving-feature-envy-in-the-domain/) by Matthias Verraes and helped me resolve many recurrent issues around Date Range selection and usage.

The `DateRange` class is a Immutable Value Object so any change to its property returns a new `DateRange` class.

### Instantiation

This class comes with many named constructors to ease its instantiation:

- `DateRange::createFromMonth($year, $month)` : `$year` the selected year, `$month` the selected month (from 1 to 12);
- `DateRange::createFromWeek($year, $week)` : `$year` the selected year, `$week` the selected week (from 1 to 53);
- `DateRange::createFromQuarter($year, $quarter)` : `$year` the selected year, `$quarter` the selected quarter (from 1 to 4);
- `DateRange::createFromSemester($year, $semester)` : `$year` the selected year, `$semester` the selected semester (from 1 to 2);
- `DateRange::createFromDuration($datetime, $ttl)` : $datetime a `DateTime` object or a string parsable by the `DateTime` constructor, $ttl a `DateInterval` or a string parsable by the `DateInterval::createFromDateString` method.


### Usage

Once instantiated the `DateRange` object tells you:

- The starting `DateTime` using the `getStart` method;
- The ending `DateTime` using the `getEnd` method;
- The duration as a `DateInterval` object using the `getDuration` method;
- If a given `DateTime` is contained within the range using the `contains` method;

The object also give you the list of `DateTime` object inside the range as a `DatePeriod` object using the `getRange()` method;

You can change the start and the end of the `DateRange` using the setter for both properties, but you'll get a new `DateRange` object in return.

```php

use Bakame\Tools\DateRange;

$range = DateRange::createFromSemester(2012, 1);
$range->getStart(); //2012-01-01
$range->getEnd(); //2012-07-01
$range->getDuration(); //returns a DateInterval object
$range->getDatePeriod('1 DAY'); //return a DatePeriod object with an Interval between date of 1 DAY
$range->contains('2012-07-01'); returns true;
$range->contains('2012-07-01', true); returns false; // when the option is set to true the delimiters are excluded

$newRange = $range->setEnd('2012-02-01');
$altRange = $range->setEnd(new DateTime('2012-02-01'));
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