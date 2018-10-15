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
function League\Period\datepoint(mixed $year_or_datepoint, int ...$indexes): DateTimeImmutable;
~~~

Returns a `DateTimeImmutable` object or throws:

- a `TypeError` if the `$year_or_datepoint` type is not supported.
- a PHP `Exception` if creating a `DateTimeImmutable` fails.

#### parameters

`$year_or_datepoint` can be:

- a `DateTimeInterface` implementing object
- a string parsable by the `DateTime` constructor.
- an integer interpreted as a timestamp.

`$indexes` represents date and time indexes for, in order:

- month,
- day,
- hour,
- minute,
- second,
- and microseconds.

**Theses arguments will only be taken into account if `$year_or_datepoint` is an integer and at least the month argument is provided.**

In such case, the other time indexes argument will default to `0` and the day index to `1` if not provided.

<p class="message-notice">If no timezone information is given, the returned <code>DateTimeImmutable</code> object will use the current timezone.</p>

#### examples

Using the `$year_or_datepoint` argument

~~~php
use function League\Period\datepoint;

datepoint('yesterday'); // returns new DateTimeImmutable('yesterday')
datepoint('2018');      // returns new DateTimeImmutable('@2018')
datepoint(new DateTime('2018-10-15'));  // returns new DateTimeImmutable('2018-10-15')
datepoint(new DateTimeImmutable('2018-10-15'));  // returns new DateTimeImmutable('2018-10-15')
~~~

Using `$indexes` extra arguments:

~~~php
use function League\Period\datepoint;

datepoint(2018, 2, 1); // returns new DateTimeImmutable('2018-02-01')
datepoint(2018, 2);    // returns new DateTimeImmutable('2018-02-01')
~~~

<p class="message-notice">Because you must provide at least the month index, to get the first day of the year using this function you must provide at least the month index.</p>

~~~php
use function League\Period\datepoint;

datepoint(2018, 1); // returns new DateTimeImmutable('2018-01-01')
~~~

<p class="message-warning">If you only supply a single parameter which is an integer it will be evaluated as a timestamp.</p>

~~~php
use function League\Period\datepoint;

datepoint(2018); // is equivalent to new DateTimeImmutable('@2018')
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
	datepoint('YESTERDAY'),
	duration(600),
	datepoint(new DateTime('+3 WEEKS'))
);

//returns the same object as if you had written

$datePeriod = new DatePeriod(
	new DateTimeImmutable('YESTERDAY'),
	new DateInterval('PT600S'),
	new DateTimeImmutable('+3 WEEKS')
);
~~~

Theses functions are used throughout the package to allow broader input acceptance.