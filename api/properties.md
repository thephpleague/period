---
layout: default
title: Accessing Period object properties
---

# Accessing properties

Once you have a instantiated `Period` object you can access its properties using the following getter methods:

<p class="message-warning">All deprecated methods will be removed in the next major version.</p>

### Period::getStartDate()

<p class="message-notice">Added to <code>Period</code> in version 2.5 and replace the deprecated <code>Perod::getStart</code></p>

Returns the starting **included** datepoint as a `DateTime` object.

### Period::getEndDate();

<p class="message-notice">Added to <code>Period</code> in version 2.5 and replace the deprecated <code>Perod::getEnd</code></p>

Returns the ending **excluded** datepoint as a `DateTime` object.

### Period::getDateInterval()

<p class="message-notice">Added to <code>Period</code> in version 2.5 and replace the deprecated <code>Perod::getDuration</code></p>

Returns the object duration as expressed as a `DateInterval` object.

### Period::getTimestampInterval()

<p class="message-notice">Added to <code>Period</code> in version 2.5 and replace the deprecated <code>Perod::getDuration</code></p>

Returns the object duration as expressed as a the difference between datepoint timestamp.

~~~php
use League\Period\Period;

$period = new Period('2012-04-01 08:30:25', new DateTime('2013-09-04 12:35:21'));
$period->getStartDate(); //returns DateTime('2012-04-01 08:30:25');
$period->getEndDate(); //returns DateTime('2013-09-04 12:35:21');
$duration = $period->getDateInterval(); //returns a DateInterval object
$altduration = $period->getTimestampInterval(); //returns the interval as expressed in seconds
~~~

### Period::getDatePeriod($interval)

<p class="message-notice">Added to <code>Period</code> in version 2.5 and replace the deprecated <code>Perod::getRange</code></p>

Returns a `DatePeriod` object that lists `DateTime` objects inside the period, separated by the given `$interval` expressed as a `DateInterval` object.

~~~php
use League\Period\Period;

$period = new Period('2012-01-01', '2013-01-01');
foreach ($period->getDatePeriod('1 MONTH') as $datetime) {
    echo $datetime->format('F, Y');
}
//will iterate 12 times
~~~

### Period::split($interval)

<p class="message-notice">Added to <code>Period</code> in version 2.5</p>

This methods split a given `Period` object in smaller `Period` objects according to the given `$interval`. All returned objects must be contained or abutted to the parent `Period` object.

- The first returned `Period` will always share the same starting datepoint with the parent object.
- The last returned `Period` will always share the same ending datepoint with the parent object.
- The last returned `Period` will have a duration equal or lesser than the submitted interval.
- If `$interval` is greater than the parent `Period` interval, the method will return an array with a single `Period` whose datepoints equals those of the parent `Period`.

~~~php
use League\Period\Period;

$period = Period::createFromYear(2012);
$period_list = $period->split('1 MONTH');
foreach ($period_list as $inner_periods) {
	echo $inner_period; //returns the string representation of a Period object
}
//will iterate 12 times
~~~

### Period::__toString()

<p class="message-notice">Added to <code>Period</code> in version 2.1</p>

Returns the string representation of a `Period` object using [ISO8601 time interval representation](http://en.wikipedia.org/wiki/ISO_8601#Time_intervals)

~~~php
date_default_timezone_set('Africa/Nairobi');

use League\Period\Period;

$period = new Period('2014-05-01 00:00:00', '2014-05-08 00:00:00');
echo $period; // '2014-04-30T21:00:00Z/2014-05-07T21:00:00Z'
~~~