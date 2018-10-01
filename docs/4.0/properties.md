---
layout: default
title: Accessing Period object properties
---

# Accessing properties

## Getter Period informations

Once you have a instantiated `Period` object you can access the object datepoints and durations using the following getter methods:

~~~php
<?php

public Period::getStartDate(void): DateTimeImmutable
public Period::getEndDate(void): DateTimeImmutable
public Period::getDateInterval(void): DateInterval
public Period::getTimestampInterval(void): float
~~~

~~~php
$period = new Period('2012-04-01 08:30:25', new DateTime('2013-09-04 12:35:21'));
$period->getStartDate(); //returns DateTimeImmutable('2012-04-01 08:30:25');
$period->getEndDate(); //returns DateTimeImmutable('2013-09-04 12:35:21');
$duration = $period->getDateInterval(); //returns a DateInterval object
$altduration = $period->getTimestampInterval(); //returns the duration in seconds
~~~

## Iteration over a Period

### Period::getDatePeriod

#### Description

~~~php
<?php

public Period::getDatePeriod(mixed $duration, int $option): DatePeriod
~~~

Returns a `DatePeriod` using the `Period` datepoints with the given `$duration`.

<p class="message-notice">When iterating over a <code>DatePeriod</code> object returns by the <code>Period::getDatePeriod</code> all the generated datepoints are <code>DateTimeImmutable</code> instances.</p>

#### Parameters

- `$duration` is a interval
- `$option` Can be set to **`DatePeriod::EXCLUDE_START_DATE`** to exclude the start date from the set of recurring dates within the period.

#### Examples

~~~php
use function League\Period\year;

foreach (year(2012)->getDatePeriod('1 MONTH') as $datetime) {
    echo $datetime->format('F, Y');
}
//will iterate 12 times
~~~

Using the `$option` parameter

~~~php
$interval = year('2012-06-05');
$datePeriod = $interval->getDatePeriod('1 MONTH', DatePeriod::EXCLUDE_START_DATE);
foreach ($datePeriod as $datetime) {
    echo $datetime->format('F, Y');
}
//will iterate 11 times
~~~

### Period::split

#### Description

~~~php
<?php

public Period::split(mixed $duration): Generator
~~~

This method splits a given `Period` object in smaller `Period` objects according to the given `$interval` startinf from the object starting datepoint to its ending datepoint. The result is returned as a `Generator` object. All returned objects must be contained or abutted to the parent `Period` object.

- The first returned `Period` will always share the same starting datepoint with the parent object.
- The last returned `Period` will always share the same ending datepoint with the parent object.
- The last returned `Period` will have a duration equal or lesser than the submitted interval.
- If `$interval` is greater than the parent `Period` interval, the generator will contain a single `Period` whose datepoints equals those of the parent `Period`.

#### Example

~~~php
foreach (year(2012)->split('1 MONTH') as $inner_periods) {
    echo $inner_period; //returns Period object whose interval is 1 MONTH
}
//will iterate 12 times
~~~

### Period::splitBackwards

#### Description

~~~php
<?php

public Period::splitBackwards(mixed $duration): Generator
~~~

This method splits a given `Period` object in smaller `Period` objects according to the given `$interval` starting from the object ending datepoint to its starting datepoint. The result is returned as a `Generator` object. All returned objects must be contained or abutted to the parent `Period` object.

- The first returned `Period` will always share the same ending datepoint with the parent object.
- The last returned `Period` will always share the same starting datepoint with the parent object.
- The last returned `Period` will have a duration equal or lesser than the submitted interval.
- If `$interval` is greater than the parent `Period` interval, the generator will contain a single `Period` whose datepoints equals those of the parent `Period`.

#### Example

~~~php
date_default_timezone_set('Africa/Kinshasa');

$collection = iterator_to_array(year(2012)->splitBackwards('5 MONTH'));
echo $collection[0]; // 2012-07-31T23:00:00Z/2012-12-31T23:00:00Z (5 months interval)
echo $collection[1]; // 2012-02-29T23:00:00Z/2012-07-31T23:00:00Z (5 months interval)
echo $collection[2]; // 2011-12-31T23:00:00Z/2012-02-29T23:00:00Z (2 months interval)
~~~

## Period representations

### String representation

~~~php
<?php

public Period::__toString(void): string
~~~

Returns the string representation of a `Period` object using [ISO8601 time interval representation](http://en.wikipedia.org/wiki/ISO_8601#Time_intervals).

~~~php
date_default_timezone_set('Africa/Nairobi');

$period = new Period('2014-05-01 00:00:00', '2014-05-08 00:00:00');
echo $period; // '2014-04-30T23:00:00.000000Z/2014-05-07T23:00:00.000000Z'
~~~

### Json representation

~~~php
<?php

public Period::jsonSerialize(void): array
~~~

`Period` implements the `JsonSerializable` interface and is directly usable with PHP `json_encode` function as shown below:

~~~php
date_default_timezone_set('Africa/Kinshasa');

$period = new Period('2014-05-01 00:00:00', '2014-05-08 00:00:00');

$res = json_decode(json_encode($period), true);
//  $res will be equivalent to:
// [
//      'startDate' => '2014-04-30T23:00:00.000000Z,
//      'endDate' => '2014-05-07T23:00:00.000000Z',
// ]
~~~

<p class="message-info">This format was chosen to enable better compatibility with Javascript <a href="https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Date/toISOString">Date representation</a>.</p>

### Mathematical representation

~~~php
<?php

public Period::format(string $format): string
~~~

You can use the `format` method to represent a `Period` object in its mathematical representation as a [right open interval](https://en.wikipedia.org/wiki/Interval_(methematics)#Notations_for_intervals) as show below. the `$format` parameter expects a string which follow [date](http://php.net/manual/en/function.date.php) first argument rules.

~~~php

$interval = new Period('2014-05-01 00:00:00', '2014-05-08 00:00:00');
echo $interval->format('Y-m-d'); // [2014-05-01, 2014-05-08)
~~~

This representation can be used, for instance, in a PostreSQL query against a [DateRange field](https://www.postgresql.org/docs/9.3/static/rangetypes.html).