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

Returns a `DateTimeImmutable` object or throws:

- a `TypeError` if the submitted parameters have the wrong type.
- a PHP `Exception` if creating a `DateTimeImmutable` fails.

#### parameters

- `$datepoint` can be:
    - a `DateTimeInterface` implementing object
    - a string parsable by the `DateTime` constructor.
    - an integer interpreted as a timestamp.

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