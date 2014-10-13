---
layout: default
title: Usage
permalink: usage/
---

# Usage

## Arguments

Unless stated otherwise:

- Whenever a `DateTime` object is expected you can provide:
    - a `DateTime` object;
    - a string parsable by the `DateTime` constructor.

- Whenever a `DateInterval` object is expected you can provide:
    - a `DateInterval` object;
    - a string parsable by the `DateInterval::createFromDateString` method.
    - an integer interpreted as the interval expressed in seconds.

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

## Comparing Period objects

### Period::contains($index)

Tells whether `$index` is contained within the `Period` or not. 

`$index` can be:

- a `Period` object
- a `DateTime` object.

~~~php
use League\Period\Period;

//comparing a datetime
$period = Period::createFromMonth(1983, 4);
$period->contains('1983-04-15');      //returns true;
$period->contains($period->getEnd()); //returns false;

//comparing two Period objects
$alt = Period::createFromDuration(1983-04-12, '12 DAYS');
$period->contains($alt); //return true;
$alt->contains($period); //return false;
~~~
 
### Period::sameValueAs(Period $period)

Tells whether two `Period` objects shares the same endpoints.

~~~php
use League\Period\Period;

$orig  = Period::createFromMonth(2014, 3);
$alt   = Period::createFromMonth(2014, 4);
$other = Period::createFromDuration('2014-03-01', '1 MONTH');

$orig->sameValueAs($alt);   //return false
$orig->sameValueAs($other); //return true
~~~

### Period::overlaps(Period $period)

Tells whether two `Period` objects overlap each other or not.

~~~php
use League\Period\Period;

$orig  = Period::createFromMonth(2014, 3);
$alt   = Period::createFromMonth(2014, 4);
$other = Period::createFromDuration('2014-03-15', '3 WEEKS');

$orig->overlaps($alt);   //return false
$orig->overlaps($other); //return true
$alt->overlaps($other);  //return true
~~~

### Period::diff(Period $period, $get_as_seconds = false)

Returns the difference between two `Period` durations. If the `$get_as_seconds` parameter is used and set to `true`, the method will return an integer which represents the duration in seconds instead of a `DateInterval` object.

~~~php
use League\Period\Period;

$period    = Period::createFromSemester(2012, 1);
$altPeriod = Period::createFromWeek(2012, 4);
$diff = $period->diff($altPeriod);
// $diff is a DateInterval object
$diff_as_seconds = $period->diff($altPeriod, true);
//$diff_as_seconds represents the interval expressed in seconds
~~~

### Period::compareDuration(Period $period)

Compare two `Period` objects according to their duration.

- Return `1` if the current object duration is greater than the submitted `$period` duration;
- Return `-1` if the current object duration is less than the submitted `$period` duration;
- Return `0` if the current object duration is equal to the submitted `$period` duration;

To ease the method usage you can rely on the following aliases methods which return boolean values:

- **Period::durationGreaterThan(Period $period)** return `true` when `Period::compareDuration(Period $period)` returns `1`;
- **Period::durationLessThan(Period $period)** return `true` when `Period::compareDuration(Period $period)` returns `-1`;
- **Period::sameDurationAs(Period $period)** return `true` when `Period::compareDuration(Period $period)` returns `0`;

~~~php
$orig  = Period::createFromDuration('2012-01-01', '1 MONTH');
$alt   = Period::createFromDuration('2012-01-01', '1 WEEK');
$other = Period::createFromDuration('2013-01-01', '1 MONTH');

$orig->compareDuration($alt);     //return 1
$orig->durationGreaterThan($alt); //return true
$orig->durationLessThan($alt);    //return false

$alt->compareDuration($other);     //return -1
$alt->durationLessThan($other);    //return true
$alt->durationGreaterThan($other); //return false

$orig->compareDuration($other);   //return 0
$orig->sameDurationAs($other);    //return true
$orig->sameValueAs($other);       //return false
//the duration between $orig and $other are equals but not the endpoints!!
~~~
 
## Modifying a Period object

The `Period` object is an **immutable value object** so any change to its property returns a new `Period` object. 

<p class="message-warning">If no <code>Period</code> object can be created the modifying methods throw a <code>LogicException</code> exception.</p>

### Period::startingOn($start)

Returns a new `Period` object with `$start` as the new **starting included endpoint** defined as a `DateTime` object.

~~~php
use League\Period\Period;

$period    = Period::createFromMonth(2014, 3);
$newPeriod = $period->startingOn('2014-02-01');
$period->getStart(); //returns DateTime('2014-03-01');
$newPeriod->getStart(); //returns DateTime('2014-02-01');
// $period->getEnd() equals $newPeriod->getEnd();
~~~

### Period::endingOn($end)

Returns a new `Period` object with `$end` as the new **ending excluded endpoint** defined as a `DateTime` object.

~~~php
use League\Period\Period;

$period    = Period::createFromMonth(2014, 3);
$newPeriod = $period->EndingOn('2014-03-16');
$period->getEnd(); //returns DateTime('2014-04-01');
$newPeriod->getEnd(); //returns DateTime('2014-03-16');
// $period->getStart() equals $newPeriod->getStart();
~~~

### Period::withDuration($duration)

Returns a new `Period` object by updating its duration. Only the excluded endpoint is updated.

The `$duration` parameter is expressed as a `DateInterval` object.

~~~php
use League\Period\Period;

$period    = Period::createFromMonth(2014, 3);
$newPeriod = $period->withDuration('2 WEEKS');
$period->getEnd(); //returns DateTime('2014-04-01');
$newPeriod->getEnd(); //returns DateTime('2014-03-16');
// $period->getStart() equals $newPeriod->getStart();
~~~

### Period::add($interval)

Returns a new `Period` object by adding an interval to the current ending excluded endpoint.

The `$interval` parameter is expressed as a `DateInterval` object.

~~~php
use League\Period\Period;

$period    = Period::createFromMonth(2014, 3);
$newPeriod = $period->add('2 WEEKS');
// $period->getStart() equals $newPeriod->getStart();
~~~

### Period::sub($interval)

Returns a new `Period` object by substracting an interval to the current ending excluded endpoint.

The `$interval` parameter is expressed as a `DateInterval` object.

~~~php
use League\Period\Period;

$period    = Period::createFromMonth(2014, 3);
$newPeriod = $period->sub('2 WEEKS');
// $period->getStart() equals $newPeriod->getStart();
~~~

### Period::merge(Period $period)

Merge two `Period` objects by returning a new `Period` object which starting endpoint is the smallest and the excluded endpoint is the biggest between both objects.

~~~php

use League\Period\Period;

$period    = Period::createFromSemester(2012, 1);
$altPeriod = Period::createFromWeek(2013, 4);
$newPeriod = $period->merge($altPeriod); 
// $newPeriod->getStart() equals $period->getStart();
// $newPeriod->getEnd() equals $altPeriod->getEnd();
~~~

### Period::intersect(Period $period)

Computes the intersection between two `Period` objects and returns a new `Period` object.

<p class="message-info">Before getting the intersection, make sure the <code>Period</code> object, at least, overlaps.</p>

~~~php

use League\Period\Period;

$period    = Period::createFromDuration(2012-01-01, '2 MONTHS');
$altPeriod = Period::createFromDuration(2012-01-15, '3 MONTHS');
if ($period->overlaps($altPeriod)) {
    $newPeriod = $period->insersect($altPeriod);
    //$newPeriod is a Period object 
}
~~~
