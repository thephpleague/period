---
layout: homepage
---

# Features

## easy instance creation

~~~php
use League\Period\Period;
use function League\Period\interval_around;

//do
$interval = interval_around('2014-01-01 08:00:25', '1 HOUR');

//instead of doing
$date = new DateTimeImmutable('2014-01-01 08:00:25');
$duration = new DateInterval('PT1H');
$startDate = $date->sub($duration);
$endDate = $date->add($duration);

$interval = new Period($startDate, $endDate);
~~~

To help you start working with `Period` objects, the library comes bundled with many more namespaced helper functions to ease manipulating datetime intervals.

## iterating over the interval made simple

You can return selected datepoints inside the interval

~~~php
use function League\Period\month;

foreach (month(2014, 10)->getDatePeriod('1 DAY') as $datepoint) {
    echo $datepoint->format('Y-m-d');
}
~~~

or split the interval into smaller `Period` objects

~~~php
use function League\Period\month;

foreach (month(2014, 10)->split('1 DAY') as $period) {
    foreach ($period->getDatePeriod('1 HOUR') as $datepoint) {
    	echo $datepoint->format('Y-m-d H:i:s');
    }
}
~~~

The library also allow iterating backwards over the interval.

## compare intervals and datepoints

You can compare time ranges based on their duration and/or their datepoints.

~~~php
use function League\Period\interval_after;
use function League\Period\iso_week;

$period = interval_after('2014-01-01', '1 WEEK');
$altPeriod = iso_week(2014, 3);
$period->durationEquals($altPeriod); //returns true
$period->equals($altPeriod); //returns false
$period->contains($altPeriod); //returns false
$period->contains('2014-01-02'); //returns true
~~~

## Modifying time ranges

`Period` is an immutable value object. Any change to the object returns a new object.

~~~php
use function League\Period\interval_before;

$period = interval_before('2014-01-07', '1 WEEK');
$altPeriod = $period->endingOn('2014-02-03');
$period->contains($altPeriod); //return false;
$altPeriod->durationGreaterThan($period); //return true;
~~~

## formatting

Format and export your `Period` instance following standardized format.

~~~php
use function League\Period\interval_after;

$period = interval_after('2014-10-03 08:00:00', 3600);

echo $period; // 2014-10-03T06:00:00.000000Z/2014-10-03T07:00:00.000000Z
echo $period->format('Y-m-d H:i:s'); // [2014-10-03 08:00:00, 2014-10-03 09:00:00)
echo json_encode($period, JSON_PRETTY_PRINT);
// {
//     "startDate": "2014-10-03T06:00:00.000000Z",
//     "endDate": "2014-10-03T07:00:00.000000Z"
// }
~~~