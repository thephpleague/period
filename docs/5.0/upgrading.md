---
layout: default
title: Upgrading from 4.x to 5.x
---

# Upgrading from 4.x to 5.x

`5.0` is a new major version that comes with backward compatibility breaks.

This guide will help you migrate from a 4.x version to 5.0. It will only explain backward compatibility breaks, it will not present the new features ([read the documentation for that](/5.0/)).

## Installation

If you are using composer then you should update the require section of your `composer.json` file.

~~~
composer require league/period:^5.0
~~~

This will edit (or create) your `composer.json` file.

## PHP version requirement

`5.0` requires a PHP version greater than or equal 8.1 (was previously 7.1.3).

## Removed methods

### Already deprecated methods and functions

All namespaced functions are removed from the package:

- `League\Period\datepoint`
- `League\Period\duration`
- `League\Period\year`
- `League\Period\semester`
- `League\Period\quarter`
- `League\Period\month`
- `League\Period\day`
- `League\Period\hour`
- `League\Period\minute`
- `League\Period\second`
- `League\Period\instant`
- `League\Period\iso_year`
- `League\Period\iso_week`
- `League\Period\interval_after`
- `League\Period\interval_before`
- `League\Period\interval_around`
- `League\Period\interval_from_dateperiod`

The following methods were already marked as deprecated is the `4.x` line. 
They are now removed from the package.

| Removed methods                | Possible replacements       |
| ------------------------------ | --------------------------- |
| `Datepoint::create`            | none                        |
| `Datepoint::createFromMutable` | none                        |
| `Datepoint::createFromFormat`  | none                        |
| `Duration::__toString`         | none                        |
| `Duration::create`             | none                        |
| `Duration::fromTimeString`     | none                        |
| `Duration::format`             | none                        |
| `Duration::withoutCarryOver`   | `Duration::adjustedTo`      |
| `Period::__toString`           | `Period::toIso8601`         |
| `Period::substract`            | `Period::subtract`          |
| `Sequence::getIntersections`   | `Sequence::intersections`   |
| `Sequence::getBoundaries`      | `Sequence::boundaries`      |
| `Sequence::getGaps`            | `Sequence::gaps`            |

## Change in inheritance

The following classes have their inheritance changed:

- The `Datepoint` class is renamed `DatePoint` and no longer extends the `DateImmutable` class
- The `Duration` class no longer extends the `DateInterval` class

## Change in method name

The following methods were renamed between `4.x` and `5.x`. 
Most notably:

- the `get` prefix is removed.
- the `create` prefix is removed.
- the `__toString` method and usage is removed from the package. 
- conversions methods are explicitly named with a `to` or a `from` prefix.
- methods name hhave been changed for consistency throughout the package.

| `4.x` method name                     | `5.x` method name                  |
| ------------------------------------- |------------------------------------|
| `Period::fromDatepoint`               | `Period::fromDate`                 |
| `Period::getStartDate`                | `Period::startDate`                |
| `Period::getEndDate`                  | `Period::endDate`                  |
| `Period::getDateInterval`             | `Period::dateInterval`             |
| `Period::getTimestampInterval`        | `Period::timestampInterval`        |
| `Period::getBoundaryType`             | `Period::bounds`                   |
| `Period::withBoundaryType`            | `Period::withBounds`               |
| `Period::getDatePeriod`               | `Period::dateRangeForward`         |
| `Period::getDatePeriodBackwards`      | `Period::dateRangeBackwards`       |
| `Period::__toString`                  | `Period::toIso8601`                |
| `Period::format`                      | `Period::toNotation`               |
| `Sequence::getTotalTimestampInterval` | `Sequence::totalTimestampInterval` |
| `Duration::createFromSeconds`         | `Duration::fromSeconds`            |
| `Duration::createFromChronoString`    | `Duration::fromChronoString`       |
| `Duration::createFromDateString`      | `Duration::fromDateString`         |
| `Duration::createFromDateInterval`    | `Duration::fromDateInterval`       |
| `Datepoint::getSecond`                | `Datepoint::second`                |
| `Datepoint::getMinute`                | `Datepoint::minute`                |
| `Datepoint::getHour`                  | `Datepoint::hour`                  |
| `Datepoint::getIsoWeek`               | `Datepoint::isoWeek`               |
| `Datepoint::getMonth`                 | `Datepoint::month`                 |
| `Datepoint::getQuarter`               | `Datepoint::quarter`               |
| `Datepoint::getSemester`              | `Datepoint::semester`              |
| `Datepoint::getYear`                  | `Datepoint::year`                  |
| `Datepoint::getIsoYear`               | `Datepoint::isoYear`               |

