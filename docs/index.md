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

$interval = Period::fromDate($startDate, $endDate);
~~~

To help you start working with `Period` objects, the library comes bundled with many more named constructors to ease datepoint, duration and intervals creation.

## iterating over the interval made simple

You can return selected date endpoints inside the interval

~~~php
use League\Period\Period;

foreach (Period::fromMonth(2014, 10)->dateRange('1 DAY') as $datepoint) {
    echo $datepoint->format('Y-m-d');
}
~~~

or split the interval into smaller `Period` objects

~~~php
use League\Period\Period;

foreach (Period::fromMonth(2014, 10)->splitForward('1 DAY') as $period) {
    foreach ($period->dateRange('1 HOUR') as $datepoint) {
    	echo $datepoint->format('Y-m-d H:i:s');
    }
}
~~~

The library also allow iterating backwards over the interval.

## compare intervals and datepoints

You can compare time ranges based on their duration, their datepoints and even their boundary types.

~~~php
use League\Period\Bounds;
use League\Period\Period;

$period = Period::after('2014-01-01', '1 MONTH', Bounds::IncludeAll);
$altPeriod = Period::after('2014-01-01', '1 MONTH', Bounds::ExcludeAll);
$period->durationEquals($altPeriod), //returns true
$period->equals($altPeriod), //returns false
$period->contains($altPeriod), //returns true
$altPeriod->contains($period), //return false
$period->contains('2014-01-10'), //returns true
Datepoint::create('2014-02-10')->isDuring($period) //returns false
~~~

## Modifying time ranges

`Period` is an immutable value object. Any change to the object returns a new object.

~~~php
use League\Period\Period;

$period = Period::before('2014-01-07', '1 WEEK');
$altPeriod = $period->endingOn('2014-02-03');
$period->contains($altPeriod); //return false;
$altPeriod->durationGreaterThan($period); //return true;
~~~

## formatting

Format and export your `Period` instance following standardized format.

~~~php
use League\Period\Bounds;
use League\Period\Period;

$period = Period::after('2014-10-03 08:00:00', 3600, Bounds::IncludeStartExcludeEnd);

echo $period; // 2014-10-03T06:00:00.000000Z/2014-10-03T07:00:00.000000Z
echo $period->toIso80000('Y-m-d H:i:s'); // [2014-10-03 08:00:00, 2014-10-03 09:00:00)
echo json_encode($period, JSON_PRETTY_PRINT);
// {
//     "startDate": "2014-10-03T06:00:00.000000Z",
//     "endDate": "2014-10-03T07:00:00.000000Z",,
//     "startDateIncluded": true,
//    "endDateIncluded": false
// }
~~~

## Accessing Gaps between intervals

~~~php
use League\Period\Period;
use League\Period\Sequence;

$sequence = new Sequence(
    Period::fromDate('2018-01-01', '2018-01-31'),
    Period::fromDate('2017-01-01', '2017-01-31'),
    Period::fromDate('2020-01-01', '2020-01-31')
);
$gaps = $sequence->gaps(); // a new Sequence object
count($gaps); // 2
~~~

`Sequence` is a `Period` container and collection class.

## Displaying intervals using a GanttChart

Using the feature present under the `League\Period\Chart` you can visualize on the console
the interactions betweens multiple `League\Period\Period` and/or `League\Period\Sequence` instances.

~~~php
use League\Period\Bounds;
use League\Period\Chart;
use League\Period\Period;
use League\Period\Sequence;

$sequence = new Sequence(
    Period::fromMonth(2021, 1, Bounds::IncludeAll),
    Period::fromDate('2021-02-10', '2021-02-20', Bounds::ExcludeAll),
    Period::fromMonth(2021, 3, Bounds::IncludeStartExcludeEnd),
    Period::after('2021-01-20', '1 month 20 days', Bounds::ExcludeStartIncludeEnd)
);
$intersections = $sequence->intersections();

$labelGenerator = new Chart\LatinLetter('f');
$labelGenerator = new Chart\AffixLabel($labelGenerator, 'Interval ', '.');

$dataset = Chart\Dataset::fromItems($sequence, $labelGenerator)
    ->append("Intersections.", $intersections);

$chart = new Chart\GanttChart();
$chart->stroke($dataset);
~~~

will output something like this:

```bash
    Interval f. [-------------------]                                       
    Interval g.                           (------)                          
    Interval h.                                        [-------------------)
    Interval i.             (---------------------------------]             
 Intersections.             (-------]     (------)     [------]   
 ```
