---
layout: default
title: Period instantiation
---

# The Period value object

A `Period` instance is a PHP implementation of a bounded datetime interval which consists of:

- two date endpoints hereafter referred to as datepoints. A **datepoint** is a position in time expressed as a `DateTimeImmutable` object. The starting datepoint is always less than or equal to the ending datepoint.
- the duration between them which correspond to the continuous portion of time between two datepoints expressed as a `DateInterval` object. The duration cannot be negative.
- its bounds. An included datepoint means that the boundary datepoint itself is included in the interval as well, while an excluded datepoint means that the boundary datepoint is not included in the interval.  

The package supports included and excluded datepoint, thus, the following bounds are supported:

- included starting datepoint and excluded ending datepoint: `[start, end)`;
- included starting datepoint and included ending datepoint : `[start, end]`;
- excluded starting datepoint and included ending datepoint : `(start, end]`;
- excluded starting datepoint and excluded ending datepoint : `(start, end)`;

<p class="message-warning">infinite or unbounded intervals are not supported.</p>

## Instantiation

Instantiating a `Period` object is done using several named constructors describe below as the default constructor is
made private. Whichever instance used, a `Period` instance is always created with the following requirements:

- The `$startDate` represents **the starting datepoint**.
- The `$endDate` represents **the ending datepoint**.
- The `$bounds` represents **the interval bounds**. 

Both `$startDate` and `$endDate` parameters are datepoints. `$endDate` **must be** greater or equal to `$startDate` or the instantiation will throw a `Period\DateRangeError`.

The `$bounds` is a `Period\Bounds` and only its value are eligible to create a new `Period` instance.

<p class="message-info">By default for each named constructor the <code>$bounds</code> is <code>Bounds::INCLUDE_START_EXCLUDE_END</code> when not explicitly provided.</p>

### Using datepoints

~~~php
public static Period::fromDate(
    DatePoint|DateTimeInterface|string $startDate, 
    DatePoint|DateTimeInterface|string $endDate, 
    Bounds $bounds = Bounds::INCLUDE_START_EXCLUDE_END
): Period
~~~

If the timezone is important use a `DateTimeInterface` object instead of a string. When a string is provided, the timezone information is derived from the underlying system.

~~~php
$day = Period::fromDate('2012-01-03', Datepoint::fromDateString('2012-02-03'), Bounds::EXCLUDE_ALL);
$day->toNotation('Y-m-d'); //return (2012-01-03, 2012-02-03)
~~~

### Using a datepoint and a duration

~~~php
public static Period::after(
    DatePoint|DateTimeInterface|string $startDate, 
    Period|Duration|DateInterval|string $duration, 
    Bounds $bounds = Bounds::INCLUDE_START_EXCLUDE_END
): Period
public static Period::before(
    DatePoint|DateTimeInterface|string $endDate,
    Period|Duration|DateInterval|string $duration,
    Bounds $bounds = Bounds::INCLUDE_START_EXCLUDE_END
): Period
public static Period::around(
    DatePoint|DateTimeInterface|string $midpoint,
    Period|Duration|DateInterval|string $duration, 
    Bounds $bounds = Bounds::INCLUDE_START_EXCLUDE_END
): Period
~~~

- `Period::after` returns a `Period` object which starts at `$startDate`
- `Period::before` returns a `Period` object which ends at `$endDate`
- `Period::around` returns a `Period` object where the given duration is simultaneously subtracted from and added to the `$midpoint`.

#### Examples

Using `Period::after`, `Period::around`, `Period::before`:

~~~php
$date = '2012-04-01 08:30:25';
$duration = '1 DAY';
$half_duration = '12 HOURS';

$intervalAfter = Period::after($date, $duration);
$intervalBefore = Period::before($date->add($duration), $duration);
$intervalAfter->equals($intervalBefore); //returns true
$intervalAround = Period::around($date->add($half_duration), $half_duration);
$intervalAround->equals($intervalBefore); //returns true
~~~

### Using date fields

<p class="message-notice">The week index follows the <a href="https://en.wikipedia.org/wiki/ISO_week_date" target="_blank">ISO week date</a> system. This means that the first week may be included in the previous year, conversely the last week may be included in the next year.</p>

