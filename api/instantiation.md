---
layout: default
title: Instantiation using named constructors
---

# Instantiation

To instantiate a `Period` object you can rely on its constructor or on several named constructors describe below.

### Period::__construct($start, $end)

Both `$start` and `$end` parameters represent the period endpoints as `DateTime` objects. 

- The `$start` endpoint represents **the starting included endpoint**.
- The `$end` value represents **the ending excluded endpoint**. 

`$end` **must be** greater or equal to `$start` or the instantiation will throw a `LogicException`.

~~~php
use League\Period\Period;

$period = new Period('2012-04-01 08:30:25', new DateTime('2013-09-04 12:35:21'));
~~~

Apart from its constructor, to ease the class instantiation you can rely on many built in named constructors.

### Period::createFromDuration($start, $duration)

Returns a `Period` object which starts at `$start` with a duration equals to `$duration`.

- The `$start` represents **the starting included endpoint** expressed as `DateTime` object.
- The `$duration` parameter is a `DateInterval` object;

~~~php
use League\Period\Period;

$period = Period::createFromDuration('2012-04-01 08:30:25', '1 DAY');
$alt    = Period::createFromDuration('2012-04-01 08:30:25', new DateInterval('P1D'));
$other  = Period::createFromDuration(new DateTime('2012-04-01 08:30:25'), 86400);
~~~

### Period::createFromDurationBeforeEnd($end, $duration)

<p class="message-notice">Added to <code>Period</code> in version 2.3</p>

Returns a `Period` object which ends at `$end` with a duration equals to `$duration`.

- The `$end` represents **the ending excluded endpoint** expressed as `DateTime` object.
- The `$duration` parameter is a `DateInterval` object;

~~~php
use League\Period\Period;

$period = Period::createFromDurationBeforeEnd('2012-04-01 08:30:25', '1 DAY');
$alt    = Period::createFromDurationBeforeEnd('2012-04-01 08:30:25', new DateInterval('P1D'));
$other  = Period::createFromDurationBeforeEnd(new DateTimeImmutable('2012-04-01 08:30:25'), 86400);
~~~


### Period::createFromWeek($year, $week)

Returns a `Period` object with a duration of 1 week for a given year and week.

- The `$year` parameter is a valid year;
- The `$week` parameter is a selected week (between 1 and 53) according to the [ISO8601 date and time standard](http://en.wikipedia.org/wiki/ISO_week_date);

~~~php
use League\Period\Period;

$period = Period::createFromWeek(2013, 23);
//this period represents the 23rd week of 2013
~~~

### Period::createFromMonth($year, $month)

Returns a `Period` object with a duration of 1 month for a given year and month.

- The `$year` parameter is a valid year;
- The `$month` parameter is a selected month (between 1 and 12);

~~~php
use League\Period\Period;

$period = Period::createFromMonth(2013, 7);
//this period represents the month of July 2013
~~~

### Period::createFromQuarter($year, $quarter)

Returns a `Period` object with a duration of 3 months for a given year and quarter.

- The `$year` parameter is a valid year;
- The `$quarter` parameter is a selected quarter (between 1 and 4);

~~~php
use League\Period\Period;

$period = Period::createFromQuarter(2013, 2);
//this period represents the second quarter of 2013
~~~

### Period::createFromSemester($year, $semester)

Returns a `Period` object with a duration of 6 months for a given year and semester.

- The `$year` parameter is a valid year;
- The `$semester` parameter is a selected semester (between 1 and 2);

~~~php
use League\Period\Period;

$period = Period::createFromSemester(2013, 1);
//this period represents the first semester of 2013
~~~

### Period::createFromYear($year)

Returns a `Period` object with a duration of 1 year for a given year.

- The `$year` parameter is a valid year;

~~~php
use League\Period\Period;

$period = Period::createFromYear(1971);
//this period represents the year 1971
~~~

### Period::createFromTimeRange(TimeRange $timerange)

<p class="message-notice">Added to <code>Period</code> in version 2.5</p>

Returns a new `Period` object from an object implementing the `TimeRange` interface.

- The `$timerange` parameter is an object implementing the `League\Period\Interfaces\TimeRange` interface

~~~php
use League\Period\Period;
use My\Awesome\TimeRangeClass;

$timerange = new TimeRangeClass();
$period = Period::createFromTimeRange($timerange);
//works only of My\Awesome\TimeRangeClass implements League\Period\Interfaces\TimeRange
~~~