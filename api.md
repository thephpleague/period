---
layout: default
title: Period cheat sheet
permalink: api/
---

# API Cheat Sheet

## Period instantiation

Learn more about how this all works in the [Instantiaton](/api/instantiation/) section.

__Using the Constructor__

~~~php
use League\Period\Period;

$period = new Period(new DateTime('2014-10-15 08:35:26'), '2014-10-15 08:53:12');
~~~

__Using named constructors__

~~~php
$period = Period::createFromYear(2014);        //a full year time range
~~~

~~~php
$period = Period::createFromSemester(2014, 1); //the first semester of 2014
~~~

~~~php
$period = Period::createFromQuarter(2014, 1);  //the first quarter of 2014
~~~

~~~php
$period = Period::createFromMonth(2014, 1);    //the month of January 2014
~~~

~~~php
$period = Period::createFromWeek(2014, 1);     //the first week of 2014
~~~

~~~php
$period = Period::createFromDuration('2014-01-01 08:00:25', 3600);
//a 1 hour time range starting at '2014-01-01 08:00:25'
~~~

~~~php
$period = Period::createFromDurationBeforeEnd('2014-01-01 08:00:25', 3600);
//a 1 hour time range ending at '2014-01-01 08:00:25'
~~~

## Period properties

Learn more about how this all works in the [Basic Usage](/api/basic-usage/) section.

~~~php
$period->getStart(); //the starting inclusive endpoint as a DateTime object
~~~

~~~php
$period->getEnd();   //the ending exclusive endpoint as a DateTime object
~~~

~~~php
$period->getDuration();     //the duration as a DateInterval object
$period->getDuration(true); //the duration expressed in seconds
~~~

~~~php
$period->getDatePeriod('1 HOUR'); //return a DatePeriod object
~~~

~~~php
$period->__toString(); //return the ISO8601 representation of the Period
~~~

## Comparing Periods

Learn more about how this all works in the [Comparing](/api/comparing/) section.

__Comparing endpoints__

~~~php
$period->contains($another_period); //return a boolean
//or
$period->contains('2014-03-02'); //return a boolean
~~~

~~~php
$period->overlaps($another_period); //return a boolean
~~~

~~~php
$period->sameValueAs($another_period); //return a boolean
~~~

~~~php
$period->abuts($another_period); //return a boolean
~~~

~~~php
$period->isBefore($another_period); //return a boolean
//or
$period->isBefore('2014-03-02'); //return a boolean
~~~

~~~php
$period->isAfter($another_period); //return a boolean
//or
$period->isAfter('2014-03-02'); //return a boolean
~~~

__Comparing durations__

~~~php
$period->durationGreaterThan($another_period); //return a boolean
~~~

~~~php
$period->durationLessThan($another_period); //return a boolean
~~~

~~~php
$period->sameDurationAs($another_period); //return a boolean
~~~

~~~php
$period->compareDuration($another_period);
//returns  1 if $period > $another_period
//returns -1 if $period < $another_period
//returns  0 if $period == $another_period
~~~

## Modifying Period

Learn more about how this all works in the [Modifying](/api/modifying/) section.

__Using endpoints__

~~~php
$new_period = $period->startingOn('2014-10-01');
~~~

~~~php
$new_period = $period->endingOn(new DateTime('2014-10-01'));
~~~

__Using duration__

~~~php
$new_period = $period->withDuration(86400);
~~~

~~~php
$new_period = $period->add('1 WEEK');
~~~

~~~php
$new_period = $period->sub(new DateInterval('P3D'));
~~~

~~~php
$new_period = $period->next();
//or
$new_period = $period->next(new DateInterval('P3D'));
~~~

~~~php
$new_period = $period->previous();
//or
$new_period = $period->previous('3 DAYS');
~~~

__Using `Period` objects__

~~~php
$new_period = $period->merge($another_period);
//or
$new_period = $period->merge($another_period, $alt_period, ...);
~~~

~~~php
$new_period = $period->intersect($another_period);
~~~

~~~php
$new_period = $period->gap($another_period);
~~~

~~~php
$arr = $period->diff($another_period);
//$arr is a array containing up to two Period objects
~~~

~~~php
$period->durationDiff($another_period);       //the difference as a DateInterval object
$period->durationDiff($another_period, true); //the difference expressed in seconds
~~~

