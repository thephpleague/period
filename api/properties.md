---
layout: default
title: Basic Usage
---

# Accessing properties

Once you have a instantiated `Period` object you can access its properties using the following getter methods defined in the `TimeRange` interface :

### Period::getStart()

Returns the starting **included** endpoint as a `DateTime`.

### Period::getEnd();

Returns the ending **excluded** endpoint as a `DateTime`.

### Period::getDuration($get_as_seconds = false)

Returns the object duration. If the `$get_as_seconds` parameter is used and set to `true`, the method will return an integer which represents the duration in seconds instead of a `DateInterval` object.

~~~php
use League\Period\Period;

$period = new Period('2012-04-01 08:30:25', new DateTime('2013-09-04 12:35:21'));
$period->getStart(); //returns DateTime('2012-04-01 08:30:25');
$period->getEnd(); //returns DateTime('2013-09-04 12:35:21');
$duration = $period->getDuration(); //returns a DateInterval object
$altduration = $period->getDuration(true); //returns the interval as expressed in seconds
~~~

### Period::getDatePeriod($interval)

<p class="message-warning"><code>Period::getRange</code> is deprecated since version 2.5 and will be remove in the next major version. For background compatibility <code>Period::getRange</code> is now a alias of <code>Period::getDatePeriod</code></p>

Returns a `DatePeriod` object that lists `DateTime` objects inside the period, separated by the given `$interval`, and expressed as a `DateInterval` object.

~~~php
use League\Period\Period;

$period = new Period('2012-01-01', '2013-01-01');
foreach ($period->getDatePeriod('1 MONTH') as $datetime) {
    echo $datetime->format('F, Y');
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