---
layout: default
title: Examples
---

# Overview

[![Author](//img.shields.io/badge/author-@nyamsprod-blue.svg?style=flat-square)](//twitter.com/nyamsprod)
[![Latest Version](//img.shields.io/github/release/thephpleague/period.svg?style=flat-square)](//github.com/thephpleague/period/releases)
[![Software License](//img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Total Downloads](//img.shields.io/packagist/dt/league/period.svg?style=flat-square)](//packagist.org/packages/league/period)

`Period` is PHP's missing time range API. this package cover all basic operations regarding time ranges.

<p class="message-info">On usage, always typehint against the <code>League\Period\Period</code> class directly because it is an immutable value object marked as final and no interface is provided by the library.</p>

## Provides multiple ways to instantiate depending on your context

~~~php
use League\Period\Bounds;
use League\Period\Period;

$period1 = Period::fromMonth(2014, 10, Bounds::ExcludeAll);
$period2 = Period::after('2014-10-01', new DateInterval('P1M'), Bounds::ExcludeAll);
$period3 = Period::fromIso80000('!Y-m-d', '(2014-10-01 , 2014-11-01)');
$period4 = Period::fromIso8601('!Y-m-d', '2014-10-01/2014-11-01', Bounds::ExcludeAll);
~~~

All the above calls will end up creating `Period` instances that are equals. The package comes bundle with even more [named constructors](/5.0/period/).

## Accessing the VO properties

~~~php
use League\Period\Period;

$period = Period::fromIso80000('!Y-m-d', '[2014-10-03 08:12:37,2014-10-03 08:12:37)');
$period->startDate;        //returns a DateTimeImmutable
$period->endDate;          //returns a DateTimeImmutable
$period->bounds;           //returns a League\Period\Bounds enum
$period->dateInterval(); //returns a DateInterval object
$period->timeDuration(); //returns the duration in seconds
echo $period->toIso8601(); //displays '2014-10-03T08:12:37Z/2014-10-03T09:12:37Z'
~~~

Learn more about how this all works in the [basic usage](/5.0/period/properties/).

## Iterate over the interval

Access a range of all days from a selected month as `DateTimeImmutable` objects.

~~~php
foreach (Period::fromMonth(2014, 10)->dateRangeForward(new DateInterval('P1D')) as $datepoint) {
    $datepoint->format('Y-m-d'); //$datepoint is a DateTimeImmutable object
}
~~~

Access a range of all days from a selected month as `Period` instances.

~~~php
foreach (Period::fromMonth(2014, 10)->splitForward('1 DAY') as $day) {
    $day->toIso80000('Y-m-d'); // $day is a Period instance which covers each day of the month.
}
~~~

## Comparing intervals

~~~php
$period = Period::after(new DateTime('2014-01-01'), '1 MONTH', Bounds::IncludeAll);
$altPeriod = Period::after(new DateTimeImmutable('2014-01-01'), new DateInterval('P1M'), Bounds::ExcludeAll);
$period->durationEquals($altPeriod); //returns true
$period->equals($altPeriod);         //returns false
$period->contains($altPeriod);       //returns true
$altPeriod->contains($period);       //return false
$period->contains('2014-01-10');     //returns true
DatePoint::fromDateString('2014-02-10')->isDuring($period); //returns false
~~~

The class comes with other ways to [compare time ranges](/5.0/period/comparing/) based on their duration and/or their datepoints.

## Modifying interval

~~~php
$period = Period::after('2014-01-01', '1 WEEK');
$altPeriod = $period->endingOn('2014-02-03');
$period->contains($altPeriod); //return false;
$altPeriod->durationGreaterThan($period); //return true;
~~~

`Period` is an immutable value object. Any changes to the object returns a new object. The class has more [modifying methods](/5.0/period/modifying/).

## Accessing all gaps between intervals

~~~php
$sequence = new Sequence(
    Period::fromIso80000('!Y-m-d', '[2018-01-01,2018-01-31)'),
    Period::fromIso80000('!Y-m-d', '[2017-01-01,2017-01-31)'),
    Period::fromIso80000('!Y-m-d', '[2020-01-01,2020-01-31)')
);
$gaps = $sequence->gaps(); // a new Sequence object
count($gaps); // 2
~~~

`Sequence` is a `Period` container and collection. The class has more [methods](/5.0/sequence/).

## Drawing the interactions between Period instances

~~~php
<?php

use League\Period\Chart;
use League\Period\Period;
use League\Period\Sequence;

$dataset = new Chart\Dataset([
    ['period', Period::fromIso80000('!Y-m-d', '[2018-01-01, 2018-02-01)')],
    ['sequence', new Sequence(
       Period::fromIso80000('!Y-m-d', '[2018-01-15, 2018-01-18)'),
       Period::fromIso80000('!Y-m-d', '[2018-01-20, 2018-02-01)')
    )],
]);
Chart\GanttChart::create()->stroke($dataset);
~~~

results:

~~~bash
 period   [----------------------------------------------------------)
 sequence                            [----)   [----------------------)
~~~

The classes under the `League\Period\Chart` namespace allows drawing all interactions
around `Period` instances. You can learn more by looking at the [drawing documentation](/5.0/chart)
