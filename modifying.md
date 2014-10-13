---
layout: default
title: the Period object as an immutable value object
permalink: modifying/
---

# Modifying a Period object

The `Period` object is an **immutable value object** so any change to its property returns a new `Period` object. 

<p class="message-warning">If no <code>Period</code> object can be created the modifying methods throw a <code>LogicException</code> exception.</p>

### Period::startingOn($start)

Returns a new `Period` object with `$start` as the new **starting included endpoint** defined as a `DateTime` object.

~~~php
use League\Period\Period;

$period    = Period::createFromMonth(2014, 3);
$newPeriod = $period->startingOn('2014-02-01');
$period->getStart(); //returns DateTime('2014-03-01');
$newPeriod->getStart(); //returns DateTime('2014-02-01');
// $period->getEnd() equals $newPeriod->getEnd();
~~~

### Period::endingOn($end)

Returns a new `Period` object with `$end` as the new **ending excluded endpoint** defined as a `DateTime` object.

~~~php
use League\Period\Period;

$period    = Period::createFromMonth(2014, 3);
$newPeriod = $period->EndingOn('2014-03-16');
$period->getEnd(); //returns DateTime('2014-04-01');
$newPeriod->getEnd(); //returns DateTime('2014-03-16');
// $period->getStart() equals $newPeriod->getStart();
~~~

### Period::withDuration($duration)

Returns a new `Period` object by updating its duration. Only the excluded endpoint is updated.

The `$duration` parameter is expressed as a `DateInterval` object.

~~~php
use League\Period\Period;

$period    = Period::createFromMonth(2014, 3);
$newPeriod = $period->withDuration('2 WEEKS');
$period->getEnd(); //returns DateTime('2014-04-01');
$newPeriod->getEnd(); //returns DateTime('2014-03-16');
// $period->getStart() equals $newPeriod->getStart();
~~~

### Period::add($interval)

Returns a new `Period` object by adding an interval to the current ending excluded endpoint.

The `$interval` parameter is expressed as a `DateInterval` object.

~~~php
use League\Period\Period;

$period    = Period::createFromMonth(2014, 3);
$newPeriod = $period->add('2 WEEKS');
// $period->getStart() equals $newPeriod->getStart();
~~~

### Period::sub($interval)

Returns a new `Period` object by substracting an interval to the current ending excluded endpoint.

The `$interval` parameter is expressed as a `DateInterval` object.

~~~php
use League\Period\Period;

$period    = Period::createFromMonth(2014, 3);
$newPeriod = $period->sub('2 WEEKS');
// $period->getStart() equals $newPeriod->getStart();
~~~

### Period::merge(Period $period)

Merge two `Period` objects by returning a new `Period` object which starting endpoint is the smallest and the excluded endpoint is the biggest between both objects.

~~~php
use League\Period\Period;

$period    = Period::createFromSemester(2012, 1);
$altPeriod = Period::createFromWeek(2013, 4);
$newPeriod = $period->merge($altPeriod); 
// $newPeriod->getStart() equals $period->getStart();
// $newPeriod->getEnd() equals $altPeriod->getEnd();
~~~

### Period::intersect(Period $period)

Computes the intersection between two `Period` objects and returns a new `Period` object.

<p class="message-info">Before getting the intersection, make sure the <code>Period</code> object, at least, overlaps.</p>

~~~php
use League\Period\Period;

$period    = Period::createFromDuration(2012-01-01, '2 MONTHS');
$altPeriod = Period::createFromDuration(2012-01-15, '3 MONTHS');
if ($period->overlaps($altPeriod)) {
    $newPeriod = $period->insersect($altPeriod);
    //$newPeriod is a Period object 
}
~~~
