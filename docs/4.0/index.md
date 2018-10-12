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

`Period` is PHP's missing time range API. It is based on [Resolving Feature Envy in the Domain](//verraes.net/2014/08/resolving-feature-envy-in-the-domain/) by Mathias Verraes and extends the concept to cover all basic operations regarding interval.

<p class="message-info">In your code, you will always have to typehint against the <code>League\Period\Period</code> class directly because it is a immutable value object class marked as final and the library does not provide an interface.</p>

## Accessing the interval properties

~~~php
<?php

use League\Period\Period;

$interval = new Period(new DateTime('2014-10-03 08:12:37'), new DateTimeImmutable('2014-10-03 08:12:37'));
$start = $interval->getStartDate(); //return DateTimeImmutable('2014-10-03 08:12:37');
$end = $interval->getEndDate();     //return DateTimeImmutable('2014-10-03 09:12:37');
$duration = $interval->getDateInterval();       //return a DateInterval object
$duration2 = $interval->getTimestampInterval(); //return the same interval expressed in seconds.
echo $interval; //displays '2014-10-03T08:12:37Z/014-10-03T09:12:37Z'
~~~

Learn more about how this all works in the [basic usage](/4.0/properties/).

## Iterate over the interval

A simple example on how to get all the days from a selected month.

~~~php
use function League\Period\month;

$interval = month(2014, 10);
foreach ($interval->getDatePeriod(new DateInterval('P1D')) as $day) {
    $day->format('Y-m-d');
}
~~~

To help easily instantiate your time range, the package comes bundle with [helper functions](/4.0/instantiation/).

## Comparing intervals

~~~php
<?php

use function League\Period\interval_after;
use function League\Period\iso_week;

$interval = interval_after('2014-01-01', '1 WEEK');
$alt_interval = iso_week(2014, 1);
$interval->durationEquals($alt_interval); //returns true
$interval->equals($alt_interval);         //returns false
~~~

The class comes with other ways to [compare time ranges](/4.0/comparing/) based on their duration and/or their datepoints.

## Modifying interval

~~~php
<?php

use function League\Period\interval_after;

$period = interval_after('2014-01-01', '1 WEEK');
$altPeriod = $period->endingOn('2014-02-03');
$period->contains($altPeriod); //return false;
$altPeriod->durationGreaterThan($period); //return true;
~~~

`Period` is an immutable value object. Any changes to the object returns a new object. The class has more [modifying methods](/4.0/modifying/).