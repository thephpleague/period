---
layout: default
title: Upgrading from 3.x to 4.x
---

# Upgrading from 3.x to 4.x

`4.0` is a new major version that comes with backward compatibility breaks.

This guide will help you migrate from a 3.x version to 4.0. It will only explain backward compatibility breaks, it will not present the new features ([read the documentation for that](/4.0/)).

## Installation

If you are using composer then you should update the require section of your `composer.json` file.

~~~
composer require league/period:^4.0
~~~

This will edit (or create) your `composer.json` file.

## PHP version requirement

`4.0` requires a PHP version greater than or equal 7.1.3 (was previously 5.5.9).

<p class="message-warning"><code>HHVM</code> support is dropped.</p>

## Removed methods

### Already deprecated methods

The following methods were already marked as deprecated is the `3.x` line. They are now removed from the class.

| removed methods    | possible replacements                        |
| ------------------ | -------------------------------------------- |
| `Period::next`     | `Period::move` with positive duration        |
| `Period::previous` | `Period::move` with negative duration        |
| `Period::add`      | `Period::moveEndDate` with positive duration |
| `Period::sub`      | `Period::moveEndDate` with negative duration |

### Named constructors

If you are migrating from `3.x` to `4.2+` version you should use the new named constructors

| old named constructors                |  new named constructors |
| ------------------------------------- | ----------------------- |
| `Period::createFromYear`              | `Period::fromYear`      |
| `Period::createFromMonth`             | `Period::fromMonth`     |
| `Period::createFromWeek`              | `Period::fromIsoWeek`   |
| `Period::createFromDay`               | `Period::fromDay`       |
| `Period::createFromSemester`          | `Period::fromSemester`  |
| `Period::createFromQuarter`           | `Period::fromQuarter`   |
| `Period::createFromDuration`          | `Period::after`         |
| `Period::createFromDurationBeforeEnd` | `Period::before`        |

The arguments are the same as in version 3 but the new named constructors accepts overflow like `DateTimeImmutable` objects.

Before:

~~~php
use League\Period\Period;

$period = Period::createFromMonth(2013, 15);
// throw LogicException
~~~

After:

~~~php
use League\Period\Period;

$period = Period::fromMonth(2013, 15);
// returns new Period('2014-03-01', '2014-04-01')
~~~

If you are using `4.2-` version you are required to use functions defined in the same namespace as the `Period` class.

| removed named constructors            |  new functions    |
| ------------------------------------- | ----------------- |
| `Period::createFromYear`              | `year`            |
| `Period::createFromMonth`             | `month`           |
| `Period::createFromWeek`              | `iso_week`        |
| `Period::createFromDay`               | `day`             |
| `Period::createFromSemester`          | `semester`        |
| `Period::createFromQuarter`           | `quarter`         |
| `Period::createFromDuration`          | `interval_after`  |
| `Period::createFromDurationBeforeEnd` | `interval_before` |

The functions take the same arguments in the same order.

Before:

~~~php
use League\Period\Period;

$period = Period::createFromDuration('2014-03-01', '1 MONTH');
~~~

After:

~~~php
use function League\Period\interval_after;

$period = interval_after('2014-03-01', '1 MONTH');
~~~

## Renamed methods

To remove ambiguity, the following methods have been renamed

| previous name                 |  new name                        |
| ----------------------------- | -------------------------------- |
| `Period::sameValueAs`         | `Period::equals`                 |
| `Period::sameDurationAs`      | `Period::durationEquals`         |
| `Period::compareDuration`     | `Period::durationCompare`        |
| `Period::withDuration`        | `Period::withDurationAfterStart` |

Before:

~~~php
$period = Period::createFromDuration('2014-03-01', '1 MONTH');
$alt_period = $period->withDuration('1 WEEK');
~~~

After:

~~~php
$period = interval_after('2014-03-01', '1 MONTH');
$alt_period = $period->withDurationAfterStart('1 WEEK');
~~~

## Backward Incompatible Changes

### Period::contains

To be more consistent with the mathematical representation of right open intervals, the following snippets differs between version 3.x et version 4.

Before:

~~~php
$instant = new Period('2014-03-01', '2014-03-01');
$period->contains($instant->getStartDate()); //return true
$period->contains($instant->getEndDate()); //return false
~~~

After:

~~~php
$instant = new Period('2014-03-01', '2014-03-01');
$period->contains($instant->getStartDate()); //return false
$period->contains($instant->getEndDate());   //return false
~~~

In other words, starting with version 4.0, an interval whose duration is equivalent to `new DateInterval('PT0S')` can not contains any datepoint.

### Period::diff

The methods now always returns an `array` containing two values. Those values can be a `Period` object or `null`.

Before:

~~~php
$period = Period::createFromDuration('2014-03-01', '1 MONTH');
$alt_period = Period::createFromDuration('2014-03-01', '2 WEEKS');

$diff = $period->diff($alt_period);
count($diff); //returns 1
get_class($diff[0]); //returns League\Period\Period
~~~

After:

~~~php
$period = interval_after('2014-03-01', '1 MONTH');
$alt_period = interval_after('2014-03-01', '2 WEEKS');

$diff = $period->diff($alt_period);
count($diff); //returns 2
get_class($diff[0]); //returns League\Period\Period
is_null($diff[1]);  //returns true
~~~

### Period::jsonSerialize

The output from `Period::jsonSerialize` has been updated to enable a better translation between the PHP and the Javascript DateTime notation.

Before:

~~~php
date_default_timezone_set('Africa/Kinshasa');

use League\Period\Period;

$period = new Period('2014-05-01 00:00:00', '2014-05-08 00:00:00');

$res = json_decode(json_encode($period), true);
//  $res will be equivalent to:
// [
//      'startDate' => [
//          'date' => '2014-05-01 00:00:00',
//          'timezone_type' => 3,
//          'timezone' => 'Africa/Kinshasa',
//      ],
//      'endDate' => [
//          'date' => '2014-05-08 00:00:00',
//          'timezone_type' => 3,
//          'timezone' => 'Africa/Kinshasa',
//      ],
// ]
~~~

After:

~~~php
date_default_timezone_set('Africa/Kinshasa');

$period = new Period('2014-05-01 00:00:00', '2014-05-08 00:00:00');

$res = json_decode(json_encode($period), true);
//  $res will be equivalent to:
// [
//      'startDate' => '2014-04-30T23:00:00.000000Z,
//      'endDate' => '2014-05-07T23:00:00.000000Z',
// ]
~~~
