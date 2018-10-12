---
layout: default
title: Period instantiation using named constructors or helper functions
---

# Instantiation

To instantiate a `Period` object you can rely on its constructor or on several helper functions describe below.

## The constructor

### Description

~~~php
<?php

public Period::__construct(mixed $startDate, mixed $endDate)
~~~

### Parameters

Both `$startDate` and `$endDate` parameters represent the period datepoints.

- The `$startDate` datepoint represents **the starting included datepoint**.
- The `$endDate` datepoint represents **the ending excluded datepoint**.

`$endDate` **must be** greater or equal to `$startDate` or the instantiation will throw a `Period\Exception`.

### Examples

~~~php
<?php

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

### Helper functions accepting only a datepoint

~~~php
<?php

function instant(mixed $datepoint): Period
function second(mixed $datepoint): Period
function minute(mixed $datepoint): Period
function hour(mixed $datepoint): Period
~~~

#### Parameter

- `$datepoint`: The datepoint used to defined the interval. It is truncated so that the duration starts at the beginning of the given time period according to the date timezone.

#### Example

~~~php
$instant = instant('2012-04-01 08:30:25.124546');
$instant->getStartDate() === $instant->getEndDate(); //returns true
$instant->getDateInterInterval() == new DateInterval('PT0S'); //returns true

$second = second('2012-04-01 08:30:25.124546');
$alt_s  = second('2012-04-01 08:30:25');
$alt_s->equals($second); //return true;

$minute = minute('2012-04-01 08:30:25');
$alt_m  = minute('2012-04-01 08:30:00');
$alt_m->equals($minute); //return true;

$hour = hour('2012-04-01 08:30:25');
$alt_h = hour('2012-04-01 08:00:00');
$alt_h->equals($hour); //return true;
~~~

### Helper functions accepting a list of integer arguments or a datepoint

#### Using a list of integer arguments

- `$year` parameter must be a valid year;
- `$month` parameter must be a valid month, between 1 and 12, default to 1;
- `$week` parameter must be a valid week, between 1 and 53, default to 1;
- `$day` parameter must be a valid day, between 1 and 31, default to 1;

The time is truncated so that the duration always starts at midnight according to the date timezone.

<p class="message-notice">The week index follows the <a href="https://en.wikipedia.org/wiki/ISO_week_date" target="_blank">ISO week date</a> system. This means that the first week may be included in the previous year, conversely the last week may be included in the next year.</p>

<p class="message-warning">Values exceeding accepted ranges will trigger <code>Period\Exception</code></p>

~~~php
<?php

function day(int $year [, int $month = 1 [, int $day = 1]]): Period
function iso_week(int $year [, int $week = 1]): Period
function month(int $year [, int $month = 1]): Period
function quarter(int $year [, int $quarter = 1]): Period
function semester(int $year [, int $semester = 1]): Period
function year(int $year): Period
function iso_year(int $year): Period
~~~

#### Using a datepoint

- `$datepoint`: The datepoint is truncated so that the duration starts at midnight according to the timezone at the beginning of the given datetime period.

<p class="message-warning">Because we are using PHP's parser, values exceeding ranges will be added to their parent values.</p>

~~~php
<?php
function day(mixed $datepoint): Period
function iso_week(mixed $datepoint): Period
function month(mixed $datepoint): Period
function quarter(mixed $datepoint): Period
function semester(mixed $datepoint): Period
function year(mixed $datepoint): Period
function iso_year(mixed $datepoint): Period
~~~

#### Examples

~~~php
$day = day(2012, 4, 1);
$day_string = day('2012-04-01 08:30:25');
$alt_d = day('2012-04-01');
$alt_d->equals($day); //return true;
$day_string->equals($day); //return true;

$week = iso_week(2013, 23);
$alt_w  = iso_week('2013-06-05');
$alt_w->equals($week); //return true;
//this period represents the 23rd week of 2013

$month = month(2013, 7);
$alt_m = month('2013-07-31');
$alt_m->equals($month); //return true;
//this period represents the month of July 2013

$quarter = quarter(2013, 2);
$alt_q = quarter('2013-05-15');
$alt_q->equals($quarter); //return true;
//this period represents the second quarter of 2013

$semester = semester(2013, 2);
$alt_s    = semester('2013-03-15');
$alt_s->equals($semester); //return true;
//this period represents the second semester of 2013

$year = year(2013);
$alt_y = year('2013-05-15');
$alt_y->equals($year); //return true;
//this period represents a the year 2013 time range

$iso_year = iso_year(2013);
$alt_iy = iso_year('2013-05-15');
$alt_iy->equals($iso_year); //return true;
//this period represents a the iso year 2013 time range
~~~

### Create a new instance from a datepoint and a duration

~~~php
<?php

function interval_after(mixed $datepoint, mixed $duration): Period
function interval_before(mixed $datepoint, mixed $duration): Period
function interval_around(mixed $datepoint, mixed $duration): Period
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
<?php

function interval_from_dateperiod(DatePeriod $datePeriod): Period
~~~

### Parameter

- `$datePeriod` is a `DatePeriod` object.

### Example

~~~php
use League\Period\Period;

$begin = new DateTime('2012-08-01');
$end = new DateTime('2012-08-31');
$interval = new DateInterval('PT1H');

$period = interval_from_dateperiod(new DatePeriod($begin, $interval, $end));
~~~

<p class="message-warning">If the submitted <code>DatePeriod</code> instance does not have a ending datepoint, It will trigger and <code>Period\Exception</code>.</p>