---
layout: default
title: Upgrading from 4.x to 5.x
---

# Upgrading from 4.x to 5.x

`5.0` is a new major version that comes with backward compatibility breaks.

This guide will help you migrate from a 4.x version to 5.0. It will only explain backward compatibility breaks, it will not present the new features ([read the documentation for that](/5.0/)).

## Installation

If you are using composer then you should update the `require` section of your `composer.json` file.

~~~
composer require league/period:^5.0
~~~

This will edit (or create) your `composer.json` file.

## PHP version requirement

`5.0` requires a PHP version greater than or equal 8.1 (was previously 7.1.3).

## Removed methods

### Already deprecated methods and functions

All namespaced functions are removed from the package:

- `League\Period\DatePoint`
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

| Removed methods                | Possible replacements     |
|--------------------------------|---------------------------|
| `Datepoint::create`            | none                      |
| `Datepoint::createFromMutable` | none                      |
| `Datepoint::createFromFormat`  | none                      |
| `Duration::__toString`         | none                      |
| `Duration::create`             | none                      |
| `Duration::format`             | none                      |
| `Duration::withoutCarryOver`   | `Duration::adjustedTo`    |
| `Period::__toString`           | `Period::toIso8601`       |
| `Period::substract`            | `Period::subtract`        |
| `Sequence::getIntersections`   | `Sequence::intersections` |
| `Sequence::getBoundaries`      | `Sequence::length`        |
| `Sequence::getGaps`            | `Sequence::gaps`          |

## Change in inheritance

The following classes have their inheritance changed:

- The `Datepoint` class is renamed `DatePoint` and no longer extends the `DateImmutable` class
- The `Duration` class no longer extends the `DateInterval` class

## Change in getters and setters

With the introduction of public readonly properties some methods have been dropped in favor 
of explicit public properties:

| `4.x` method name                    | `5.x` property                             |
|--------------------------------------|--------------------------------------------|
| `Period::getStartDate()`             | `Period::startDate`                        |
| `Period::getEndDate()`               | `Period::endDate`                          |
| `Period::getBoundaryType()`          | `Period::bounds`                           |
| `GanttChartConfig::output()`         | `GanttChartConfig::output`                 |
| `GanttChartConfig::startExcluded()`  | `GanttChartConfig::startExcludedCharacter` |
| `GanttChartConfig::startIncluded()`  | `GanttChartConfig::startIncludedCharacter` |
| `GanttChartConfig::endExcluded()`    | `GanttChartConfig::endExcludedCharacter`   |
| `GanttChartConfig::endIncluded()`    | `GanttChartConfig::endIncludedCharacter`   |
| `GanttChartConfig::width()`          | `GanttChartConfig::width`                  |
| `GanttChartConfig::body()`           | `GanttChartConfig::bodyCharacter`          |
| `GanttChartConfig::space()`          | `GanttChartConfig::spaceCharacter`         |
| `GanttChartConfig::colors()`         | `GanttChartConfig::colors`                 |
| `GanttChartConfig::gapSize()`        | `GanttChartConfig::gapSize`                |
| `GanttChartConfig::labelAlign()`     | `GanttChartConfig::labelAlignment`         |
| `GanttChartConfig::leftMarginSize()` | `GanttChartConfig::leftMarginSize`         |
| `LatinLetter::startingAt()`          | `LatinLetter::startingAt`                  |
| `DecimalNumber::startingAt()`        | `DecimalNumber::startingAt`                |
| `RomanNumber::startingAt()`          | `RomanNumber::startingAt`                  |
| `AffixLabel::prefix()`               | `AffixLabel::prefix`                       |
| `AffixLabel::suffix()`               | `AffixLabel::suffix`                       |

Conversely the old getter are now used for setter purposes and the `with` prefix is dropped
where it does no longer have meaning.

