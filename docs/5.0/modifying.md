---
layout: default
title: the Period object as an immutable value object
---

# Modifying Period objects

You can manipulate a `Period` object according to their bounds, datepoints or durations.

`Period` **is an immutable value object** which means that any change returns a new `Period` object.

Whenever a duration is expected the following types are supported:

- `DateInterval`
- `Period`
- `Duration`
- a `string` parsable by `DateInterval::createFromDateString`

Unless explicitly restricted, whenever a datepoint is expected the following types are supported:

- `DateTimeInterface`
- `DatePoint`
- a `string` parsable by `DateTimeImmutable::__construct`

<p class="message-warning">If no <code>Period</code> object can be created the modifying methods throw a <code>Period\DateRangeError</code> exception.</p>

## Using the bounds information

### Period::withBounds

~~~php
public Period::withBounds(Bounds $bounds): Period
~~~

Returns a new `Period` object with a different bounds.

#### Example

~~~php
$interval = Period::fromMonth(2014, 3);
$newInterval = $interval->withBounds(Bounds::INCLUDE_ALL);
$interval->toNotation('Y-m-d');    // '[2014-03-01, 2014-04-01)'
$newInterval->toNotation('Y-m-d'); // '[2014-03-01, 2014-04-01]'
~~~

## Using datepoints

### Period::startingOn

~~~php
public Period::startingOn(DatePoint|DateTimeInterface|string $datepoint): Period
~~~

Returns a new `Period` object with `$datepoint` as the new **starting datepoint**.

#### Example

~~~php
$interval = Period::fromMonth(2014, 3);
$newInterval = $interval->startingOn('2014-02-01');
$interval->startDate();    //returns DateTimeImmutable('2014-03-01');
$newInterval->startDate(); //returns DateTimeImmutable('2014-02-01');
// $interval->endDate() === $newInterval->endDate();
~~~

### Period::endingOn

~~~php
public Period::endingOn(DatePoint|DateTimeInterface|string $datepoint): Period
~~~

Returns a new `Period` object with `$datepoint` as the new **ending datepoint**.

#### Example

~~~php
$interval = Period::fromMonth(2014, 3);
$newInterval = $interval->EndingOn('2014-03-16');
$interval->endDate();    //returns DateTimeImmutable('2014-04-01');
$newInterval->endDate(); //returns DateTimeImmutable('2014-03-16');
// $interval->startDate() === $newInterval->startDate();
~~~

## Using durations

### Period::withDurationAfterStart

~~~php
public Period::withDurationAfterStart(Period|Duration|DateInterval|string $duration): Period
~~~

Returns a new `Period` object by updating its duration. Only the ending datepoint is updated.

#### Example

~~~php
$interval = Period::fromMonth(2014, 3);
$newInterval = $interval->withDurationAfterStart('2 WEEKS');
$interval->endDate();    //returns DateTimeImmutable('2014-04-01');
$newInterval->endDate(); //returns DateTimeImmutable('2014-03-16');
// $interval->startDate() === $newInterval->startDate();
~~~

### Period::withDurationBeforeEnd

~~~php
public Period::withDurationBeforeEnd(Period|Duration|DateInterval|string $duration): Period
~~~

Returns a new `Period` object by updating its duration. Only the starting datepoint is updated.

#### Example

~~~php
$interval = Period::fromMonth(2014, 3);
$newInterval = $interval->withDurationBeforeEnd('2 DAYS');
$interval->startDate();    //returns DateTimeImmutable('2014-03-01');
$newInterval->startDate(); //returns DateTimeImmutable('2014-03-30');
// $interval->endDate() === $newInterval->endDate();
~~~

### Period::move

~~~php
public Period::move(Period|Duration|DateInterval|string $duration): Period
~~~

Returns a new `Period` object where the endpoints are moved forward or backward simultaneously by a given interval.

#### Example

~~~php
$interval = Period::fromMonth(2014, 3);
$newInterval = $interval->move('1 MONTH');
$interval->startDate()     //returns DateTimeImmutable('2014-03-01');
$interval->endDate();      //returns DateTimeImmutable('2014-04-01');
$newInterval->startDate(); //returns DateTimeImmutable('2014-04-01');
$newInterval->endDate();   //returns DateTimeImmutable('2014-05-01');
~~~

### Period::moveStartDate

~~~php
public Period::moveStartDate(Period|Duration|DateInterval|string $duration): Period
~~~

Returns a new `Period` object where the starting endpoint is moved forward or backward by a given interval.

#### Example

~~~php
$interval = Period::fromMonth(2014, 3);
$newInterval = $interval->moveStartDate('-1 MONTH');
$interval->startDate()     //returns DateTimeImmutable('2014-03-01');
$interval->endDate();        //returns DateTimeImmutable('2014-04-01');
$newInterval->startDate(); //returns DateTimeImmutable('2014-02-01');
$newInterval->endDate();     //returns DateTimeImmutable('2014-04-01');
~~~

### Period::moveEndDate

~~~php
public Period::moveEndDate(Period|Duration|DateInterval|string $duration): Period
~~~

Returns a new `Period` object where the ending endpoint is moved forward or backward by a given interval.

#### Example

~~~php
$interval = Period::fromMonth(2014, 3);
$newInterval = $interval->moveEndDate('1 MONTH');
$interval->startDate()     //returns DateTimeImmutable('2014-03-01');
$interval->endDate();      //returns DateTimeImmutable('2014-04-01');
$newInterval->startDate(); //returns DateTimeImmutable('2014-03-01');
$newInterval->endDate();   //returns DateTimeImmutable('2014-05-01');
~~~

### Period::expand

~~~php
public Period::expand(Period|Duration|DateInterval|string $duration): Period
~~~

Returns a new `Period` object where the given interval is:

- subtracted from the starting endpoint
- added to the ending endpoint

#### Example

~~~php
$interval = Period::fromMonth(2014, 3);
$newInterval = $interval->expand('1 MONTH');
$interval->startDate()     //returns DateTimeImmutable('2014-03-01');
$interval->endDate();      //returns DateTimeImmutable('2014-04-01');
$newInterval->startDate(); //returns DateTimeImmutable('2014-02-01');
$newInterval->endDate();   //returns DateTimeImmutable('2014-05-01');
~~~

<p class="message-info">If you need to shrink the time range you can simply use a <code>DateInterval</code> object with negative interval.</p>

~~~php
$interval = Period::fromMonth(2014, 3);
$newInterval = $interval->expand('-1 DAY');
$interval->startDate();     //returns DateTimeImmutable('2014-03-01');
$interval->endDate();      //returns DateTimeImmutable('2014-04-01');
$newInterval->startDate(); //returns DateTimeImmutable('2014-03-02');
$newInterval->endDate();   //returns DateTimeImmutable('2014-03-31');
~~~

## Using another Period object

### Period::merge

~~~php
public Period::merge(Period ...$intervals): Period
~~~

Merges two or more `Period` objects by returning a new `Period` object which englobes all the submitted objects.

#### Example

~~~php
$interval = Period::fromSemester(2012, 1);
$alt = Period::fromIsoWeek(2013, 4);
$other = Period::after('2012-03-07 08:10:27', 86000*3);
$merge_interval = $interval->merge($alt, $other);
// $merge_interval->startDate() equals $period->startDate();
// $merge_interval->endDate( equals $altPeriod->endDate();
~~~
