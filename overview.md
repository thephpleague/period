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

<p class="message-info">To ease instantiation the class comes with many <a href="/instantiation/">named constructors</a>.</p>

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

$period = new Period('2012-04-01 08:30:25', new DateTime('2013-09-04 12:35:21'));
$period->getStart(); //returns DateTime('2012-04-01 08:30:25');
$period->getEnd(); //returns DateTime('2013-09-04 12:35:21');
$duration = $period->getDuration(); //returns a DateInterval object
$altduration = $period->getDuration(true); //returns the interval as expressed in seconds
~~~

### Period::getRange($interval)

Returns a `DatePeriod` object that lists `DateTime` objects inside the period separated by the given `$interval` expressed as a `DateInterval` object.

~~~php
use League\Period\Period;

$period = new Period('2012-01-01', '2013-01-01');
foreach ($period->getRange('1 MONTH') as $datetime) {
    echo $datetime->format('F, Y');
}
//will iterate 12 times
~~~