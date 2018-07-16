---
layout: default
title: Period instantiation using named constructors
---

# Instantiation

To instantiate a `Period` object you can rely on its constructor or on several named constructors describe below.

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

## Named constructors

Apart from its constructor, to ease the class instantiation you can rely on many built in named constructors to return a new `Period` object.

### Period::createFromSecond

~~~php
<?php

public static Period::createFromSecond(mixed $datepoint): Period
~~~

#### Parameter

- `$datepoint`: The datepoint is truncated so that the duration starts at the beginning of the given second according to the date timezone.

#### Example

~~~php
<?php

use League\Period\Period;

$period = Period::createFromSecond('2012-04-01 08:30:25.124546');
$alt    = Period::createFromSecond('2012-04-01 08:30:25');
$alt->sameValueAs($period); //return true;
~~~

### Period::createFromMinute

~~~php
<?php

public static Period::createFromMinute(mixed $datepoint): Period
~~~

#### Parameter

- `$datepoint`: The datepoint is truncated so that the duration starts at the beginning of the given minute according to the date timezone.

#### Example

~~~php
<?php

use League\Period\Period;

$period = Period::createFromMinute('2012-04-01 08:30:25');
$alt    = Period::createFromMinute('2012-04-01 08:30:00');
$alt->sameValueAs($period); //return true;
~~~

### Period::createFromHour

~~~php
<?php

public static Period::createFromHour(mixed $datepoint): Period
~~~

#### Parameter

- `$datepoint`: The datepoint is truncated so that the duration starts at the beginning of the given hour according to the date timezone.

#### Example

~~~php
<?php

use League\Period\Period;

$period = Period::createFromHour('2012-04-01 08:30:25');
$alt    = Period::createFromHour('2012-04-01 08:00:00');
$alt->sameValueAs($period); //return true;
~~~

### Period::createFromDay

~~~php
<?php

public static Period::createFromDay(mixed $datepoint): Period
~~~

#### Parameter

- `$datepoint`: The datepoint is truncated so that the duration starts at midnight according to the date timezone.

#### Example

~~~php
<?php

use League\Period\Period;

$period = Period::createFromDay('2012-04-01 08:30:25');
$alt    = Period::createFromDay('2012-04-01');
$alt->sameValueAs($period); //return true;
~~~

### Period::createFromWeek

~~~php
<?php

public static Period::createFromWeek(int $year, int $week): Period
public static Period::createFromWeek(mixed $datepoint): Period
~~~


#### Parameters

- `$year` parameter must be a valid year;
- `$week` parameter must be a valid week (between 1 and 53);

**or**

- `$datepoint`: **a datepoint included in the returned week interval** according to its timezone.


#### Example

~~~php
<?php

use League\Period\Period;

$period = Period::createFromWeek(2013, 23);
$alt    = Period::createFromWeek('2013-06-05');
$alt->sameValueAs($period); //return true;
//this period represents the 23rd week of 2013
~~~

<p class="message-notice">The week index follows the <a href="https://en.wikipedia.org/wiki/ISO_week_date" target="_blank">ISO week date</a> system. This means that the first week may be included in the previous year, conversely the last week may be included in the next year.</p>

### Period::createFromMonth

~~~php
<?php

public static Period::createFromMonth(int $year, int $month): Period
public static Period::createFromMonth(mixed $datepoint): Period
~~~

#### Parameters

- The `$year` parameter must be a valid year;
- The `$month` parameter must be a valid month (between 1 and 12);

**or**

- The `$datepoint` represents **a datepoint included in the returned month interval** according to its timezone.

#### Example

~~~php
<?php

use League\Period\Period;

$period = Period::createFromMonth(2013, 7);
$alt    = Period::createFromMonth('2013-07-31');
$alt->sameValueAs($period); //return true;
//this period represents the month of July 2013
~~~

### Period::createFromQuarter

~~~php
<?php

public static Period::createFromQuarter(int $year, int $quarter): Period
public static Period::createFromQuarter(mixed $datepoint): Period
~~~

#### Parameters

- The `$year` parameter must be a valid year;
- The `$quarter` parameter must be a valid quarter index (between 1 and 4);

**or**

- The `$datepoint` represents **a datepoint included in the returned quarter interval** according to its timezone.

#### Example

~~~php
<?php

use League\Period\Period;

$period = Period::createFromQuarter(2013, 2);
$alt    = Period::createFromQuarter('2013-05-15');
$alt->sameValueAs($period); //return true;
//this period represents the second quarter of 2013
~~~

### Period::createFromSemester

~~~php
<?php

public static Period::createFromSemester(int $year, int $semester): Period
public static Period::createFromSemester(mixed $datepoint): Period
~~~

#### Parameters

- The `$year` parameter must be a valid year;
- The `$semester` parameter must be a valid semester index (between 1 and 2);

**or**

- The `$datepoint` represents **a datepoint included in the returned semester interval** according to its timezone.

#### Example

~~~php
<?php

use League\Period\Period;

$period = Period::createFromSemester(2013, 2);
$alt    = Period::createFromSemester('2013-03-15');
$alt->sameValueAs($period); //return true;
//this period represents the second semester of 2013
~~~

### Period::createFromYear

~~~php
<?php

public static Period::createFromYear(int $year): Period
public static Period::createFromYear(mixed $datepoint): Period
~~~

#### Parameter

- The `$year` parameter must be a valid year;

**or**

- The `$datepoint` represents **a datepoint included in the returned year interval** according to its timezone.

#### Example

~~~php
<?php

use League\Period\Period;

$period = Period::createFromYear(2013);
$alt    = Period::createFromYear('2013-05-15');
$alt->sameValueAs($period); //return true;
//this period represents a time range for 2013
~~~

### Create a new instance from a date and a duration

~~~php
<?php

public static Period::createFromDuration(mixed $startDate, mixed $duration): Period
public static Period::createFromDurationBeforeEnd(mixed $endDate, mixed $duration): Period
~~~

- `createFromDuration` returns a `Period` object which starts at `$startDate`
- `createFromDurationBeforeEnd` returns a `Period` object which ends at `$endDate`.

Both created `Period` objects will have a duration equals to `$duration`.

#### Parameters

- Both `$startDate` and `$endDate` parameters represent a time range datepoints.
- The `$duration` represents the time range duration.

#### Example

~~~php
<?php

use League\Period\Period;

$period = Period::createFromDuration('2012-04-01 08:30:25', '1 DAY');
$alt    = Period::createFromDurationBeforeEnd('2012-04-02 08:30:25', new DateInterval('P1D'));
$alt->sameValueAs($period); //returns true
~~~