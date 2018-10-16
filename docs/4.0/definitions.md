---
layout: default
title: Concepts and arguments
---

# Definition and Argument

## Concepts

- **interval** - `Period` is a PHP implementation of a left closed right open datetime interval which consists of two datepoints and the duration between them. This means that:

    - The starting datepoint is included into the interval whereas the ending datepoint is excluded from it.
    - The starting datepoint is always less than or equal to the ending datepoint.

- **datepoint** - A position in time expressed as a `DateTimeImmutable` object.

- **duration** - The continuous portion of time between two datepoints expressed as a `DateInterval` object. The duration cannot be negative.

## Arguments

Since this package relies heavily on `DateTimeImmutable` and `DateInterval` objects and because it is sometimes complicated to get your hands on such objects the package comes bundled with two simple functions that are used throughout the library to ensure typesafety. These functions are defined under the `League\Period` namespace.

### datepoint

~~~php
function League\Period\datepoint(mixed $datepoint): DateTimeImmutable;

//or

function League\Period\datepoint(
    int $year,
    int $month,
    int $day,
    int $hour = 0,
    int $minute = 0,
    int $second = 0
    int $microsecond = 0
): DateTimeImmutable;
~~~

Returns a `DateTimeImmutable` object or throws:

- a `TypeError` if the submitted parameters have the wrong type.
- a `TypeError` if the number of parameters passed is invalid.
- a PHP `Exception` if creating a `DateTimeImmutable` fails.

#### parameters

- `$datepoint` can be:
    - a `DateTimeInterface` implementing object
    - a string parsable by the `DateTime` constructor.
    - an integer interpreted as a timestamp.

- `$year` the date year as an `int`;
- `$month` the date month as an `int`;
- `$day` the date day as an `int`;
- `$hour` the date hour as an `int`;
- `$minute` the date minute as an `int`;
- `$second` the date second as an `int`;
- `$microsecond` the date microseconds as an `int`;

The `$month`, `$day`, `$hour`, `$minute`, `$second`, `$microsecond` arguments will only be taken into account if:

- `$year` is an integer;
- `$month` is not `null`;
- `$day` is not `null`;

In such case, `$hour`, `$minute`, `$second`, `$microsecond` values will default to `0` if not provided.

<p class="message-info">Because we are using PHP's parser, values exceeding ranges will be added to their parent values.</p>

<p class="message-info">If no timezone information is given, the returned <code>DateTimeImmutable</code> object will use the current timezone.</p>

#### examples

Using the `$datepoint` argument

~~~php
use function League\Period\datepoint;

datepoint('yesterday'); // returns new DateTimeImmutable('yesterday')
datepoint('2018');      // returns new DateTimeImmutable('@2018')
datepoint(2018);        // returns new DateTimeImmutable('@2018')
datepoint(new DateTime('2018-10-15'));  // returns new DateTimeImmutable('2018-10-15')
datepoint(new DateTimeImmutable('2018-10-15'));  // returns new DateTimeImmutable('2018-10-15')
~~~

Using `$year`, `$month`, `$day`, `$hour`, `$minute`, `$second`, `$microsecond` arguments:

~~~php
use function League\Period\datepoint;

datepoint(2018, 2, 1, 4); // returns new DateTimeImmutable('2018-02-01 04:00:00')
~~~

Because you must provide at least the month and the day index, here's how to get the first day of the year using this function.

~~~php
use function League\Period\datepoint;

datepoint(2018, 1, 1); // returns new DateTimeImmutable('2018-01-01')
~~~

<p class="message-warning">If you provide too few date and time arguments a <code>TypeError</code> exception will be thrown.</p>

~~~php
use function League\Period\datepoint;

datepoint(2018, 1);
// throw a TypeError the day argument is missing
~~~

### duration

~~~php
function League\Period\duration(mixed $duration): DateInterval;
~~~

Converts its single input into a `DateInterval` object or throws a `TypeError` otherwise.

#### parameter

`$duration` can be:

- a `League\Period\Period` object;
- a `DateInterval` object;
- a string parsable by the `DateInterval::createFromDateString` method.
- an integer interpreted as the interval expressed in seconds.

<p class="message-warning"><strong>WARNING:</strong> When the string is not parsable by <code>DateInterval::createFromDateString</code> a <code>DateInterval</code> object representing the <code>0</code> interval is returned as described in <a href="https://bugs.php.net/bug.php?id=50020">PHP bug #50020</a>.</p>

### Examples

~~~php
use function League\Period\datepoint;
use function League\Period\duration;

$datePeriod = new DatePeriod(
    datepoint(2012, 3, 6),
    duration(600),
    datepoint(new DateTime())
);

//returns the same object as if you had written

$datePeriod = new DatePeriod(
    (new DateTimeImmutable())->setDate(2012, 3, 6)->setTime(0, 0),
    new DateInterval('PT600S'),
    new DateTimeImmutable()
);
~~~