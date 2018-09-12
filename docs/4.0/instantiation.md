---
layout: default
title: Period instantiation using named constructors
---

# Instantiation

To instantiate a `Period` object you can rely on its constructor or on several helper functions describe below.

## The default constructor

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

## Create an instance from a DatePeriod object.

### Description

~~~php
<?php

public Period::fromDatePeriod(DatePeriod $datePeriod): Period
~~~

### Parameters

The `$datePeriod` is a `DatePeriod` object.

### Examples

~~~php
<?php

use League\Period\Period;

$begin = new DateTime('2012-08-01');
$end = new DateTime('2012-08-31');
$interval = new DateInterval('PT1H');

$period = Period::fromDatePeriod(new DatePeriod($begin, $interval, $end));
~~~

<p class="message-warning">If the submitted `DatePeriod` instance does not have a ending datepoint, It will trigger and exception.</p>


## Helper functions

Apart from its constructor, to ease the class instantiation you can rely on many built in helper functions to return a new `Period` object. All helper functions are declared under the `League\Period` namespace.

### second

~~~php
<?php

function second(mixed $datepoint): Period
~~~

#### Parameter

- `$datepoint`: The datepoint is truncated so that the duration starts at the beginning of the given second according to the date timezone.

#### Example

~~~php
$period = second('2012-04-01 08:30:25.124546');
$alt    = second('2012-04-01 08:30:25');
$alt->equals($period); //return true;
~~~

### minute

~~~php
<?php

function minute(mixed $datepoint): Period
~~~

#### Parameter

- `$datepoint`: The datepoint is truncated so that the duration starts at the beginning of the given minute according to the date timezone.

#### Example

~~~php
$period = minute('2012-04-01 08:30:25');
$alt    = minute('2012-04-01 08:30:00');
$alt->equals($period); //return true;
~~~

### hour

~~~php
<?php

function hour(mixed $datepoint): Period
~~~

#### Parameter

- `$datepoint`: The datepoint is truncated so that the duration starts at the beginning of the given hour according to the date timezone.

#### Example

~~~php
$period = hour('2012-04-01 08:30:25');
$alt    = hour('2012-04-01 08:00:00');
$alt->equals($period); //return true;
~~~

### day

~~~php
<?php

function day(mixed $datepoint): Period
~~~

#### Parameter

- `$datepoint`: The datepoint is truncated so that the duration starts at midnight according to the date timezone.

#### Example

~~~php
$period = day('2012-04-01 08:30:25');
$alt    = day('2012-04-01');
$alt->equals($period); //return true;
~~~

### iso_week

~~~php
<?php

function iso_week(int $year, int $week): Period
function iso_week(mixed $datepoint): Period
~~~


#### Parameters

- `$year` parameter must be a valid year;
- `$week` parameter must be a valid week (between 1 and 53);

**or**

- `$datepoint`: **a datepoint included in the returned week interval** according to its timezone.


#### Example

~~~php
$period = iso_week(2013, 23);
$alt    = iso_week('2013-06-05');
$alt->equals($period); //return true;
//this period represents the 23rd week of 2013
~~~

<p class="message-notice">The week index follows the <a href="https://en.wikipedia.org/wiki/ISO_week_date" target="_blank">ISO week date</a> system. This means that the first week may be included in the previous year, conversely the last week may be included in the next year.</p>

### month

~~~php
<?php

function month(int $year, int $month): Period
function month(mixed $datepoint): Period
~~~

#### Parameters

- The `$year` parameter must be a valid year;
- The `$month` parameter must be a valid month (between 1 and 12);

**or**

- The `$datepoint` represents **a datepoint included in the returned month interval** according to its timezone.

#### Example

~~~php
month(2013, 7);
$alt = month('2013-07-31');
$alt->equals($period); //return true;
//this period represents the month of July 2013
~~~

### quarter

~~~php
<?php

function quarter(int $year, int $quarter): Period
function quarter(mixed $datepoint): Period
~~~

#### Parameters

- The `$year` parameter must be a valid year;
- The `$quarter` parameter must be a valid quarter index (between 1 and 4);

**or**

- The `$datepoint` represents **a datepoint included in the returned quarter interval** according to its timezone.

#### Example

~~~php
$period = quarter(2013, 2);
$alt    = quarter('2013-05-15');
$alt->equals($period); //return true;
//this period represents the second quarter of 2013
~~~

### semester

~~~php
<?php

function semester(int $year, int $semester): Period
function semester(mixed $datepoint): Period
~~~

#### Parameters

- The `$year` parameter must be a valid year;
- The `$semester` parameter must be a valid semester index (between 1 and 2);

**or**

- The `$datepoint` represents **a datepoint included in the returned semester interval** according to its timezone.

#### Example

~~~php
$period = semester(2013, 2);
$alt    = semester('2013-03-15');
$alt->equals($period); //return true;
//this period represents the second semester of 2013
~~~

### year

~~~php
<?php

function year(int $year): Period
function year(mixed $datepoint): Period
~~~

#### Parameter

- The `$int_or_datepoint` parameter must be a valid year;

**or**

- The `$datepoint` represents **a datepoint included in the returned year interval** according to its timezone.

#### Example

~~~php
$period = function year(2013);
$alt    = function year('2013-05-15');
$alt->equals($period); //return true;
//this period represents a time range for 2013
~~~

### iso_year

~~~php
<?php

function iso_year(int $year): Period
function iso_year(mixed $datepoint): Period
~~~

#### Parameter

- The `$int_or_datepoint` parameter must be a valid year;

**or**

- The `$datepoint` represents **a datepoint included in the returned iso year interval** according to its timezone.

#### Example

~~~php
$period = function iso_year(2013);
$alt    = function iso_year('2013-05-15');
$alt->equals($period); //return true;
//this period represents a time range for 2013
~~~

### Create a new instance from a datepoint and a duration

~~~php
<?php

function interval_after(mixed $datepoint, mixed $duration): Period
function interval_before(mixed $datepoint, mixed $duration): Period
function interval_around(mixed $datepoint, mixed $duration): Period
~~~

- `interval_after` returns a `Period` object which starts at `$startDate`
- `interval_before` returns a `Period` object which ends at `$endDate`.
- `interval_around` returns a `Period` object where the given duration is simultaneously substracted from and added to the datepoint.

#### Parameters

- The `$datepoint` parameter represent a time range datepoints.
- The `$duration` represents a duration.

#### Example

~~~php
$period = interval_after('2012-04-01 08:30:25', '1 DAY');
$alt    = interval_before('2012-04-02 08:30:25', new DateInterval('P1D'));
$alt->equals($period); //returns true
~~~