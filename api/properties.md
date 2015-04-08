---
layout: default
title: Accessing Period object properties
---

# Accessing properties

Once you have a instantiated `Period` object you can access its properties using the following getter methods:

### Period::getStartDate()

Returns the starting **included** datepoint as a `DateTimeImmutable` object.

<p class="message-warning"><strong>BC Break :</strong> In <code>version 2.X</code>, this method returned an <code>DateTime</code> object</p>

### Period::getEndDate();

Returns the ending **excluded** datepoint as a `DateTimeImmutable` object.

<p class="message-warning"><strong>BC Break :</strong> In <code>version 2.X</code>, this method returned an <code>DateTime</code> object</p>

### Period::getDateInterval()

Returns the object duration as expressed as a `DateInterval` object.

### Period::getTimestampInterval()

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

This method splits a given `Period` object in smaller `Period` objects according to the given `$interval`. THe result is returned using a `Generator` object. All returned objects must be contained or abutted to the parent `Period` object.

<p class="message-warning"><strong>BC Break :</strong> In <code>version 2.X</code>, this method returned an <code>array</code></p>

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

Returns the string representation of a `Period` object using [ISO8601 time interval representation](http://en.wikipedia.org/wiki/ISO_8601#Time_intervals)

<p class="message-notice">Starting with <code>version 3</code>, this method also returns the microseconds</p>

~~~php
date_default_timezone_set('Africa/Nairobi');

use League\Period\Period;

$period = new Period('2014-05-01 00:00:00', '2014-05-08 00:00:00');
echo $period; // '2014-04-30T21:00:00.000000Z/2014-05-07T21:00:00.000000Z'
~~~

### Period::jsonSerialize()

Period implements the `JsonSerializable` interface and is directly usable with PHP `json_encode` function as shown below:

~~~php

use League\Period\Period;

$period = new Period('2014-05-01 00:00:00', '2014-05-08 00:00:00');

$res = json_decode(json_encode($period), true);
//  $res will be equivalent to:
// [
//      'startDate' => [
//          'date' => '2014-05-01 00:00:00.000000',
//          'timezone_type' => 3,
//          'timezone' => 'Africa\Kinshasa',
//      ],
//      'endDate' => [
//          'date' => '2014-05-08 00:00:00.000000',
//          'timezone_type' => 3,
//          'timezone' => 'Africa\Kinshasa',
//      ],
// ]
~~~

<p class="message-notice">microseconds are missing prior to <a href="http://php.net/ChangeLog-5.php#5.5.14" target="_blank">version 5.5.14</a>.</p>
