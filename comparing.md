---
layout: default
title: Comparing Period objects
permalink: comparing/
---

# Comparing Period objects

The following methods help you compare different `Period` objects according to their endpoints or durations.

## Using endpoints

### Period::sameValueAs(Period $period)

Tells whether two `Period` objects shares the same endpoints.

~~~php
use League\Period\Period;

$orig  = Period::createFromMonth(2014, 3);
$alt   = Period::createFromMonth(2014, 4);
$other = Period::createFromDuration('2014-03-01', '1 MONTH');

$orig->sameValueAs($alt);   //return false
$orig->sameValueAs($other); //return true
~~~

### Period::abuts(Period $period)

<p class="message-notice">Added to <code>Period</code> in version 2.2</p>

Tells whether two `Period` objects abuts

~~~php
use League\Period\Period;

$orig  = Period::createFromMonth(2014, 3);
$alt   = Period::createFromMonth(2014, 4);
$orig->abuts($alt); //return true
//in this case $orig->getEnd() == $alt->getStart();
~~~

### Period::overlaps(Period $period)

Tells whether two `Period` objects overlap each other or not.

~~~php
use League\Period\Period;

$orig  = Period::createFromMonth(2014, 3);
$alt   = Period::createFromMonth(2014, 4);
$other = Period::createFromDuration('2014-03-15', '3 WEEKS');

$orig->overlaps($alt);   //return false
$orig->overlaps($other); //return true
$alt->overlaps($other);  //return true
~~~

### Period::contains($index)

Tells whether `$index` is contained within the `Period` or not. 

`$index` can be:

- a `Period` object
- a `DateTime` object.

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
 
### Period::isBefore($index)

<p class="message-notice">Added to <code>Period</code> in version 2.2</p>

Tells whether the current `Periodf` object is entirely before the specified `$index`. The `index` can be another `Period` object or a `DateTime` .

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

Tells whether the current `Periodf` object is entirely after the specified `$index`. The `index` can be another `Period` object or a `DateTime` .

~~~php
use League\Period\Period;

//comparing a datetime
$period = Period::createFromMonth(1983, 4);
$alt = Period::createFromMonth(1984, 4);
$alt->isAfter($period); //returns true;
$period->isAfter($alt); //return false;
~~~

## Using durations

### Period::durationDiff(Period $period, $get_as_seconds = false)

Returns the difference between two `Period` durations. If the `$get_as_seconds` parameter is used and set to `true`, the method will return an integer which represents the duration in seconds instead of a `DateInterval` object.

~~~php
use League\Period\Period;

$period    = Period::createFromSemester(2012, 1);
$altPeriod = Period::createFromWeek(2012, 4);
$diff = $period->durationDiff($altPeriod);
// $diff is a DateInterval object
$diff_as_seconds = $period->durationDiff($altPeriod, true);
//$diff_as_seconds represents the interval expressed in seconds
~~~

### Period::compareDuration(Period $period)

Compare two `Period` objects according to their duration.

- Return `1` if the current object duration is greater than the submitted `$period` duration;
- Return `-1` if the current object duration is less than the submitted `$period` duration;
- Return `0` if the current object duration is equal to the submitted `$period` duration;

To ease the method usage you can rely on the following alias methods which return boolean values:

- **Period::durationGreaterThan(Period $period)** return `true` when `Period::compareDuration(Period $period)` returns `1`;
- **Period::durationLessThan(Period $period)** return `true` when `Period::compareDuration(Period $period)` returns `-1`;
- **Period::sameDurationAs(Period $period)** return `true` when `Period::compareDuration(Period $period)` returns `0`;

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
//the duration between $orig and $other are equals but not the endpoints!!
~~~