| `4.x` method name                        | `5.x` method name                              |
|------------------------------------------|------------------------------------------------|
| `GanttChartConfig::withOutput()`         | `GanttChartConfig::output()`                   |
| `GanttChartConfig::withStartExcluded()`  | `GanttChartConfig::startExcludedCharacter()`   |
| `GanttChartConfig::withStartIncluded()`  | `GanttChartConfig::startIncludedCharacter()`   |
| `GanttChartConfig::withEndExcluded()`    | `GanttChartConfig::endExcludedCharacter()`     |
| `GanttChartConfig::withEndIncluded()`    | `GanttChartConfig::endIncludedCharacter()`     |
| `GanttChartConfig::withWidth()`          | `GanttChartConfig::width()`                    |
| `GanttChartConfig::withBody()`           | `GanttChartConfig::bodyCharacter()`            |
| `GanttChartConfig::withSpace()`          | `GanttChartConfig::spaceCharacter()`           |
| `GanttChartConfig::withColors()`         | `GanttChartConfig::colors()`                   |
| `GanttChartConfig::withGapSize()`        | `GanttChartConfig::gapSize()`                  |
| `GanttChartConfig::withLabelAlign()`     | `GanttChartConfig::labelAlignment()`           |
| `GanttChartConfig::withLeftMarginSize()` | `GanttChartConfig::leftMarginSize()`           |
| `LatinLetter::withStartingAt()`          | none instantiate a new `LatinLetter` instead   |
| `DecimalNumber::withStartingAt()`        | none instantiate a new `DecimalNumber` instead |
| `RomanNumber::withStartingAt()`          | none instantiate a new `RomanNumber` instead   |
| `AffixLabel::withPrefix()`               | none instantiate a new `AffixLabel` instead    |
| `AffixLabel::withSuffix()`               | none instantiate a new `AffixLabel` instead    |

```diff
- GanttChartConfig::fromRainbow()->withGapSize(3)->gapSize(); // returns 3
+ GanttChartConfig::fromRainbow()->gapSize(3)->gapSize; // returns 3
```

## Change in method name

The following methods were renamed between `4.x` and `5.x`. 
Most notably:

- the `get` prefix is removed.
- the `create` prefix is removed.
- the `__toString` method and usage is removed from the package. 
- conversions methods are explicitly named with a `to` or a `from` prefix.
- methods name have been changed for consistency throughout the package.

| `4.x` method name                     | `5.x` method name                             |
|---------------------------------------|-----------------------------------------------|
| `Period::fromDatepoint`               | `Period::fromDate`                            |
| `Period::__construct`                 | `Period::fromDate` or `Period::fromTimestamp` |
| `Period::getDateInterval`             | `Period::dateInterval`                        |
| `Period::getTimestampInterval`        | `Period::timeDuration`                        |
| `Period::withBoundaryType`            | `Period::boundedBy`                           |
| `Period::getDatePeriod`               | `Period::dateRangeForward`                    |
| `Period::getDatePeriodBackwards`      | `Period::dateRangeBackwards`                  |
| `Period::split`                       | `Period::splitForward`                        |
| `Period::__toString`                  | `Period::toIso8601`                           |
| `Period::format`                      | `Period::toIso80000`                          |
| `Period::timestampIntervalDiff`       | `Period::timeDurationDiff`                    |
| `Sequence::getTotalTimestampInterval` | `Sequence::totalTimeDuration`                 |
| `Sequence::toArray`                   | `Sequence::toList`                            |
| `Duration::createFromSeconds`         | `Duration::fromSeconds`                       |
| `Duration::createFromChronoString`    | `Duration::fromChronoString`                  |
| `Duration::createFromTimeString`      | `Duration::fromTimeString`                    |
| `Duration::createFromDateString`      | `Duration::fromDateString`                    |
| `Duration::createFromDateInterval`    | `Duration::fromDateInterval`                  |
| `Datepoint::getSecond`                | `Datepoint::second`                           |
| `Datepoint::getMinute`                | `Datepoint::minute`                           |
| `Datepoint::getHour`                  | `Datepoint::hour`                             |
| `Datepoint::getIsoWeek`               | `Datepoint::isoWeek`                          |
| `Datepoint::getMonth`                 | `Datepoint::month`                            |
| `Datepoint::getQuarter`               | `Datepoint::quarter`                          |
| `Datepoint::getSemester`              | `Datepoint::semester`                         |
| `Datepoint::getYear`                  | `Datepoint::year`                             |
| `Datepoint::getIsoYear`               | `Datepoint::isoYear`                          |

