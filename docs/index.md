---
layout: homepage
---

# Features

## Multiple named constructors

To help you start working with `Period` objects

~~~php
<?php

use League\Period\Period;

$period = new Period(new DateTime('2014-10-15 08:35:26'), '2014-10-15 08:53:12');
Period::createFromYear(2014);
Period::createFromSemester(2014, 1);
Period::createFromQuarter(2014, 1);
Period::createFromMonth(2014, 1);
Period::createFromWeek(2014, 1);
Period::createFromDay('NOW');
Period::createFromDuration('2014-01-01 08:00:25', new DateInterval('PT1H'));
Period::createFromDurationBeforeEnd('2014-01-01 08:00:25', 3600);
~~~

## Accessing time range properties

Once instantiated, you can access `Period` datepoints and durations easily:

~~~php
<?php

use League\Period\Period;

$period = Period::createFromDuration('2014-10-03 08:12:37', 3600);
$start = $period->getStartDate();
$end = $period->getEndDate();
$duration = $period->getDateInterval();
$duration2 = $period->getTimestampInterval();
echo $period;
~~~

## Different ways to iterate over the time range

You can return selected datepoints inside the `Period` time range

~~~php
<?php

use League\Period\Period;

$period = Period::createFromMonth(2014, 10);
foreach ($period->getDatePeriod('1 DAY') as $datepoint) {
    echo $datepoint->format('Y-m-d');
}
~~~

or split the given time range into smaller `Period` objects

~~~php
<?php

use League\Period\Period;

$period = Period::createFromMonth(2014, 10);
foreach ($period->split('1 DAY') as $period) {
    echo $period;
}
~~~

## Comparing different time ranges simplified

You can compare time ranges based on their duration and/or their datepoints.

~~~php
<?php

use League\Period\Period;

$period = Period::createFromDuration('2014-01-01', '1 WEEK');
$altPeriod = Period::createFromWeek(2014, 3);
$period->sameDurationAs($altPeriod); //will return true because the duration are equals
$period->sameValueAs($altPeriod); //will return false because the datepoints differ
~~~

## Modifying time ranges

`Period` is an immutable value object. Any changes to the object returns a new object.

~~~php
<?php

use League\Period\Period;

$period = Period::createFromDuration('2014-01-01', '1 WEEK');
$altPeriod = $period->endingOn('2014-02-03');
$period->contains($altPeriod); //return false;
$altPeriod->durationGreaterThan($period); //return true;
~~~