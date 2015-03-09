---
layout: default
title: the Period object as an immutable value object
---

# Modifying Period objects

You can manipulate a `League\Period\Period` object according to their datepoints or durations. `League\Period\Period` **is an immutable value object** which means that any change to its property returns a new `Period` object.

<p class="message-warning">If no <code>Period</code> object can be created the modifying methods throw a <code>LogicException</code> exception.</p>

## Using datepoints

### Period::startingOn($start)

Returns a new `Period` object with `$start` as the new **starting included datepoint** defined as a `DateTime` object.

~~~php
use League\Period\Period;

$period    = Period::createFromMonth(2014, 3);
$newPeriod = $period->startingOn('2014-02-01');
$period->getStartDate(); //returns DateTime('2014-03-01');
$newPeriod->getStartDate(); //returns DateTime('2014-02-01');
// $period->getEndDate() equals $newPeriod->getEndDate();
~~~

### Period::endingOn($end)

Returns a new `Period` object with `$end` as the new **ending excluded datepoint** defined as a `DateTime` object.

~~~php
use League\Period\Period;

$period    = Period::createFromMonth(2014, 3);
$newPeriod = $period->EndingOn('2014-03-16');
$period->getEndDate(); //returns DateTime('2014-04-01');
$newPeriod->getEndDate(); //returns DateTime('2014-03-16');
// $period->getStartDate() equals $newPeriod->getStartDate();
~~~

## Using durations

The supplied `DateInterval` object can be added or substracted from the starting and/or ending datepoint.

### Period::withDuration($duration)

Returns a new `Period` object by updating its duration. Only the excluded datepoint is updated.

The `$duration` parameter is expressed as a `DateInterval` object.

~~~php
use League\Period\Period;

$period    = Period::createFromMonth(2014, 3);
$newPeriod = $period->withDuration('2 WEEKS');
$period->getEndDate(); //returns DateTime('2014-04-01');
$newPeriod->getEndDate(); //returns DateTime('2014-03-16');
// $period->getStartDate() equals $newPeriod->getStartDate();
~~~

### Period::add($duration)

Returns a new `Period` object by adding an interval to the current ending excluded datepoint.

The `$duration` parameter is expressed as a `DateInterval` object.

~~~php
use League\Period\Period;

$period    = Period::createFromMonth(2014, 3);
$newPeriod = $period->add('2 WEEKS');
// $period->getStartDate() equals $newPeriod->getStartDate();
~~~

### Period::sub($duration)

Returns a new `Period` object by substracting an interval to the current ending excluded datepoint.

The `$duration` parameter is expressed as a `DateInterval` object.

~~~php
use League\Period\Period;

$period    = Period::createFromMonth(2014, 3);
$newPeriod = $period->sub('2 WEEKS');
// $period->getStartDate() equals $newPeriod->getStartDate();
~~~

### Period::next($duration = null)

<p class="message-notice">Added to <code>Period</code> in version 2.1</p>

Returns a new `Period` object adjacent to the current `Period` and starting with its ending datepoint. If no interval is provided, the new `Period` object will be created using the current `Period` duration.

~~~php
use League\Period\Period;

$period    = Period::createFromMonth(2014, 3);
$newPeriod = $period->next('1 MONTH');
// $period->getEndDate() equals $newPeriod->getStartDate();
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
// since the ending datepoint is excluded from the period!!
~~~

<p class="message-info">To remove any ambiguity, it is recommended to always provide a <code>$duration</code> when using <code>Period::next</code></p>

### Period::previous($duration = null)

<p class="message-notice">Added to <code>Period</code> in version 2.1</p>

Complementary to `Period::next`, the created `Period` object is adjacent to the current `Period` **but** its ending datepoint is equal to the starting datepoint of the current object.

~~~php
use League\Period\Period;

$period    = Period::createFromMonth(2014, 3);
$newPeriod = $period->previous('1 WEEK');
// $period->getEndDate() equals $newPeriod->Start();
$period->durationGreaterThan($newPeriod); //return true
~~~

The method must be used with the same arguments and warnings as `Period::next`.

`Period::next` and `Period::previous` methods allow to easily create adjacent Periods as shown in the graph below

![](/media/period-adjacents.png "$previous and $next are adjacent to the $period object")

