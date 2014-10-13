---
layout: default
title: Overview
permalink: overview/
---

# Overview

## Instantiation

### Period::__construct($start, $end)

Both `$start` and `$end` parameters represent the period endpoints as `DateTime` objects. 

- The `$start` endpoint represents **the starting included endpoint**.
- The `$end` value represents **the ending excluded endpoint**. 

`$end` **must be** greater or equal to `$start` or the instantiation will throw a `LogicException`. 

~~~php
use League\Period\Period;

$period = new Period('2012-04-01 08:30:25', new DateTime('2013-09-04 12:35:21'));
~~~

<p class="message-info">To ease instantiation the class comes with many named constructors.</p>

### Period::createFromDuration($start, $duration)

returns a `Period` object which starts at `$start` with a duration equals to `$duration`

- The `$start` represents **the starting included endpoint** expressed as `DateTime` object.
- The `$duration` parameter is a `DateInterval` object;

~~~php
use League\Period\Period;

$period = Period::createFromDuration('2012-04-01 08:30:25', '1 DAY');
$alt    = Period::createFromDuration('2012-04-01 08:30:25', new DateInterval('P1D'));
$other  = Period::createFromDuration(new DateTime('2012-04-01 08:30:25'), 86400);
~~~

### Period::createFromWeek($year, $week)

returns a `Period` object with a duration of 1 week for a given year and week.

- The `$year` parameter is a valid year;
- The `$week` parameter is a selected week (between 1 and 53) according to the [ISO8601 date and time standard](http://en.wikipedia.org/wiki/ISO_week_date);

~~~php
use League\Period\Period;

$period = Period::createFromWeek(2013, 23);
//this period represents the 23rd week of 2013
~~~

### Period::createFromMonth($year, $month)

returns a `Period` object with a duration of 1 month for a given year and month. 

- The `$year` parameter is a valid year;
- The `$month` parameter is a selected month (between 1 and 12);

~~~php
use League\Period\Period;

$period = Period::createFromMonth(2013, 7);
//this period represents the month of July 2013
~~~

### Period::createFromQuarter($year, $quarter)

returns a `Period` object with a duration of 3 months for a given year and quarter. 

- The `$year` parameter is a valid year;
- The `$quarter` parameter is a selected quarter (between 1 and 4);

~~~php
use League\Period\Period;

$period = Period::createFromQuarter(2013, 2);
//this period represents the second quarter of 2013
~~~

### Period::createFromTrimester($year, $trimester)

returns a `Period` object with a duration of 4 months for a given year and trimester. 

- The `$year` parameter is a valid year;
- The `$trimester` parameter is a selected semester (between 1 and 3);

~~~php
use League\Period\Period;

$period = Period::createFromTrimester(2011, 1);
//this period represents the first trimester of 2013
~~~

### Period::createFromSemester($year, $semester)

returns a `Period` object with a duration of 6 months for a given year and semester. 

- The `$year` parameter is a valid year;
- The `$semester` parameter is a selected semester (between 1 and 2);

~~~php
use League\Period\Period;

$period = Period::createFromSemester(2011, 1);
//this period represents the first semester of 2013
~~~

### Period::createFromYear($year)

returns a `Period` object with a duration of 1 year for a given year.

- The `$year` parameter is a valid year;

~~~php
use League\Period\Period;

$period = Period::createFromYear(1971);
//this period represents the year 1971
~~~

## Accessing properties

Once you have a instantiated `Period` object you can access its properties using getter methods:

### Period::getStart()

Returns the starting **included** endpoint as a `DateTime`.

### Period::getEnd();

Returns the ending **excluded** endpoint as a `DateTime`.

### Period::getDuration($get_as_seconds = false)

Returns the `Period` duration. If the `$get_as_seconds` parameter is used and set to `true`, the method will return an integer which represents the duration in seconds instead of a `DateInterval` object.

~~~php
use League\Period\Period;

$period = Period::createFromMonth(1983, 4);
$period->getStart(); //returns DateTime('1983-04-01');
$period->getEnd(); //returns DateTime('1983-05-01');
$duration = $period->getDuration(); //returns a DateInterval object
$altduration = $period->getDuration(true); //returns the interval as expressed in seconds
~~~

### Period::getRange($interval)

Returns a `DatePeriod` object that lists `DateTime` objects inside the period separated by the given `$interval` expressed as a `DateInterval` object.

~~~php
use League\Period\Period;

$period  = Period::createFromYear(1971);
foreach ($period->getRange('1 MONTH') as $datetime) {
    echo $datetime->format('F, Y');
}
//will iterate 12 times
~~~