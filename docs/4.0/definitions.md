---
layout: default
title: Concepts and arguments
---

# Definition and Argument

## Concepts

- **interval** - `Period` is a PHP implementation of a left closed right open datetime interval which consists of two datepoints and the duration between them. This means that:

    - The starting datepoint is included in the interval.
    - The ending datepoint is excluded from the interval.
    - The starting datepoint is always less than or equal to the ending datepoint.

- **datepoint** - A position in time expressed as a `DateTimeImmutable` object.

- **duration** - The continuous portion of time between two datepoints expressed as a `DateInterval` object. The duration cannot be negative.

## Arguments

Since this package relies heavily on `DateTimeImmutable` and `DateInterval` objects and because it is sometimes complicated to get your hands on such objects the package comes bundled with two simple functions that are used throughout the library to ensure typesafety. These functions are defined under the `League\Period` namespace.

<p class="message-notice">the <code>Datepoint</code> and <code>Duration</code> classes are added since <code>version 4.2</code></p>

<p class="message-notice">the <code>datepoint</code> and <code>duration</code> functions are deprecated since <code>version 4.2</code></p>

### datepoint

~~~php
League\Period\Datepoint::create(mixed $datepoint): DateTimeImmutable;
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
use League\Period\Datepoint;
use function League\Period\datepoint;

datepoint('yesterday'); // returns new DateTimeImmutable('yesterday')
datepoint('2018');      // returns new DateTimeImmutable('@2018')
datepoint(2018);        // returns new DateTimeImmutable('@2018')
datepoint(new DateTime('2018-10-15'));  // returns new DateTimeImmutable('2018-10-15')
datepoint(new DateTimeImmutable('2018-10-15'));  // returns new DateTimeImmutable('2018-10-15')

Datepoint::create('yesterday'); // returns new DateTimeImmutable('yesterday')
Datepoint::create('2018');      // returns new DateTimeImmutable('@2018')
Datepoint::create(2018);        // returns new DateTimeImmutable('@2018')
Datepoint::create(new DateTime('2018-10-15'));  // returns new DateTimeImmutable('2018-10-15')
Datepoint::create(new DateTimeImmutable('2018-10-15'));  // returns new DateTimeImmutable('2018-10-15')
~~~

### duration

~~~php
League\Period\Duration::create(mixed $duration): DateInterval;
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
use League\Period\Duration;
use League\Period\Period;
use function League\Period\duration;

duration('1 DAY');                  // returns new DateInterval('P1D')
duration(2018);                     // returns new DateInterval('PT2018S')
duration(new DateInterval('PT1H')); // returns new DateInterval('PT1H')
duration(new Period('now', 'tomorrow'));
// returns (new DateTime('yesterday'))->diff(new DateTime('tomorrow'))

Duration::create('1 DAY');                  // returns new DateInterval('P1D')
Duration::create(2018);                     // returns new DateInterval('PT2018S')
Duration::create(new Period('now', 'tomorrow'));
// returns (new DateTime('yesterday'))->diff(new DateTime('tomorrow'))
~~~
