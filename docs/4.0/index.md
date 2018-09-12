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

`Period` is PHP's missing time range API. It is based on [Resolving Feature Envy in the Domain](//verraes.net/2014/08/resolving-feature-envy-in-the-domain/) by Mathias Verraes and extends the concept to cover all basic operations regarding time ranges.

## Usage

Of note, in your code, you will always have to typehint against the `League\Period\Period` class directly. Because `League\Period\Period` is a immutable value object class, the class is marked as final and does not provide an interface.

### Accessing time range properties

~~~php
<?php

date_default_timezone_set('UTC');

use League\Period\Period;

$period = new Period(new DateTime('2014-10-03 08:12:37'), new DateTimeImmutable('2014-10-03 08:12:37'));
$start = $period->getStartDate(); //return DateTimeImmutable('2014-10-03 08:12:37');
$end   = $period->getEndDate();   //return DateTimeImmutable('2014-10-03 09:12:37');
$duration  = $period->getDateInterval();      //return a DateInterval object
$duration2 = $period->getTimestampInterval(); //return the same interval expressed in seconds.
echo $period; //displays '2014-10-03T08:12:37Z/014-10-03T09:12:37Z'
~~~

Learn more about how this all works in the [basic usage](/4.0/properties/).

### Iterate over a time range

A simple example on how to get all the days from a selected month.

~~~php
$period = new Period(new DateTime('2014-10-01'), new DateTimeImmutable('2014-11-01'));
foreach ($period->getDatePeriod('1 DAY') as $day) {
    $day->format('Y-m-d');
}
~~~

To help easily instantiate your time range, the package comes bundle with many [helper functions](/4.0/instantiation/).

### Comparing time ranges

~~~php
<?php

use function League\Period\interval_after;
use function League\Period\iso_week;

$period = interval_after('2014-01-01', '1 WEEK');
$altPeriod = iso_week(2014, 3);
$period->durationEquals($altPeriod); //will return true because the duration are equals
$period->equals($altPeriod);         //will return false because the datepoints differ
~~~

The class comes with other ways to [compare time ranges](/4.0/comparing/) based on their duration and/or their datepoints.

### Modifying time ranges

~~~php
<?php

use function League\Period\interval_after;

$period = interval_after('2014-01-01', '1 WEEK');
$altPeriod = $period->endingOn('2014-02-03');
$period->contains($altPeriod); //return false;
$altPeriod->durationGreaterThan($period); //return true;
~~~

`Period` is an immutable value object. Any changes to the object returns a new object. The class has more [modifying methods](/4.0/modifying/).