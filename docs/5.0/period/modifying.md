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

<p class="message-warning">If no <code>Period</code> object can be created the modifying methods throw a <code>Period\IntervalError</code> exception.</p>

## Using the bounds information

### Period::boundedBy

~~~php
public Period::boundedBy(Bounds $bounds): Period
~~~

Returns a new `Period` object with a different bounds.

#### Example

~~~php
$interval = Period::fromMonth(2014, 3);
$newInterval = $interval->boundedBy(Bounds::IncludeAll);
$interval->toIso80000('Y-m-d');    // '[2014-03-01, 2014-04-01)'
$newInterval->toIso80000('Y-m-d'); // '[2014-03-01, 2014-04-01]'
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

## Snapping the Period object

The following methods returns a new instance which snaps the start datepoint and ending datepoint to the nearest time unit interval.

~~~php
public Period::snapToSecond(): Period
public Period::snapToMinute(): Period
public Period::snapToHour(): Period
public Period::snapToDay(): Period
public Period::snapToIsoWeek(): Period
public Period::snapToMonth(): Period
public Period::snapToQuarter(): Period
public Period::snapToSemester(): Period
public Period::snapToYear(): Period
public Period::snapToIsoYear(): Period
~~~

### Examples

~~~php
$period = Period::fromDate('2022-01-08 09:44:38', '2022-01-08 09:45:01');
echo 'Period::toIso80000 '.$period->toIso80000('Y-m-d H:i:s');
echo 'Period::snapToSecond: '.$period->snapToSecond()->toIso80000('Y-m-d H:i:s');
echo 'Period::snapToMinute: '.$period->snapToMinute()->toIso80000('Y-m-d H:i:s');
echo 'Period::snapToHour: '.$period->snapToHour()->toIso80000('Y-m-d H:i:s');
echo 'Period::snapToDay: '.$period->snapToDay()->toIso80000('Y-m-d H:i:s');
echo 'Period::snapToIsoWeek: '.$period->snapToIsoWeek()->toIso80000('Y-m-d H:i:s');
echo 'Period::snapToMonth: '.$period->snapToMonth()->toIso80000('Y-m-d H:i:s');
echo 'Period::snapToQuarter: '.$period->snapToQuarter()->toIso80000('Y-m-d H:i:s');
echo 'Period::snapToSemester: '.$period->snapToSemester()->toIso80000('Y-m-d H:i:s');
echo 'Period::snapToYear: '.$period->snapToYear()->toIso80000('Y-m-d H:i:s');
echo 'Period::snapToIsoYear: '.$period->snapToIsoYear()->toIso80000('Y-m-d H:i:s');
~~~

Here's The results of each line:


| Period methods           | Results                                      |
|--------------------------|----------------------------------------------|
| `Period::toIso80000`     | `[2022-01-08 09:44:38, 2022-01-08 09:45:01)` |
| `Period::snapToSecond`   | `[2022-01-08 09:44:38, 2022-01-08 09:45:02)` | 
| `Period::snapToMinute`   | `[2022-01-08 09:44:00, 2022-01-08 09:46:00)` |
| `Period::snapToHour`     | `[2022-01-08 09:00:00, 2022-01-08 10:00:00)` |
| `Period::snapToDay`      | `[2022-01-08 00:00:00, 2022-01-09 00:00:00)` |
| `Period::snapToIsoWeek`  | `[2022-01-03 00:00:00, 2022-01-10 00:00:00)` | 
| `Period::snapToMonth`    | `[2022-01-01 00:00:00, 2022-02-01 00:00:00)` |
| `Period::snapToQuarter`  | `[2022-01-01 00:00:00, 2022-04-01 00:00:00)` | 
| `Period::snapToSemester` | `[2022-01-01 00:00:00, 2022-07-01 00:00:00)` |
| `Period::snapToYear`     | `[2022-01-01 00:00:00, 2023-01-01 00:00:00)` |
| `Period::snapToIsoYear`  | `[2022-01-03 00:00:00, 2023-01-02 00:00:00)` | 