```diff
- Period::fromDatepoint('2021-05-23', '2021-05-24', Period::INCLUDE_ALL)->getStartDate();
+ Period::fromDate('2021-05-23', '2021-05-24', Bounds::IncludeAll)->startDate;

- (new Datepoint('NOW'))->getHour()->format('Y-m-d H:i:s');
+ DatePoint::fromDateString('NOW')->hour()->toIso80000('Y-m-d H:i:s');
```

## Backward Incompatibility Changes

### Change in formatting methods

`Period::jsonSerialize` representation adds two new boolean properties

- `startDateIncluded`
- `endDateIncluded`

to expose the boundaries properties of the `Period` object.

```php
echo json_encode(Period::fromMonth(2015, 4)), PHP_EOL;
```

will now return the following JSON:

```diff
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
- a string that can be parsed by the `DateTimeImmutable` construct
- a string like integer that would be converted to a timestamp

In version `5.x` to avoid hard to debug issues and by taking advantage 
of union types a date can only accept the following types:

- a `DateTimeInterface` implementing object
- a `DatePoint` object
- a `string` that can be parsed by the `DateTimeImmutable` construct

if better or more complex conversion is needed, first convert it using a `DateTimeInterface` implementing object
or a `DatePoint` named constructor.

```diff
- Period::fromDatepoint('1635585868', '2021-05-24', Period::INCLUDE_ALL);
+ Period::fromDate(Carbon::createFromTimestamp(1635585868), '2021-05-24', Bounds::IncludeAll);
```

In version `4.x` a method expecting a duration accepts the following types:

- a `DateInterval` object
- a `Period` object  
- a string like float that would be converted to a duration in seconds
- an object that implements the `__toString` method converted into a string
- a string in the Chronometer format
- a string in a specific `DateInterval` format
- a string convertible into a `DateInterval` object via its `createFromDateString` named constructor

In version `5.x` to avoid hard to debug issues and by taking advantage of union type
the duration can only accept the following types:

- a `DateInterval` object
- a `Period` object
- a `Duration` object
- a `string` that can be parsed by the `DateInterval::createFromDateString` named constructor

if better or more complex conversion is needed, first convert it using a `DateInterval` object
or one of the `Duration` named constructor.

```diff
- Period::after('2021-05-23', '12:30', Period::INCLUDE_ALL);
+ Period::after(
+    '2021-05-23', 
+    Duration::fromChronoString('12:30'), 
+    Bounds::IncludeAll
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
+ Period::fromDate('2021-03-21 12:23:56', '2021-03-21 13:23:56', Bounds::ExcludeAll);
```

`Period::timeDuration` now returns an `int` instead of a `float` value.

```diff
- $period->timestampInterval(); //returns float
+ $period->timeDuration();    //returns int
```

`Period::diff` now returns a `Sequence` object, before it was returning an `array`.

The following example 
```php
$period = Period::fromDate('2013-01-01', '2014-01-01');
$alt = Period::fromDate('2013-01-01', '2014-01-01');
```
 
will have its return value updated.

```diff
- [] === $alt->diff($period); // return true
+ $alt->diff($period)->isEmpty(); //return true
```

in `5.x` `Closure` objects are used instead of the callable pseudo type with the `Sequence` methods.

```diff
- $res = $sequence->filter('myFilter');    // a callable string can be given
+ $res = $sequence->filter(myFilter(...)); // a Closure object MUST be given
```

`Period::dateRange` and `Period::dateRangeBackwards` accept a `InitialDatePresence` Enum as a parameter to control the presence
or not of the initial date object in their returned values.

```diff
- $res = $period->getDatePeriodBackwards('1 DAY', DatePeriod::EXCLUDE_START_DATE); 
+ $res = $period->dateRangeBackwards('1 DAY', InitialDatePresence::Excluded);
```

## Changes in bounds related methods

With the introduction of the `Bounds` enum, all bound related methods have been moved to the added Enum.

```diff
- $period->isStartDateIncluded();       // return true
+ $period->bounds->isStartIncluded(); // return true
```

| `4.x` method name                             | `5.x` method name                             |
|-----------------------------------------------|-----------------------------------------------|
| `Period::getBoundaryType`                     | `Period::bounds` (a public readonly property) |
| `Period::withBoundaryType` (expects a string) | `Period::boundedBy` (expects a Bounds enum)   |
| `Period::isStartIncluded`                     | `Bounds::isStartIncluded`                     |
| `Period::isStartExcluded`                     | `Bounds::isStartIncluded`                     |
| `Period::isEndIncluded`                       | `Bounds::isEndIncluded`                       |
| `Period::isEndExcluded`                       | `Bounds::isEndIncluded`                       |

## Changes in Sequence conversion

The array provided by the `Sequence::toList` method will always be a list. While the order of the array values
may change using the `Sequence::sort` method, for instance, the return array indexes will always be re-arranged to
return a proper list.

The following code will work in both versions:

```php
$day1 = Period::fromDay(2012, 6, 23);
$day2 = Period::fromDay(2012, 6, 12);
$sequence = new Sequence($day1, $day2);
$sequence->sort(fn (Period $period1, Period $period2): int => $period1->startDate <=> $period2->startDate);
foreach ($sequence as $offset => $period) {
// first iteration $offset = 1 and $period === $day2
// second iteration $offset = 0 and $period === $day1
}
```

But the returned value will be different:

```diff
- $sequence->toArray(); // returns [1 => $day2, 0 => $day1];
+ $sequence->toList();  // returns [0 => $day2, 1 => $day1];
```

## Changes in Charts LabelGenerator

The label generators provided by the package no longer allows changes.
Instead of modifying the label generator, create a new instance instead with new explicitly set arguments.

```diff
- $labelGenerator = new LatinLetter('A');
- $labelGenerator->startingAt(); // returns 'A'
- $labelGenerator->startsWith('a')->startingAt(); // returns 'a'
+ (new LatinLetter('A'))->startLabel; // returns 'A'
+ (new LatinLetter('a'))->startLabel; // returns 'a'
```

| `4.x` method name                  | `5.x` method name                                    |
|------------------------------------|------------------------------------------------------|
| `LatinLetter::startsWith`          | removed with no replacement                          |
| `LatinLetter::startingAt` method   | `LatinLetter::startLabel` public readonly property   |
| `DecimalNumber::startsWith`        | removed with no replacement                          |
| `DecimalNumber::startingAt` method | `DecimalNumber::startLabel` public readonly property |
| `AffixLabel::suffix` method        | `AffixLabel::labelSuffix` public readonly property   |
| `AffixLabel::prefix` method        | `AffixLabel::labelPrefix` public readonly property   |
| `AffixLabel::withPrefix`           | removed with no replacement                          |
| `AffixLabel::withSuffix`           | removed with no replacement                          |

The `LatinLetter` label generator no longer fall back to using the `0` value. Only ASCII letters will be used.

```diff
- var_export(iterator_to_array((new LatinLetter(''))->generate(1), false)); // [0 => '0']
+ var_export(iterator_to_array((new LatinLetter(''))->generate(1), false)); // [0 => 'A']
```

The `DecimalNumber` label generator allows negative integer and `O`. Previously they would be silently converted to `1`. 

For the following instance

```php
$labelGenerator = new DecimalNumber(-3);
```
The returned object will behave as follow:

```diff
- $labelGenerator->startingAt(); // returns '1'
+ $labelGenerator->startLabel;   // returns '-3'
```

The `RomanNumber` label generator constructor will throw if the `DecimalNumber::startLabel` is lower than `1`.

```diff
-  $labelGenerator = new RomanNumber(new DecimalNumber(-5), RomanNumber::LOWER);
-  $labelGenerator->startingAt(); //returns 'i'
+  $labelGenerator = new RomanNumber(new DecimalNumber(-5), LetterCase::Lower);
+  //will throw UnableToDrawChart exception
```
