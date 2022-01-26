---
layout: default
title: Comparing Period objects
---

# Comparing

You can compare different `Period` objects according to their datepoints or durations.

<p class="message-info"><code>datepoint</code> and <code>duration</code> conversions are done internally using the <a href="/4.0/datepoint">League\Period\Datepoint</a> and the <a href="/4.0/datepoint">League\Period\Duration</a> classes.</p>

## Using durations

### Sorting objects

~~~php
public Period::durationCompare(Period $interval): int
public Period::durationGreaterThan(Period $interval): bool
public Period::durationLessThan(Period $interval): bool
public Period::durationEquals(Period $interval): bool
~~~

The method `Period::durationCompare` compares two `Period` objects according to their duration. The method returns:

- `1` if the current object duration is greater than the submitted object duration;
- `-1` if the current object duration is lesser than the submitted object duration;
- `0` if the current object duration is equal to the submitted object duration;

To ease the method usage you can rely on the following proxy methods:

- `Period::durationGreaterThan` returns `true` when `Period::durationCompare` returns `1`
- `Period::durationLessThan` returns `true` when `Period::durationCompare` returns `-1`
- `Period::durationEquals` returns `true` when `Period::durationCompare` returns `0`


#### Examples

~~~php
$orig = Period::after('2012-01-01', '1 MONTH');
$alt = Period::after('2012-01-01', '1 WEEK');
$other = Period::after('2013-01-01', '1 MONTH');

$orig->durationCompare($alt);     //return 1
$orig->durationGreaterThan($alt); //return true
$orig->durationLessThan($alt);    //return false

$alt->durationCompare($other);     //return -1
$alt->durationLessThan($other);    //return true
$alt->durationGreaterThan($other); //return false

$orig->durationCompare($other);   //return 0
$orig->durationEquals($other);    //return true
$orig->equals($other);       //return false
//the duration between $orig and $other are equals but not the datepoints!!
~~~

### Returning the duration differences

~~~php
public Period::dateIntervalDiff(Period $interval): DateInterval
public Period::timestampIntervalDiff(Period $interval): float
~~~

Returns the duration difference between two Period objects using a `DateInterval` object or expressed in seconds.

#### Examples

~~~php
use League\Period\Period;

$interval = Period::fromSemester(2012, 1);
$alt_interval = Period::fromIsoWeek(2012, 4);
$diff = $interval->dateIntervalDiff($alt_interval);
// $diff is a DateInterval object
$diff_as_seconds = $interval->timeDurationDiff($alt_interval);
//$diff_as_seconds represents the interval expressed in seconds
~~~

## Using datepoints and boundary types

All following methods results take into account the interval datepoints as well as its boundary types.

### Period::isBefore

~~~php
public Period::isBefore(mixed $index): bool
~~~

Tells whether the current `Period` object datetime continuum is entirely before the specified `$index`.

#### Parameter

The `$index` argument can be another `Period` object or a datepoint.

#### Examples

~~~php
$period = Period::fromMonth(1983, 4);
$alt = Period::fromMonth(1984, 4);

//test against another Period object
$period->isBefore($alt); //returns true;
$alt->isBefore($period); //return false;

//test againts a datepoint
$period->isBefore('1983-06-02'); //returns true
$period->isBefore('1982-06-02'); //returns false
$period->isBefore($period->getEndDate()); //returns true
~~~

### Period::isDuring

<p class="message-info">Since <code>version 4.4</code></p>

~~~php
public Period::isDuring(Period $interval): bool
~~~

A `Period` is contained into another if its datetime continuum is completely contained within the submitted `Period` datetime continuum.

#### Examples

~~~php
//comparing a datetime
$period = Period::fromMonth(1983, 4);

//comparing two Period objects
$alt = Period::after('1983-04-12', '12 DAYS');
$period->contains($alt); //return true;
$alt->isDuring($period); //return true;
~~~

### Period::isAfter

~~~php
public Period::isAfter(mixed $index): bool
~~~

Tells whether the current `Period` object datetime continuum is entirely after the specified `$index`.

#### Parameter

The `$index` argument can be another `Period` object or a datepoint.

#### Examples

~~~php
$period = Period::fromMonth(1983, 4);
$alt = Period::fromMonth(1984, 4);

//test against another Period object
$alt->isAfter($period); //returns true;
$period->isAfter($alt); //return false;

//test againts a datepoint
$period->isAfter('1983-06-02'); //returns false
$period->isAfter('1982-06-02'); //returns true
$period->isAfter($period->getStartDate()); //returns false
~~~

### Period::bordersOnStart

<p class="message-info">Since <code>version 4.4</code></p>

