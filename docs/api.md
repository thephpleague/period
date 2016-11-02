---
layout: default
title: Period cheat sheet
---

# API Cheat Sheet

## Arguments

Unless stated otherwise:

- Whenever a datepoint is expected you can provide:
    - a `DateTimeInterface` implementing object;
    - a string parsable by the `DateTime` constructor.

- Whenever a duration is expected you can provide:
    - a `DateInterval` object;
    - a string parsable by the `DateInterval::createFromDateString` method.
    - an integer interpreted as the interval expressed in seconds.

## Period instantiation

Learn more about how this all works in the [Instantiaton](/api/instantiation/) section.

__Using the Constructor__

~~~php
<?php

use League\Period\Period;

$period = new Period(new DateTime('2014-10-15 08:35:26'), '2014-10-15 08:53:12');
~~~

__Using named constructors__

~~~php
<?php

Period::createFromYear(2014);        //a full year time range
Period::createFromSemester(2014, 1); //the first semester of 2014
Period::createFromQuarter(2014, 1);  //the first quarter of 2014
Period::createFromMonth(2014, 1);    //the month of January 2014
Period::createFromWeek(2014, 1);     //the first week of 2014
Period::createFromDay('NOW');        //the current day time range

Period::createFromDuration('2014-01-01 08:00:25', new DateInterval('PT1H'));
//a 1 hour time range starting at '2014-01-01 08:00:25'

$period = Period::createFromDurationBeforeEnd('2014-01-01 08:00:25', 3600);
//a 1 hour time range ending at '2014-01-01 08:00:25'
~~~

## Period properties

Learn more about how this all works in the [Properties](/api/properties/) section.

~~~php
<?php

$period->getStartDate();          //the starting inclusive datepoint as a DateTimeImmutable object
$period->getEndDate();            //the ending exclusive datepoint as a DateTimeImmutable object
$period->getDateInterval();       //the duration as a DateInterval object
$period->getTimestampInterval();  //the duration expressed in seconds
$period->getDatePeriod('1 HOUR'); //return a DatePeriod object
$period->split('1 WEEK');         //return a Generator object containing Period objects
$period->__toString();            //return the ISO8601 representation of the Period
$json = json_encode($period);     //return the json representation of the Period
~~~


## Comparing Periods

Learn more about how this all works in the [Comparing](/api/comparing/) section.

__Comparing datepoints__

~~~php
<?php

$period->sameValueAs($another_period); //return a boolean
$period->abuts($another_period); //return a boolean
$period->overlaps($another_period); //return a boolean
$period->contains($period_or_datepoint); //return a boolean
$period->isBefore($period_or_datepoint); //return a boolean
$period->isAfter($period_or_datepoint); //return a boolean
$new_period = $period->intersect($another_period);
$new_period = $period->gap($another_period);
$arr = $period->diff($another_period);
//$arr is a array containing up to two Period objects
~~~

__Comparing durations__

~~~php
<?php

$period->durationGreaterThan($another_period); //return a boolean
$period->durationLessThan($another_period); //return a boolean
$period->sameDurationAs($another_period); //return a boolean
$period->compareDuration($another_period);
//returns  1 if $period > $another_period
//returns -1 if $period < $another_period
//returns  0 if $period == $another_period

$period->dateIntervalDiff($another_period);     //the difference as a DateInterval object
$period->timestampIntervalDiff($another_period); //the difference expressed in seconds
~~~

## Modifying Period

Learn more about how this all works in the [Modifying](/api/modifying/) section.

__Using datepoints__

~~~php
<?php

$new_period = $period->startingOn('2014-10-01');
$new_period = $period->endingOn(new DateTime('2014-10-01'));
~~~

__Using duration__

~~~php
<?php

$new_period = $period->withDuration(86400);
$new_period = $period->moveStartDate('+1 HOUR');
$new_period = $period->moveEndDate('-1 WEEK');
$new_period = $period->move(new DateInterval('P3D'));
$new_period = $period->next();
$new_period = $period->previous();
~~~

__Using `Period` objects__

~~~php
<?php

$new_period = $period->merge($another_period);
~~~