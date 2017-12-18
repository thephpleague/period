---
layout: default
title: Period instantiation using named constructors
redirect_from: /installation/
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

`$endDate` **must be** greater or equal to `$startDate` or the instantiation will throw a `LogicException`.

### Examples

~~~php
<?php

use League\Period\Period;

$period = new Period('2012-04-01 08:30:25', new DateTime('2013-09-04 12:35:21'));
~~~

## Named constructors

Apart from its constructor, to ease the class instantiation you can rely on many built in named constructors to return a new `Period` object.

### Create a new instance representing a given day

<p class="message-notice">Since <code>version 3.1</code></p>

#### Description

~~~php
<?php

public static Period::createFromDay(mixed $startDate): Period
~~~

#### Parameter

The `$startDate` represents **the starting included datepoint**; The date is truncated so that the duration starts at midnight according to the date timezone.

#### Example

~~~php
<?php

use League\Period\Period;

$period = Period::createFromDay('2012-04-01 08:30:25');
$alt    = Period::createFromDay('2012-04-01');
$alt->sameValueAs($period); //return true;
~~~

<p class="message-warning">Because of a <a href="https://github.com/facebook/hhvm/issues/7237" target="_blank">bug in HHVM</a>, when using <code>createFromDay</code>, the timezone information is lost.</p>

### Create a new instance representing a given week

#### Description

~~~php
<?php

public static Period::createFromWeek(int $year, int $week): Period
~~~

#### Parameters

- The `$year` parameter must be a valid year;
- The `$week` parameter must be a valid week (between 1 and 53);

#### Example

~~~php
<?php

use League\Period\Period;

$period = Period::createFromWeek(2013, 23);
//this period represents the 23rd week of 2013
~~~

<p class="message-notice">The week index follows the <a href="https://en.wikipedia.org/wiki/ISO_week_date" target="_blank">ISO week date</a> system. This means that the first week may be included in the previous year, conversely the last week may be included in the next year.</p>

### Create a new instance representing a given month

#### Description

~~~php
<?php

public static Period::createFromMonth(int $year, int $month): Period
~~~

#### Parameters

- The `$year` parameter must be a valid year;
- The `$month` parameter must be a valid month (between 1 and 12);

#### Example

~~~php
<?php

use League\Period\Period;

$period = Period::createFromMonth(2013, 7);
//this period represents the month of July 2013
~~~

### Create a new instance representing a given quarter

#### Description

~~~php
<?php

public static Period::createFromQuarter(int $year, int $quarter): Period
~~~

#### Parameters

- The `$year` parameter must be a valid year;
- The `$quarter` parameter must be a valid quarter index (between 1 and 4);

#### Example

~~~php
<?php

use League\Period\Period;

$period = Period::createFromQuarter(2013, 2);
//this period represents the second quarter of 2013
~~~

### Create a new instance representing a given semester

#### Description

~~~php
<?php

public static Period::createFromSemester(int $year, int $semester): Period
~~~

#### Parameters

- The `$year` parameter must be a valid year;
- The `$semester` parameter must be a valid semester index (between 1 and 2);

#### Example

~~~php
<?php

use League\Period\Period;

$period = Period::createFromSemester(2013, 2);
//this period represents the second semester of 2013
~~~

### Create a new instance representing a given year

#### Description

~~~php
<?php

public static Period::createFromYear(int $year): Period
~~~

#### Parameter

The `$year` parameter must be a valid year;

#### Example

~~~php
<?php

use League\Period\Period;

$period = Period::createFromYear(2013);
//this period represents a time range for 2013
~~~

### Create a new instance from a date and a duration

#### Description

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

### Using __set_state

<p class="message-notice">Since <code>version 3.2</code></p>

PHP’s magic method `__set_state` is now supported.

~~~php
<?php

use League\Period\Period;

$period = Period::createFromDuration('2012-04-01 08:30:25', '1 DAY');
$alt = eval('return '.var_export($period, true).';');

$alt->sameValueAs($period); //returns true
~~~

<p class="message-warning">Because of a <a href="https://github.com/facebook/hhvm/issues/7235" target="_blank">bug in HHVM</a>, This method is not supported in HHVM.</p>