~~~php
use League\Period\Period;

$current = Period::createFromMonth(2012, 1);
$prev    = $current->previous('1 MONTH');
$next    = $curent->next('1 MONTH');
~~~

### Period::split($interval)

<p class="message-notice">Added to <code>Period</code> in version 2.5</p>

This methods split a given `Period` object in smaller `Period` objects according to the given `$interval`. All returned objects must be contained or abutted to the parent `Period` object.

- The first returned `Period` will always share the same starting datepoint with the parent object.
- The last returned `Period` will always share the same ending datepoint with the parent object.
- The last returned `Period` will have a duration equal or lesser than the submitted interval.
- If `$interval` is greater than the parent `Period` interval, the method will return an array with a single `Period` whose datepoints equals those of the parent `Period`.

~~~php
use League\Period\Period;

$period = Period::createFromYear(2012);
$period_list = $period->split('1 MONTH');
count($period_list); //returns 12 with each Period object representing a full month of 2012
~~~

## Using another Period object

### Period::merge(Period $period, Period ...$periods)

Merges two or more `Period` objects by returning a new `Period` object which englobes all the submitted objects.

~~~php
use League\Period\Period;

$period = Period::createFromSemester(2012, 1);
$alt    = Period::createFromWeek(2013, 4);
$other  = Period::createFromDuration('2012-03-07 08:10:27', 86000*3);
$newPeriod = $period->merge($alt, $other);
// $newPeriod->getStartDate() equals $period->getStartDate();
// $newPeriod->getEndDate() equals $altPeriod->getEndDate();
~~~

### Period::intersect(Period $period)

An Period overlaps another if it shares some common part of the datetime continuum. This method returns the amount of the overlap as a Period object, only if they actually do overlap. If they do not overlap or abut, then an Exception is thrown.

<p class="message-info">Before getting the intersection, make sure the <code>Period</code> objects, at least, overlap each other.</p>

![](/media/period-intersect.png "$intersectPeriod represents the intersection Period between both Period object")

~~~php
use League\Period\Period;

$period        = Period::createFromDuration(2012-01-01, '2 MONTHS');
$anotherPeriod = Period::createFromDuration(2012-01-15, '3 MONTHS');
$intersectPeriod = $period->insersect($anotherPeriod);
~~~

### Period::gap(Period $period)

<p class="message-notice">Added to <code>Period</code> in version 2.2</p>

 A `Period` has a gap with another Period if there is a non-zero interval between them. This method returns the amount of the gap as a new Period object only if they do actually have a gap between them. If they overlap a Exception is thrown.

<p class="message-info">Before getting the gap, make sure the <code>Period</code> objects do not overlaps.</p>

![](/media/period-gap.png "$gapPeriod represents the gap Period between both Period objects")

~~~php
use League\Period\Period;

$orig = Period::createFromDuration(2012-01-01, '2 MONTHS');
$alt  = Period::createFromDuration(2013-01-15, '3 MONTHS');
$gapPeriod = $period->gap($alt);
~~~

### Period::diff(Period $period)

<p class="message-notice">Added to <code>Period</code> in version 2.4</p>

 This method returns the difference between two `Period` objects only if they actually do overlap. If they do not overlap or abut, then an `Exception` is thrown.

 The difference is expressed as an `array`. The returned array:

 - is empty if both objects share the same datepoints;
 - contains one `Period` object if both objects share only one datepoint;
 - contains two `Period` objects if no datepoint are shared between objects. The first `Period` datetime continuum is always entirely set before the second one;

![](/media/period-diff.png "The difference express as Period objects")

~~~php
use League\Period\Period;

$orig = Period::createFromDuration(2013-01-01, '1 MONTH');
$alt  = Period::createFromDuration(2013-01-15, '7 DAYS');
$diff = $period->diff($alt);
// $diff is an array containing 2 Period objects
// the first object is equal to new Period('2013-01-01', '2013-01-15');
// the second object is equal to new Period('2013-01-23', '2013-02-01');
$diff[0]->isBefore($diff[1]); //return true;
//this is always true when two Period objects are present
~~~

<p class="message-info">Before getting the difference, make sure the <code>Period</code> objects, at least, overlap each other.</p>
