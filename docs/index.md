---
layout: homepage
---

# Features

## easy instance creation

~~~php
use League\Period\Period;

//do
$interval = Period::around('2014-01-01 08:00:25', '1 HOUR');

//instead of doing
$date = new DateTimeImmutable('2014-01-01 08:00:25');
$duration = new DateInterval('PT1H');
$startDate = $date->sub($duration);
$endDate = $date->add($duration);

$interval = new Period($startDate, $endDate);
~~~

To help you start working with `Period` objects, the library comes bundled with many more named constructors to ease manipulating datetime intervals.

## iterating over the interval made simple

You can return selected datepoints inside the interval

~~~php
foreach (Period::fromMonth(2014, 10)->getDatePeriod('1 DAY') as $datepoint) {
    echo $datepoint->format('Y-m-d');
}
~~~

or split the interval into smaller `Period` objects

~~~php
foreach (Period::fromMonth(2014, 10)->split('1 DAY') as $period) {
    foreach ($period->getDatePeriod('1 HOUR') as $datepoint) {
    	echo $datepoint->format('Y-m-d H:i:s');
    }
}
~~~

The library also allow iterating backwards over the interval.

## compare intervals and datepoints

You can compare time ranges based on their duration and/or their datepoints.

~~~php
$period = Period::after('2014-01-01', '1 WEEK');
$altPeriod = Period::fromIsoWeek(2014, 3);
$period->durationEquals($altPeriod); //returns true
$period->equals($altPeriod); //returns false
$period->contains($altPeriod); //returns false
$period->contains('2014-01-02'); //returns true
~~~

## Modifying time ranges

`Period` is an immutable value object. Any change to the object returns a new object.

~~~php
$period = Period::before('2014-01-07', '1 WEEK');
$altPeriod = $period->endingOn('2014-02-03');
$period->contains($altPeriod); //return false;
$altPeriod->durationGreaterThan($period); //return true;
~~~

## formatting

Format and export your `Period` instance following standardized format.

~~~php
$period = Period::after('2014-10-03 08:00:00', 3600);

echo $period; // 2014-10-03T06:00:00.000000Z/2014-10-03T07:00:00.000000Z
echo $period->format('Y-m-d H:i:s'); // [2014-10-03 08:00:00, 2014-10-03 09:00:00)
echo json_encode($period, JSON_PRETTY_PRINT);
// {
//     "startDate": "2014-10-03T06:00:00.000000Z",
//     "endDate": "2014-10-03T07:00:00.000000Z"
// }
~~~

## Accessing Gaps between intervals

~~~php
use League\Period\Period;
use League\Period\Sequence;

$sequence = new Sequence(
    new Period('2018-01-01', '2018-01-31'),
    new Period('2017-01-01', '2017-01-31'),
    new Period('2020-01-01', '2020-01-31')
);
$gaps = $sequence->gaps(); // a new Sequence object
count($gaps); // 2
~~~

`Sequence` is a `Period` container and collection class.