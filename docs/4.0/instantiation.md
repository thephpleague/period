---
layout: default
title: Period instantiation using named constructors or helper functions
---

# Instantiation

To instantiate a `Period` object you can rely on its constructor or on several helper functions describe below.

## The constructor

~~~php
public Period::__construct(mixed $startDate, mixed $endDate)
~~~

#### Parameters

Both `$startDate` and `$endDate` parameters represent the period datepoints.

- The `$startDate` datepoint represents **the starting included datepoint**.
- The `$endDate` datepoint represents **the ending excluded datepoint**.

`$endDate` **must be** greater or equal to `$startDate` or the instantiation will throw a `Period\Exception`.

#### Examples

~~~php
use League\Period\Period;

$period = new Period('2012-04-01 08:30:25', new DateTime('2013-09-04 12:35:21'));
~~~

## Helper functions

Apart from its constructor, to ease the class instantiation you can rely on many built in helper functions to return a new `Period` object. All helper functions are declared under the `League\Period` namespace.

~~~php
use League\Period;
use function League\Period\interval_after;

$period = Period\interval_after('YESTERDAY', '2 MINUTE');
$alt_period = interval_after('YESTERDAY', 180);

$alt_period->equals($period);
~~~

### Helper functions accepting a list of integer arguments or a datepoint

#### Using a list of integer arguments

- `$year` must be an integer;
- `$month` default to `1`;
- `$day` default to `1`;
- `$hour` default to `0`;
- `$minute` default to `0`;
- `$second` default to `0`;
- `$week` default to `1`;
- `$quarter` default to `1`;
- `$semester` default to `1`;

The time is truncated so that the duration always starts at midnight according to the date timezone.

<p class="message-notice">The week index follows the <a href="https://en.wikipedia.org/wiki/ISO_week_date" target="_blank">ISO week date</a> system. This means that the first week may be included in the previous year, conversely the last week may be included in the next year.</p>

~~~php
function League\Period\instant(int $year [, int $month = 1 [, int $day = 1 [, int $hour = 0 [, int $minute = 0 [, int $second = 0[, int $microsecond = 0]]]]]]): Period
function League\Period\second(int $year [, int $month = 1 [, int $day = 1 [, int $hour = 0 [, int $minute = 0 [, int $second = 0]]]]]): Period
function League\Period\minute(int $year [, int $month = 1 [, int $day = 1 [, int $hour = 0 [, int $minute = 0]]]]): Period
function League\Period\hour(int $year [, int $month = 1 [, int $day = 1 [, int $hour = 0]]]): Period
function League\Period\day(int $year [, int $month = 1 [, int $day = 1]]): Period
function League\Period\month(int $year [, int $month = 1]): Period
function League\Period\year(int $year): Period
function League\Period\quarter(int $year [, int $quarter = 1]): Period
function League\Period\semester(int $year [, int $semester = 1]): Period
function League\Period\iso_week(int $year [, int $week = 1]): Period
function League\Period\iso_year(int $year): Period
~~~

#### Using a datepoint

- `$datepoint`: The datepoint is truncated so that the duration starts at midnight according to the timezone at the beginning of the given datetime period.

~~~php
function League\Period\instant(mixed $datepoint): Period
function League\Period\second(mixed $datepoint): Period
function League\Period\minute(mixed $datepoint): Period
function League\Period\hour(mixed $datepoint): Period
function League\Period\day(mixed $datepoint): Period
function League\Period\month(mixed $datepoint): Period
function League\Period\year(mixed $datepoint): Period
function League\Period\quarter(mixed $datepoint): Period
function League\Period\semester(mixed $datepoint): Period
function League\Period\iso_week(mixed $datepoint): Period
function League\Period\iso_year(mixed $datepoint): Period
~~~

#### Examples

~~~php
use League\Period;

$instant = Period\instant('2012-04-01');
$alt_in = Period\instant(2012, 4, 1);
$alt_in->equals($instant); //return true;

$second = Period\second('2012-04-01 08:30:25');
$alt_s  = Period\second(2012, 4, 1, 8, 30, 25);
$alt_s->equals($second); //return true;

$minute = Period\minute('2012-04-01 08:30:25');
$alt_m  = Period\minute(2012, 4, 1, 8, 30, 48);
$alt_m->equals($minute); //return true;

$hour = Period\hour('2012-04-01 08:30:25');
$alt_h = Period\hour(2012, 4, 1, 8);
$alt_h->equals($hour); //return true;

$day = Period\day(2012, 4, 1);
$day_string = Period\day('2012-04-01 08:30:25');
$alt_d = Period\day('2012-04-01');
$alt_d->equals($day); //return true;
$day_string->equals($day); //return true;

$week = Period\iso_week(2013, 23);
$alt_w  = Period\iso_week('2013-06-05');
$alt_w->equals($week); //return true;
//this period represents the 23rd week of 2013

$month = Period\month(2013, 7);
$alt_m = Period\month('2013-07-31');
$alt_m->equals($month); //return true;
//this period represents the month of July 2013

$quarter = Period\quarter(2013, 2);
$alt_q = Period\quarter('2013-05-15');
$alt_q->equals($quarter); //return true;
//this period represents the second quarter of 2013

$semester = Period\semester(2013, 2);
$alt_s    = Period\semester('2013-03-15');
$alt_s->equals($semester); //return true;
//this period represents the second semester of 2013

$year = Period\year(2013);
$alt_y = Period\year('2013-05-15');
$alt_y->equals($year); //return true;
//this period represents a the year 2013 time range

$iso_year = Period\iso_year(2013);
$alt_iy = Period\iso_year('2013-05-15');
$alt_iy->equals($iso_year); //return true;
//this period represents a the iso year 2013 time range
~~~

### Create a new instance from a datepoint and a duration

~~~php
function League\Period\interval_after(mixed $datepoint, mixed $duration): Period
function League\Period\interval_before(mixed $datepoint, mixed $duration): Period
function League\Period\interval_around(mixed $datepoint, mixed $duration): Period
~~~

- `interval_after` returns a `Period` object which starts at `$datepoint`
- `interval_before` returns a `Period` object which ends at `$datepoint`.
- `interval_around` returns a `Period` object where the given duration is simultaneously substracted from and added to the `$datepoint`.

#### Parameters

- The `$datepoint` parameter represent a time range datepoints.
- The `$duration` represents a duration.

#### Example

~~~php
$date = datepoint('2012-04-01 08:30:25');
$duration = duration('1 DAY');
$half_duration = duration('12 HOURS');

$interval_after = interval_after($date, $duration);
$interval_before = interval_before($date->add($duration), $duration);
$interval_after->equals($interval_before); //returns true
$interval_around = interval_around($date->add($half_duration), $half_duration);
$interval_around->equals($interval_before); //returns true
~~~

### Create a new instance from a DatePeriod object

~~~php
function League\Period\interval_from_dateperiod(DatePeriod $datePeriod): Period
~~~

#### Parameter

- `$datePeriod` is a `DatePeriod` object.

#### Example

~~~php
$begin = new DateTime('2012-08-01');
$end = new DateTime('2012-08-31');
$interval = new DateInterval('PT1H');

$period = interval_from_dateperiod(new DatePeriod($begin, $interval, $end));
$period->getStartDate() == $begin;
$period->getEndDate() == $end;
~~~

<p class="message-warning">If the submitted <code>DatePeriod</code> instance does not have a ending datepoint, It will trigger and <code>Period\Exception</code>.</p>