## Backward Incompatibility Changes

### Change in formatting methods

`Period::jsonSerialize` representation adds two new boolean properties

- `startDateIncluded`
- `endDateIncluded`

to expose the boundaries properties of the `Period` object.

```diff
$period = Period::fromMonth(2015, 4);
echo json_encode($period), PHP_EOL;

{
     "startDate": "2015-04-01T00:00:00.000000Z",
     "endDate": "2015-05-01T00:00:00.000000Z",
+    "startDateIncluded": true,
+    "endDateIncluded": false
}
```

### Change in argument type hinting name

In version `4.x` a method expecting a date accepts the following types:

- a `DateTimeInterface` implementing object
- an integer that represents a timestamp
- a string that can be parsed by the DateTimeImmutable construct
- a string like integer that would be converted to a timestamp

In version `5.x` to avoid hard to debug issues and by taking advantage 
of union types a date can only accept the following types:

- a `DateTimeInterface` implementing object
- a `DatePoint` object

if you need to use a string you need to first convert it using a `DateTimeInterface` implementing object
or a `DatePoint` named constructor.

```diff
- Period::fromDatepoint('2021-05-23', '2021-05-24', Period::INCLUDE_ALL);
+ Period::after(
+    new DateTime('2021-05-23'), 
+    DatePoint::fromDateString('2021-05-24'), 
+    Bounds::INCLUDE_ALL
+ );
```

In version `4.x` a method expecting a duration accepts the following types:

- a `DateInterval` object
- a `Period` object  
- a string like float that would be converted to a duration in seconds
- an object that implements the `__toString` method converted into a string
- a string in the Chronometer format
- a string in a specific DateInterval format
- a string convertible into a DateInterval object via its `createFromDateString` named constructor

In version `5.x` to avoid hard to debug issues and by taking advantage of union type
the duration can only accept the following types:

- a `DateInterval` object
- a `Period` object
- a `Duration` object

if you need to use a string you need to first convert it using a `DateInterval` object
or one of the `Duration` named constructor.

```diff
- Period::after('2021-05-23', '1 HOUR', Period::INCLUDE_ALL);
+ Period::after(
+    new DateTime('2021-05-23'), 
+    DateInterval::createFromDateString('1 HOUR'), 
+    Bounds::INCLUDE_ALL
+ );
```

In `4.x` a method expecting or returning bounds information expected a string, in `5.x`
an `Bounds` enum is expected instead as shown in all previous examples.

## Changes in method signatures

Creating a Duration out of some seconds as changed, the method only accepts integer and the fraction should be explicitly set.

```diff
- $duration = Duration::createFromSeconds(2015.208);
+ $duration = Duration::fromSeconds(2015, 208);
```

`Period` default constructor is now private.

```diff
- new Period('2021-03-21 12:23:56', '2021-03-21 13:23:56', Period::EXCLUDE_ALL);
+ Period::fromDate(
+     new DateTime('2021-03-21 12:23:56'), 
+     new DateTimeImmutable('2021-03-21 13:23:56'), 
+     Bounds::EXCLUDE_ALL
+ );
```

`Period::timestampInterval` now returns an int instead of a float value.

```diff
- $period->timestampInterval(); //returns float
+ $period->timestampInterval(); //returns int
```

`Period::diff` now returns a `Sequence` object, before it was returning an `array`.

```diff
$period = Period::fromDate(new DateTimeImmutable('2013-01-01'), new DateTimeImmutable('2014-01-01'));
$alt = Period::fromDate(new DateTimeImmutable('2013-01-01'), new DateTimeImmutable('2014-01-01'));
        
- [] === $alt->diff($period); // return true
+ $alt->diff($period)->isEmpty(); //return true
```

in `5.x` Closure objects are used instead of the callable pseudo type with the `Sequence` methods.

## Changes in bounds related methods

With the introduction of the `Bounds` enum, all bound related methods are moved to the Enum>

```diff
$period = Period::fromDate(new DateTimeImmutable('2013-01-01'), new DateTimeImmutable('2014-01-01'));
        
- $period->isStartDateIncluded(); // return true
+ $period->bounds()->isLowerIncluded(); //return true
```

| `4.x` method name          | `5.x` method name         |
| -------------------------- |---------------------------|
| `Period::withBoundaryType` | `Period::withBounds`      |
| `Period::isStartIncluded`  | `Bounds::isStartIncluded` |
| `Period::isStartExcluded`  | `Bounds::isStartIncluded` |
| `Period::isEndIncluded`    | `Bounds::isEndIncluded`   |
| `Period::isEndExcluded`    | `Bounds::isEndIncluded`   |
