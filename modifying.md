---
layout: default
title: the Period object as an immutable value object
permalink: modifying/
---

# Modifying

The `Period` object:

- implements the `League\Period\PeriodInterface`
- is an **immutable value object** so any change to its property returns a new `Period` object.

<p class="message-warning">If no <code>Period</code> object can be created the modifying methods throw a <code>LogicException</code> exception.</p>

## Using endpoints

### Period::startingOn($start)

Returns a new `PeriodInterface` object with `$start` as the new **starting included endpoint** defined as a `DateTime` object.

~~~php
use League\Period\Period;

$period    = Period::createFromMonth(2014, 3);
$newPeriod = $period->startingOn('2014-02-01');
$period->getStart(); //returns DateTime('2014-03-01');
$newPeriod->getStart(); //returns DateTime('2014-02-01');
// $period->getEnd() equals $newPeriod->getEnd();
~~~

### Period::endingOn($end)

Returns a new `PeriodInterface` object with `$end` as the new **ending excluded endpoint** defined as a `DateTime` object.

~~~php
use League\Period\Period;

$period    = Period::createFromMonth(2014, 3);
$newPeriod = $period->EndingOn('2014-03-16');
$period->getEnd(); //returns DateTime('2014-04-01');
$newPeriod->getEnd(); //returns DateTime('2014-03-16');
// $period->getStart() equals $newPeriod->getStart();
~~~

## Using durations

The supplied `DateInterval` object can be added or substracted from the starting and/or ending endpoint.

### Period::withDuration($duration)

Returns a new `PeriodInterface` object by updating its duration. Only the excluded endpoint is updated.

The `$duration` parameter is expressed as a `DateInterval` object.

~~~php
use League\Period\Period;

$period    = Period::createFromMonth(2014, 3);
$newPeriod = $period->withDuration('2 WEEKS');
$period->getEnd(); //returns DateTime('2014-04-01');
$newPeriod->getEnd(); //returns DateTime('2014-03-16');
// $period->getStart() equals $newPeriod->getStart();
~~~

### Period::add($duration)

Returns a new `PeriodInterface` object by adding an interval to the current ending excluded endpoint.

The `$duration` parameter is expressed as a `DateInterval` object.

~~~php
use League\Period\Period;

$period    = Period::createFromMonth(2014, 3);
$newPeriod = $period->add('2 WEEKS');
// $period->getStart() equals $newPeriod->getStart();
~~~

### Period::sub($duration)

Returns a new `PeriodInterface` object by substracting an interval to the current ending excluded endpoint.

The `$duration` parameter is expressed as a `DateInterval` object.

~~~php
use League\Period\Period;

$period    = Period::createFromMonth(2014, 3);
$newPeriod = $period->sub('2 WEEKS');
// $period->getStart() equals $newPeriod->getStart();
~~~

### Period::next($duration = null)

<p class="message-notice">Added to <code>Period</code> in version 2.1</p>

Returns a new `PeriodInterface` object adjacent to the current `PeriodInterface` and starting with its ending endpoint. If no interval is provided, the new `Period` object will be created using the current `Period` duration.

~~~php
use League\Period\Period;

$period    = Period::createFromMonth(2014, 3);
$newPeriod = $period->next('1 MONTH');
// $period->getEnd() equals $newPeriod->getStart();
~~~

<p class="message-warning">When no <code>$duration</code> is provided to the method the new <code>Period</code> duration may vary. See below for a concrete example</p>

~~~php
use League\Period\Period;

$january  = Period::createFromMonth(2012, 1); //January 2012
$february = $period->next();
$march    = $newPeriod->next();
$january->sameDurationAs($february); //return false;
$january->sameDurationAs($march); //return false;

echo $january;  // 2012-01-01T00:00:00+0100/2012-02-01T00:00:00+0100
echo $february; // 2012-02-01T00:00:00+0100/2012-03-01T00:00:00+0100
echo $march;    // 2012-03-01T00:00:00+0100/2012-03-30T00:00:00+0200

// $march does not represents the full month
// since the ending endpoint is excluded from the period!!
~~~

<p class="message-info">To remove any ambiguity, it is recommended to always provide a <code>$duration</code> when using <code>Period::next</code></p>

### Period::previous($duration = null)

<p class="message-notice">Added to <code>Period</code> in version 2.1</p>

Complementary to `PeriodInterface::next`, the created `PeriodInterface` object is adjacent to the current `PeriodInterface` **but** its ending endpoint is equal to the starting endpoint of the current object.

~~~php
use League\Period\Period;

$period    = Period::createFromMonth(2014, 3);
$newPeriod = $period->previous('1 WEEK');
// $period->getEnd() equals $newPeriod->Start();
$period->durationGreaterThan($newPeriod); //return true
~~~

The method must be used with the same arguments and warnings as `PeriodInterface::next`.

`PeriodInterface::next` and `PeriodInterface::previous` methods allow to easily create adjacent Periods as shown in the graph below

![](/media/period-adjacents.png "$previous and $next are adjacent to the $period object")

~~~php
use League\Period\Period;

$current = Period::createFromMonth(2012, 1);
$prev    = $current->previous('1 MONTH');
$next    = $curent->next('1 MONTH');
~~~

## Using TimeRangeInterface implemented objects

### Period::merge(TimeRangeInterface $period[, TimeRangeInterface $...])

Merges two or more `TimeRangeInterface` objects by returning a new `PeriodInterface` object which englobes all the submitted objects.

~~~php
use League\Period\Period;

$period = Period::createFromSemester(2012, 1);
$alt    = Period::createFromWeek(2013, 4);
$other  = Period::createFromDuration('2012-03-07 08:10:27', 86000*3);
$newPeriod = $period->merge($alt, $other);
// $newPeriod->getStart() equals $period->getStart();
// $newPeriod->getEnd() equals $altPeriod->getEnd();
~~~

### Period::intersect(TimeRangeInterface $period)

An TimeRangeInterface overlaps another if it shares some common part of the datetime continuum. This method returns the amount of the overlap as a PeriodInterface object, only if they actually do overlap. If they do not overlap or abut, then an Exception is thrown.

<p class="message-info">Before getting the intersection, make sure the <code>Period</code> objects, at least, overlap each other.</p>

![](/media/period-intersect.png "$intersectPeriod represents the intersection Period between both Period object")

~~~php
use League\Period\Period;

$period        = Period::createFromDuration(2012-01-01, '2 MONTHS');
$anotherPeriod = Period::createFromDuration(2012-01-15, '3 MONTHS');
$intersectPeriod = $period->insersect($anotherPeriod);
~~~

### Period::gap(TimeRangeInterface $period)

<p class="message-notice">Added to <code>Period</code> in version 2.2</p>

 A TimeRangeInterface has a gap with another TimeRangeInterface if there is a non-zero interval between them. This method returns the amount of the gap as a new PeriodInterface object only if they do actually have a gap between them. If they overlap a Exception is thrown.

<p class="message-info">Before getting the gap, make sure the <code>Period</code> objects do not overlaps.</p>

![](/media/period-gap.png "$gapPeriod represents the gap Period between both Period objects")

~~~php
use League\Period\Period;

$orig = Period::createFromDuration(2012-01-01, '2 MONTHS');
$alt  = Period::createFromDuration(2013-01-15, '3 MONTHS');
$gapPeriod = $period->gap($alt);
~~~