~~~php
public Period::bordersOnStart(Period $interval): bool
~~~

A `Period` borders on the starting datepoint of another instance if its ending datepoint is immediately before the submitted `Period` starting datepoint without overlapping.

#### Examples

~~~php
//comparing a datetime
$period = Period::fromMonth(1983, 4);

//comparing two Period objects
$alt = Period::fromMonth(1983, 3);
$alt->bordersOnStart($period); //return true;
~~~

### Period::bordersOnEnd

<p class="message-info">Since <code>version 4.4</code></p>

~~~php
public Period::bordersOnEnd(Period $interval): bool
~~~

A `Period` borders on the ending datepoint of another instance if its starting datepoint is immediately after the submitted `Period` end datepoint without overlapping.

#### Examples

~~~php
//comparing a datetime
$period = Period::fromMonth(1983, 4);

//comparing two Period objects
$alt = Period::fromMonth(1983, 3);
$period->bordersOnEnd($alt); //return true;
~~~

### Period::abuts

~~~php
public Period::abuts(Period $interval): bool
~~~

A `Period` abuts if it starts immediately after, or ends immediately before the submitted `Period` without overlapping.

![](/media/period-abuts.png "$period abuts $anotherPeriod")

<p class="message-info">If <code>Period::bordersOnStart</code> or <code>Period::bordersOnEnd</code> returns <code>true</code> then <code>Period::abuts</code> will also return <code>true</code> with the same arguments.</p>

#### Examples

~~~php
$period = Period::fromMonth(2014, 3);
$alt = Period::fromMonth(2014, 4);
$period->abuts($alt); //return true
//in this case $period->getEndDate() == $alt->getStartDate();
~~~

### Period::overlaps

~~~php
public Period::overlaps(Period $interval): bool
~~~

A `Period` overlaps another if they share some common part of their respective continuous portion of time without abutting.

#### Examples

~~~php
$orig  = Period::fromMonth('2014-03-15');
$alt   = Period::fromMonth('2014-04-15');
$other = Period::after('2014-03-15', '3 WEEKS');

$orig->overlaps($alt);   //return false
$orig->overlaps($other); //return true
$alt->overlaps($other);  //return true
~~~

### Period::isStartedBy

<p class="message-info">Since <code>version 4.4</code></p>

~~~php
public Period::isStartedBy(mixed $index): bool
~~~

- Tells whether both `Period` objects starts at the same datepoint.
- Tells whethter the submitted `DateTimeInterface` object is the interval included starting datepoint 

#### Parameter

The `$index` argument can be another `Period` object or a datepoint.

#### Examples

~~~php
$period = Period::fromMonth(2014, 3);
$alt = Period::after('2014-03-01', '2 DAYS');
$period->isStartedBy($alt); //return true
//in this case $period->getStartDate() == $alt->getStartDate();
//         and $period->isStartIncluded === $alt->isStartIncluded;
~~~

### Period::isEndedBy

<p class="message-info">Since <code>version 4.4</code></p>

~~~php
public Period::isEndedBy(mixed $index): bool
~~~

- Tells whether both `Period` objects ends at the same datepoint.
- Tells whether the submitted `DateTimeInterface` object is the interval included ending datepoint 

#### Parameter

The `$index` argument can be another `Period` object or a datepoint.

#### Examples

~~~php
$period = Period::fromMonth(2014, 3);
$alt = Period::before('2014-04-01', '2 DAYS');
$period->isEndedBy($alt); //return true
//in this case $period->getEndDate() == $alt->getEndDate();
//         and $period->isEndExcluded() === $alt->isEndExcluded();
~~~

### Period::equals

~~~php
public Period::equals(Period $interval): bool
~~~

Tells whether two `Period` objects shares the same datepoints and the same boundary type.

#### Examples

~~~php
$orig  = Period::fromMonth(2014, 3);
$alt   = Period::fromMonth(2014, 4);
$other = Period::after('2014-03-01', '1 MONTH');
$otherInclusif = Period::after('2014-03-01', '1 MONTH', Period::INCLUDE_ALL);

$orig->equals($alt);   //return false
$orig->equals($other); //return true
$orig->equals($otherInclusif); //return false because the boundary are not the same
~~~

### Period::contains

~~~php
public Period::contains(mixed $index): bool
~~~

- A `Period` contains a datepoint reference if this datepoint is present in its datetime continuum.
- A `Period` contains another `Period` object if the latter datetime continuum is completely contained within the `Period` datetime continuum.

#### Parameter

The `$index` argument can be another `Period` object or a datepoint.

#### Examples

