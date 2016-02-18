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

$period = Period::createFromYear(2014);        //a full year time range
~~~

~~~php
<?php

$period = Period::createFromSemester(2014, 1); //the first semester of 2014
~~~

~~~php
<?php

$period = Period::createFromQuarter(2014, 1);  //the first quarter of 2014
~~~

~~~php
<?php

$period = Period::createFromMonth(2014, 1);    //the month of January 2014
~~~

~~~php
<?php

$period = Period::createFromWeek(2014, 1);     //the first week of 2014
~~~

~~~php
<?php

$period = Period::createFromDay('NOW');        //the current day time range
~~~

~~~php
<?php

$period = Period::createFromDuration('2014-01-01 08:00:25', new DateInterval('PT1H'));
//a 1 hour time range starting at '2014-01-01 08:00:25'
~~~

~~~php
<?php

$period = Period::createFromDurationBeforeEnd('2014-01-01 08:00:25', 3600);
//a 1 hour time range ending at '2014-01-01 08:00:25'
~~~

## Period properties

Learn more about how this all works in the [Properties](/api/properties/) section.

~~~php
<?php

$period->getStartDate(); //the starting inclusive datepoint as a DateTimeImmutable object
~~~

~~~php
<?php

$period->getEndDate();   //the ending exclusive datepoint as a DateTimeImmutable object
~~~

~~~php
<?php

$period->getDateInterval();      //the duration as a DateInterval object
$period->getTimestampInterval(); //the duration expressed in seconds
~~~

~~~php
<?php

$period->getDatePeriod('1 HOUR'); //return a DatePeriod object
~~~

~~~php
<?php

$period->split('1 WEEK'); //return a Generator object containing Period objects
~~~

~~~php
<?php

$period->__toString(); //return the ISO8601 representation of the Period
~~~

~~~php
<?php

$json = json_encode($period); //return the json representation of the Period
~~~


## Comparing Periods

Learn more about how this all works in the [Comparing](/api/comparing/) section.

__Comparing datepoints__

~~~php
<?php

$period->contains($another_period); //return a boolean
//or
$period->contains('2014-03-02'); //return a boolean
~~~

~~~php
<?php

$period->overlaps($another_period); //return a boolean
~~~

~~~php
<?php

$period->sameValueAs($another_period); //return a boolean
~~~

~~~php
<?php

$period->abuts($another_period); //return a boolean
~~~

~~~php
<?php

$period->isBefore($another_period); //return a boolean
//or
$period->isBefore('2014-03-02'); //return a boolean
~~~

~~~php
<?php

$period->isAfter($another_period); //return a boolean
//or
$period->isAfter('2014-03-02'); //return a boolean
~~~

~~~php
<?php

$arr = $period->diff($another_period);
//$arr is a array containing up to two Period objects
~~~

~~~php
<?php

$new_period = $period->intersect($another_period);
~~~

~~~php
<?php

$new_period = $period->gap($another_period);
~~~

__Comparing durations__

~~~php
<?php

$period->durationGreaterThan($another_period); //return a boolean
~~~

~~~php
<?php

$period->durationLessThan($another_period); //return a boolean
~~~

~~~php
<?php

$period->sameDurationAs($another_period); //return a boolean
~~~

~~~php
<?php

$period->compareDuration($another_period);
//returns  1 if $period > $another_period
//returns -1 if $period < $another_period
//returns  0 if $period == $another_period
~~~

~~~php
<?php

$period->dateIntervalDiff($another_period);     //the difference as a DateInterval object
$period->timestampIntervalDiff($another_period); //the difference expressed in seconds
~~~

## Modifying Period

Learn more about how this all works in the [Modifying](/api/modifying/) section.

__Using datepoints__

~~~php
<?php

$new_period = $period->startingOn('2014-10-01');
~~~

~~~php
<?php

$new_period = $period->endingOn(new DateTime('2014-10-01'));
~~~

__Using duration__

~~~php
<?php

$new_period = $period->withDuration(86400);
~~~

~~~php
<?php

$new_period = $period->add('1 WEEK');
~~~

~~~php
<?php

$new_period = $period->sub(new DateInterval('P3D'));
~~~

~~~php
<?php

$new_period = $period->next();
//or
$new_period = $period->next(new DateInterval('P3D'));
~~~

~~~php
<?php

$new_period = $period->previous();
//or
$new_period = $period->previous('3 DAYS');
~~~

__Using `Period` objects__

~~~php
<?php

$new_period = $period->merge($another_period);
~~~