~~~php
public static Period::fromDay(int $year, int $month, int $day, Bounds $bounds = Bounds::INCLUDE_START_EXCLUDE_END): Period
public static Period::fromIsoWeek(int $year, int $week, Bounds $bounds = Bounds::INCLUDE_START_EXCLUDE_END): Period
public static Period::fromMonth(int $year, int $month, Bounds $bounds = Bounds::INCLUDE_START_EXCLUDE_END): Period
public static Period::fromQuarter(int $year, int $quarter, Bounds $bounds = Bounds::INCLUDE_START_EXCLUDE_END): Period
public static Period::fromSemester(int $year, int $semester, Bounds $bounds = Bounds::INCLUDE_START_EXCLUDE_END): Period
public static Period::fromYear(int $year, Bounds $bounds = Bounds::INCLUDE_START_EXCLUDE_END): Period
public static Period::fromIsoYear(int $year, Bounds $bounds = Bounds::INCLUDE_START_EXCLUDE_END): Period
~~~

<p class="message-info">The datepoints will be created following PHP <code>DateTimeImmutable::setDate</code>, <code>DateTimeImmutable::setISODate</code> and <code>DateTimeImmutable::setTime</code> rules<br> which means that overflow is possible and acceptable.</p>

#### Examples

~~~php
$day = Period::fromDay(2012, 1, 3);
$daybis = Period::fromDate('2012-01-03', '2012-01-04');
$day->equals($daybis); //return true;
$day->startDate()->format('Y-m-d H:i:s'); //return 2012-01-03 00:00:00
$day->endDate()->format('Y-m-d H:i:s'); //return 2012-01-04 00:00:00
~~~

### Using standardized notation

~~~php
public static Period::fromIso8601(string $format, string $notation, Bounds $bounds = Bounds::INCLUDE_START_EXCLUDE_END): Period
public static Period::fromNotation(string $format, string $notation): Period
~~~

- The `$format` string describes how the date are presented should be a valid string accepted by `DateTimeImmutable::createFromFormat` first argument.
- The `$notation` string contains the date range as expected by each format.

For better understanding:

- the start datepoint is represented as `{startDate}`
- the end datepoint is represented as `{endDate}`
- the lower bound is represented as `{lowerBound}`
- the upper bound is represented as `{upperBound}`

#### Using ISO 8601 notation

The `$notation` should follow the `{startDate}/{endDate}` pattern where `/` serves as delimiter. Each endpoint should be formatted following the `$format` input;

~~~php
$day = Period::fromIso8601('Y-m-d', '2012-01-03/2012-02-03');
echo $day->toNotation('Y-m-d H:i:s'), //return [2012-01-03 21:38:22, 2012-02-03 21:38:22)
~~~

#### Using mathematical notation

The `$notation` should follow the `{lowerbound}{startDate},{endDate}{upperbound}` where `,` serves as delimiter. 
Each endpoint should be formatted following the `$format` input.
The possible bound values are:

- `{lowerbound}` can be `[` or `(`;
- `{upperbound}` can be `]` or `)`;

~~~php
$day = Period::fromNotation('!Y-m-d', '[ 2012-01-03  , 2012-02-03 ]');
echo $day->toNotation('Y-m-d H:i:s'); // returns [2012-01-03 00:00:00, 2012-02-03 00:00:00]
$day->bounds() === Bounds::INCLUDE_ALL;
~~~

### Using a DatePeriod object

~~~php
function Period::fromDateRange(
    DatePeriod $datePeriod,
    Bounds $bounds = Bounds::INCLUDE_START_EXCLUDE_END
): self
~~~

#### Example

~~~php
$daterange = new DatePeriod(
    new DateTime('2012-08-01'),
    new DateInterval('PT1H'),
    new DateTime('2012-08-31')
);
$interval = Period::fromDateRange($daterange);
$interval->getStartDate() == $daterange->getStartDate();
$interval->getEndDate() == $daterange->getEndDate();
~~~

<p class="message-warning">If the submitted <code>DatePeriod</code> instance does not have a ending datepoint, It will trigger a <code>TypeError</code> error. This is possible if the <code>DatePeriod</code> instance was created using recurrences only</p>

~~~php
$dateRange = new DatePeriod('R4/2012-07-01T00:00:00Z/P7D');
$interval = Period::fromDateRange($dateRange);
//throws a TypeError error because $dateRange->getEndDate() returns null
~~~
