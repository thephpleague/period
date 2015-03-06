---
layout: default
title: Comparing Period objects
---

# Comparing Period objects

You can compare different `Period` objects according to their datepoints or durations.

## Using datepoints

### Period::isBefore($index)

<p class="message-notice">Added to <code>Period</code> in version 2.2</p>

The `$index` argument can be another `Period` object or a `DateTime` object.

Tells whether the current `Period` object datetime continuum is entirely before the specified `$index`.

~~~php
use League\Period\Period;

//comparing a datetime
$period = Period::createFromMonth(1983, 4);
$alt = Period::createFromMonth(1984, 4);
$period->isBefore($alt); //returns true;
$alt->isBefore($period); //return false;
~~~

### Period::isAfter($index)

<p class="message-notice">Added to <code>Period</code> in version 2.2</p>

The `$index` argument can be another `Period` object or a `DateTime` object.

Tells whether the current `Period` object datetime continuum is entirely after the specified `$index`.

~~~php
use League\Period\Period;

//comparing a datetime
$period = Period::createFromMonth(1983, 4);
$alt = Period::createFromMonth(1984, 4);
$alt->isAfter($period); //returns true;
$period->isAfter($alt); //return false;
~~~

### Period::abuts(Period $period)

<p class="message-notice">Added to <code>Period</code> in version 2.2</p>

A `Period` abuts if it starts immediately after, or ends immediately before the submitted `Period` without overlapping.

![](/media/period-abuts.png "$period abuts $anotherPeriod")

~~~php
use League\Period\Period;

$period        = Period::createFromMonth(2014, 3);
$anotherPeriod = Period::createFromMonth(2014, 4);
$period->abuts($anotherPeriod); //return true
//in this case $period->getEnd() == $anotherPeriod->getStart();
~~~

### Period::overlaps(Period $period)

A `Period` overlaps another if it shares some common part of the datetime continuum. This methods returns true if this is the case and the objects do not abut.

~~~php
use League\Period\Period;

$orig  = Period::createFromMonth(2014, 3);
$alt   = Period::createFromMonth(2014, 4);
$other = Period::createFromDuration('2014-03-15', '3 WEEKS');

$orig->overlaps($alt);   //return false
$orig->overlaps($other); //return true
$alt->overlaps($other);  //return true
~~~

### Period::sameValueAs(Period $period)

Tells whether two `Period` objects shares the same datepoints.

~~~php
use League\Period\Period;

$orig  = Period::createFromMonth(2014, 3);
$alt   = Period::createFromMonth(2014, 4);
$other = Period::createFromDuration('2014-03-01', '1 MONTH');

$orig->sameValueAs($alt);   //return false
$orig->sameValueAs($other); //return true
~~~

### Period::contains($index)

The `$index` argument can be another `Period` object or a `DateTime` object.

- A `Period` contains a `DateTime` if it is present in its datetime continuum.
- A `Period` contains another `Period` object if the latter datetime continuum is completely contained within the `Period` datetime continuum.

~~~php
use League\Period\Period;

//comparing a datetime
$period = Period::createFromMonth(1983, 4);
$period->contains('1983-04-15');      //returns true;
$period->contains($period->getEnd()); //returns false;

//comparing two Period objects
$alt = Period::createFromDuration(1983-04-12, '12 DAYS');
$period->contains($alt); //return true;
$alt->contains($period); //return false;
~~~

## Using durations

### Period::compareDuration(Period $period)

Compare two `Period` objects according to their duration.

- Return `1` if the current object duration is greater than the submitted `$period` duration;
- Return `-1` if the current object duration is less than the submitted `$period` duration;
- Return `0` if the current object duration is equal to the submitted `$period` duration;

To ease the method usage you can rely on the following proxy methods which return boolean values:

### Period::durationGreaterThan(Period $period)

Compare two `Period` objects according to their duration. Returns `true` when `Period::compareDuration(Period $period)` returns `1`

### Period::durationGreaterThan(Period $period)

Compare two `Period` objects according to their duration. Returns `true` when `Period::compareDuration(Period $period)` returns `-1`

### Period::sameDurationAs(Period $period)

Compare two `Period` objects according to their duration. Returns `true` when `Period::compareDuration(Period $period)` returns `0`

~~~php
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
//the duration between $orig and $other are equals but not the datepoints!!
~~~

### Period::dateIntervalDiff(Period $period)

<p class="message-notice">Added to <code>Period</code> in version 2.5 and replace de deprecated <code>Period::durationDiff</code></p>

Returns the difference between two `Period` durations as expressed as a `DateInterval` object.

### Period::timestampIntervalDiff(Period $period)

<p class="message-notice">Added to <code>Period</code> in version 2.5 and replace de deprecated <code>Period::durationDiff</code></p>

Returns the difference between two `Period` durations as expressed in seconds.

~~~php
use League\Period\Period;

$period    = Period::createFromSemester(2012, 1);
$altPeriod = Period::createFromWeek(2012, 4);
$diff = $period->dateIntervalDiff($altPeriod);
// $diff is a DateInterval object
$diff_as_seconds = $period->timestampIntervalDiff($altPeriod);
//$diff_as_seconds represents the interval expressed in seconds
~~~