~~~php
//comparing a datetime
$period = Period::fromMonth(1983, 4);
$period->contains('1983-04-15');            //returns true;
$period->contains($period->getStartDate()); //returns true;
$period->contains($period->getEndDate());   //returns false;

//comparing two Period objects
$alt = Period::after('1983-04-12', '12 DAYS');
$period->contains($alt); //return true;
$alt->contains($period); //return false;
~~~

### Period::gap

~~~php
public function gap(Period $interval): Period
~~~

A `Period` has a gap with another Period if there is a non-zero interval between them. This method returns the amount of the gap as a new Period object only if they do actually have a gap between them. If they overlap a Exception is thrown.

<p class="message-info">Before getting the gap, make sure the <code>Period</code> objects do not overlaps.</p>

![](/media/period-gap.png "$gapPeriod represents the gap Period between both Period objects")

#### Examples

~~~php
$interval = Period::after('2012-01-01', '2 MONTHS');
$alt_interval = Period::after('2013-01-15', '3 MONTHS');
$gap = $interval->gap($alt_interval);
~~~

### Period::intersect

~~~php
public function intersect(Period $interval): Period
~~~

An Period overlaps another if it shares some common part of the datetime continuum. This method returns the amount of the overlap as a Period object, only if they actually do overlap. If they do not overlap, then an `Period\Exception` is thrown.

<p class="message-info">Before getting the intersection, make sure the <code>Period</code> objects, at least, overlap each other.</p>

![](/media/period-intersect.png "$intersectPeriod represents the intersection Period between both Period object")

#### Examples

~~~php
$interval = Period::after('2012-01-01', '2 MONTHS');
$alt_interval = Period::after('2012-01-15', '3 MONTHS');
$intersection = $interval->intersect($alt_interval);
~~~

### Period::diff

~~~php
public Period::diff(Period $interval): array
~~~

This method returns the difference between two `Period` objects only if they actually do overlap. If they do not overlap or abut, then an `Exception` is thrown.

<p class="message-info">This method complements <code>Period::intersect</code>.</p>

<p class="message-info">Before getting the difference, make sure the <code>Period</code> objects, at least, overlap each other.</p>

The difference is expressed as an `array`. The returned array always contains two values:

- both values are `null` if both interval share the same datepoints;
- contains one `Period` object and a `null` value if both objects share only one datepoint;
- contains two `Period` objects if no datepoint are shared between objects. The first `Period` datetime continuum is always entirely set before the second one;

![](/media/period-diff.png "The difference express as Period objects")

#### Examples

~~~php
$orig = Period::after('2013-01-01', '1 MONTH');
$alt  = Period::after('2013-01-15', '7 DAYS');
list($first, $last) = $orig->diff($alt);
// $diff is an array containing 2 Period objects
$first->equals(new Period('2013-01-01', '2013-01-15')); // returns true
$last->equals(new Period('2013-01-23', '2013-02-01'));  // returns true
$first->isBefore($last); //return true;
//this is always true when two Period objects are present
~~~

### Period::subtract

<p class="message-info"><code>Period::substract</code> introduced in <code>version 4.7</code></p>
<p class="message-notice">typo fixed to <code>Period::subtract</code> in <code>version 4.9</code></p>
<p class="message-warning"><code>Period::substract</code> is deprecated and will be remove in the next major release.</p>

~~~php
public Period::subtract(Period $interval): Sequence
~~~

This method returns the difference between two `Period` objects. It differs from `Period::diff` as:

- the method is **not** commutative;
- the method returns a [Sequence](/4.0/sequence/container/) object;
- the method never throws even when the instances do not overlaps;

![](/media/period-substract.png "Period subtraction")

#### Examples

~~~php
$periodA = Period::after('2000-01-01 10:00:00', '8 HOURS');
$periodB = Period::after('2000-01-01 14:00:00', '6 HOURS');
$periodC = Period::before('2019-01-03', '1 MONTH');

$sequenceAB = $periodA->subtract($periodB);
count($sequenceAB); //returns 1
$sequenceAB[0]->equals
	new Period($periodA->getStartDate(), $periodB->getStartDate())
);

$sequenceBA = $periodB->subtract($periodA);
count($sequenceBA); //returns 1
$sequenceBA[0]->equals(
	new Period($periodA->getEndDate(), $periodB->getEndDate())
);

$sequenceAC = $periodA->subtract($periodC);
count($sequenceAC); //returns 1
$sequenceAC[0]->equals($periodA); //returns true

$sequenceCA = $periodC->subtract($periodA);
count($sequenceCA); //returns 1
$sequenceCA[0]->equals($periodC); //returns true
~~~
