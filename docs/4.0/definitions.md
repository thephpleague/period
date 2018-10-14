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
~~~

Converts its single input into a `DateTimeImmutable` object or throws a `TypeError` otherwise.

#### parameter

`$datepoint` can be:

- a `DateTimeInterface` implementing object
- a string parsable by the `DateTime` constructor.
- an integer interpreted as a timestamp.

<p class="message-notice">If no timezone information is given, the returned <code>DateTimeImmutable</code> object will use the current timezone.</p>

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