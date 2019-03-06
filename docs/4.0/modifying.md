---
layout: default
title: the Period object as an immutable value object
---

# Modifying Period objects

You can manipulate a `Period` object according to their datepoints or durations.

`Period` **is an immutable value object** which means that any change returns a new `Period` object.

<p class="message-info"><code>datepoint</code> and <code>duration</code> conversions are done internally using the <a href="/4.0/datepoint">League\Period\Datepoint</a> and the <a href="/4.0/datepoint">League\Period\Duration</a> classes.</p>

<p class="message-warning">If no <code>Period</code> object can be created the modifying methods throw a <code>Period\Exception</code> exception.</p>

## Using datepoints

### Period::startingOn

~~~php
public Period::startingOn(mixed $datepoint): Period
~~~

Returns a new `Period` object with `$datepoint` as the new **starting datepoint**.

#### Example

~~~php
$interval = Period::fromMonth(2014, 3);
$new_interval = $interval->startingOn('2014-02-01');
$interval->getStartDate(); //returns DateTimeImmutable('2014-03-01');
$new_interval->getStartDate(); //returns DateTimeImmutable('2014-02-01');
// $interval->getEndDate() equals $new_interval->getEndDate();
~~~

### Period::endingOn

~~~php
public Period::endingOn(mixed $datepoint): Period
~~~

Returns a new `Period` object with `$datepoint` as the new **ending datepoint**.

#### Example

~~~php
$interval = Period::fromMonth(2014, 3);
$new_interval = $interval->EndingOn('2014-03-16');
$interval->getEndDate(); //returns DateTimeImmutable('2014-04-01');
$new_interval->getEndDate(); //returns DateTimeImmutable('2014-03-16');
// $interval->getStartDate() equals $new_interval->getStartDate();
~~~

## Using durations

### Period::withDurationAfterStart

~~~php
public Period::withDurationAfterStart(mixed $duration): Period
~~~

Returns a new `Period` object by updating its duration. Only the ending datepoint is updated.

#### Example

~~~php
$interval = Period::fromMonth(2014, 3);
$new_interval = $interval->withDurationAfterStart('2 WEEKS');
$interval->getEndDate();    //returns DateTimeImmutable('2014-04-01');
$new_interval->getEndDate(); //returns DateTimeImmutable('2014-03-16');
// $interval->getStartDate() equals $new_interval->getStartDate();
~~~

### Period::withDurationBeforeEnd

~~~php
public Period::withDurationBeforeEnd(mixed $duration): Period
~~~

Returns a new `Period` object by updating its duration. Only the starting datepoint is updated.

#### Example

~~~php
$interval = Period::fromMonth(2014, 3);
$new_interval = $interval->withDurationBeforeEnd('2 DAYS');
$interval->getStartDate();    //returns DateTimeImmutable('2014-03-01');
$new_interval->getStartDate(); //returns DateTimeImmutable('2014-03-30');
// $interval->getEndDate() equals $new_interval->getEndDate();
~~~

### Period::move

~~~php
public Period::move(mixed $duration): Period
~~~

Returns a new `Period` object where the endpoints are moved forward or backward simultaneously by a given interval.

#### Example

~~~php
$interval = Period::fromMonth(2014, 3);
$new_interval = $interval->move('1 MONTH');
$interval->getStartDate()     //returns DateTimeImmutable('2014-03-01');
$interval->getEndDate();      //returns DateTimeImmutable('2014-04-01');
$new_interval->getStartDate(); //returns DateTimeImmutable('2014-04-01');
$new_interval->getEndDate();   //returns DateTimeImmutable('2014-05-01');
~~~

### Period::moveStartDate

~~~php
public Period::moveStartDate(mixed $duration): Period
~~~

Returns a new `Period` object where the starting endpoint is moved forward or backward by a given interval.

#### Example

~~~php
$interval = Period::fromMonth(2014, 3);
$new_interval = $interval->moveStartDate('-1 MONTH');
$interval->getStartDate()     //returns DateTimeImmutable('2014-03-01');
$interval->getEndDate();      //returns DateTimeImmutable('2014-04-01');
$new_interval->getStartDate(); //returns DateTimeImmutable('2014-02-01');
$new_interval->getEndDate();   //returns DateTimeImmutable('2014-04-01');
~~~

### Period::moveEndDate

~~~php
public Period::moveEndDate(mixed $duration): Period
~~~

Returns a new `Period` object where the ending endpoint is moved forward or backward by a given interval.

#### Example

~~~php
$interval = Period::fromMonth(2014, 3);
$new_interval = $interval->moveEndtDate('1 MONTH');
$interval->getStartDate()     //returns DateTimeImmutable('2014-03-01');
$interval->getEndDate();      //returns DateTimeImmutable('2014-04-01');
$new_interval->getStartDate(); //returns DateTimeImmutable('2014-03-01');
$new_interval->getEndDate();   //returns DateTimeImmutable('2014-05-01');
~~~

### Period::expand

~~~php
public Period::expand(mixed $duration): Period
~~~

Returns a new `Period` object where the given interval is:

- substracted from the starting endpoint
- added to the ending endpoint

#### Example

~~~php
$interval = Period::fromMonth(2014, 3);
$new_interval = $interval->expand('1 MONTH');
$interval->getStartDate()     //returns DateTimeImmutable('2014-03-01');
$interval->getEndDate();      //returns DateTimeImmutable('2014-04-01');
$new_interval->getStartDate(); //returns DateTimeImmutable('2014-02-01');
$new_interval->getEndDate();   //returns DateTimeImmutable('2014-05-01');
~~~

<p class="message-info">If you need to shrink the time range you can simply use a <strong>inverted</strong> <code>DateInterval</code> object</p>

~~~php
$interval = Period::fromMonth(2014, 3);
$new_interval = $interval->expand('-1 DAY');
$interval->getStartDate();     //returns DateTimeImmutable('2014-03-01');
$interval->getEndDate();      //returns DateTimeImmutable('2014-04-01');
$new_interval->getStartDate(); //returns DateTimeImmutable('2014-03-02');
$new_interval->getEndDate();   //returns DateTimeImmutable('2014-03-31');
~~~

## Using the boundary information

### Period::withBoundaryType

<p class="message-info">Since <code>version 4.4</code></p>

~~~php
public Period::withBoundaryType(string $boundaryType): Period
~~~

Returns a new `Period` object with a different boundary type.

#### Example

~~~php
$interval = Period::fromMonth(2014, 3);
$new_interval = $interval->withBoundaryType(Period::INCLUDE_ALL);
$interval->format('Y-m-d'); // '[2014-03-01, 2014-04-01)'
$new_interval->format('Y-m-d'); // '[2014-03-01, 2014-04-01]'
~~~

## Using another Period object

### Period::merge

~~~php
public Period::merge(Period $interval, Period ...$intervals): Period
~~~

Merges two or more `Period` objects by returning a new `Period` object which englobes all the submitted objects.

#### Example

~~~php
$interval = Period::fromSemester(2012, 1);
$alt = Period::fromIsoWeek(2013, 4);
$other = Period::after('2012-03-07 08:10:27', 86000*3);
$merge_interval = $interval->merge($alt, $other);
// $merge_interval->getStartDate() equals $period->getStartDate();
// $merge_interval->getEndDate() equals $altPeriod->getEndDate();
~~~
