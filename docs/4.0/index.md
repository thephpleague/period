---
layout: default
title: Examples
redirect_from: /examples/
---

# Overview

[![Author](//img.shields.io/badge/author-@nyamsprod-blue.svg?style=flat-square)](//twitter.com/nyamsprod)
[![Latest Version](//img.shields.io/github/release/thephpleague/period.svg?style=flat-square)](//github.com/thephpleague/period/releases)
[![Software License](//img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Build Status](//img.shields.io/travis/thephpleague/period/master.svg?style=flat-square)](//travis-ci.org/thephpleague/period)
[![Total Downloads](//img.shields.io/packagist/dt/league/period.svg?style=flat-square)](//packagist.org/packages/league/period)

`Period` is PHP's missing time range API. Based on ideas from [Resolving Feature Envy in the Domain](http://verraes.net/2014/08/resolving-feature-envy-in-the-domain/) by Mathias Verraes, this package extends the concept to cover all basic operations regarding time ranges.

<p class="message-info">In your code, you will always have to typehint against the <code>League\Period\Period</code> class directly because it is a immutable value object class marked as final and the library does not provide an interface.</p>

<p class="message-info">Since <code>version 4.1</code> a <code>League\Period\Sequence</code> class is added to improve manipulating a collection of <code>Period</code> objects.</p>

<p class="message-info">Since <code>version 4.4</code> the <code>Period</code> objects supports all types of boundary with the exception of unbounded interval.</p>

<p class="message-info">Since <code>version 4.10</code> support for <code>PHP 7.1</code> is dropped.</p>

## Accessing the interval properties

~~~php
use League\Period\Period;

$interval = new Period(
    new DateTime('2014-10-03 08:12:37'),
    new DateTimeImmutable('2014-10-03 08:12:37')
    Period::INCLUDE_START_EXCLUDE_END
);
$start = $interval->startDate(); //returns a DateTimeImmutable
$end = $interval->endDate();     //returns a DateTimeImmutable
$duration = $interval->dateInterval();       //returns a DateInterval object
$duration2 = $interval->timestampInterval(); //returns the duration in seconds
echo $interval; //displays '2014-10-03T08:12:37Z/2014-10-03T09:12:37Z'
~~~

Learn more about how this all works in the [basic usage](/4.0/properties/).

## Iterate over the interval

A simple example on how to get all the days from a selected month.

~~~php
foreach (Period::fromMonth(2014, 10)->getDatePeriod('1 DAY') as $day) {
    $day->format('Y-m-d');
}
~~~

To help easily instantiate your time range and manipulating it, the package comes bundle with [named constructors](/4.0/instantiation/) and [helper classes](/4.0/definitions/#arguments).

## Comparing intervals

~~~php
$period = Period::after('2014-01-01', '1 MONTH', Period::INCLUDE_ALL);
$altPeriod = Period::after('2014-01-01', '1 MONTH', Period::EXCLUDE_ALL);
$period->durationEquals($altPeriod), //returns true
$period->equals($altPeriod), //returns false
$period->contains($altPeriod), //returns true
$altPeriod->contains($period), //return false
$period->contains('2014-01-10'), //returns true
Datepoint::create('2014-02-10')->isDuring($period) //returns false
~~~

The class comes with other ways to [compare time ranges](/4.0/comparing/) based on their duration and/or their datepoints.  
`Datepoint` extends `DateTimeImmutable` and offers more [methods](/4.0/datepoint/).


## Modifying interval

~~~php
$period = Period::after('2014-01-01', '1 WEEK');
$altPeriod = $period->endingOn('2014-02-03');
$period->contains($altPeriod); //return false;
$altPeriod->durationGreaterThan($period); //return true;
~~~

`Period` is an immutable value object. Any changes to the object returns a new object. The class has more [modifying methods](/4.0/modifying/).

## Accessing all gaps between intervals

~~~php
$sequence = new Sequence(
    new Period('2018-01-01', '2018-01-31'),
    new Period('2017-01-01', '2017-01-31'),
    new Period('2020-01-01', '2020-01-31')
);
$gaps = $sequence->gaps(); // a new Sequence object
count($gaps); // 2
~~~

`Sequence` is a `Period` container and collection. The class has more [methods](/4.0/sequence/).
