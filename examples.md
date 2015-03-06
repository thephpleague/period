---
layout: default
title: Examples
---

# Examples

Of note, in your code, you will always have to typehint against the `League\Period\Period` class directly. Because `League\Period\Period` is a immutable value object class, the class does not provide an interface.

## Accessing time range properties

~~~php
date_default_timezone_set('UTC');

use League\Period\Period;

$period = Period::createFromDuration('2014-10-03 08:12:37', 3600);
$start = $period->getStartDate(); //return the following DateTime: DateTime('2014-10-03 08:12:37');
$end   = $period->getEndDate(); //return the following DateTime: DateTime('2014-10-03 09:12:37');
$duration  = $period->getDateInterval(); //return a DateInterval object
$duration2 = $period->getTimestampInterval(); //return the same interval expressed in seconds.
echo $period; //displays '2014-10-03T08:12:37Z/014-10-03T09:12:37Z'
~~~

Learn more about how this all works in the [basic usage](/overview/).

## Iterate over a time range

A simple example on how to get all the days from a selected month.

~~~php
use League\Period\Period;

$period = Period::createFromMonth(2014, 10);
foreach ($period->getDatePeriod('1 DAY') as $day) {
    $day->format('Y-m-d');
}
~~~

The `Period` object comes with many [named constructors](/instantiation/) to help easily instantiate your time range.

## Comparing time ranges

~~~php
use League\Period\Period;

$period    = Period::createFromDuration('2014-01-01', '1 WEEK');
$altPeriod = Period::createFromWeek(2014, 3);
$period->sameDurationAs($altPeriod); //will return true because the duration are equals
$period->sameValueAs($altPeriod); //will return false because the endpoints differ
~~~

The class comes with other ways to [compare time ranges](/comparing/) based on their duration and/or their endpoints.

## Modifying time ranges

~~~php
use League\Period\Period;

$period    = Period::createFromDuration('2014-01-01', '1 WEEK');
$altPeriod = $period->endingOn('2014-02-03');
$period->contains($altPeriod); //return false;
$altPeriod->durationGreaterThan($period); //return true;
~~~

`Period` is an immutable value object. Any changes to the object returns a new object. The class has more [modifying methods](/modifying/).