---
layout: default
title: Helper Functions
---

# Helper functions

<p class="message-warning">Since <code>version 4.2</code> both functions are deprecated and you are encouraged to use the classes instead.</p>

Since this package relies heavily on `DateTimeImmutable` and `DateInterval` objects and because it is sometimes complicated to get your hands on such objects the package comes bundled with two simple functions defined under the `League\Period` namespace:

- League\Period\datepoint;
- League\Period\duration;

### datepoint

<p class="message-notice">the <code>League\Period\datepoint</code> function is deprecated since <code>version 4.2</code>. Your encouraged to use the <code>League\Period\Datepoint</code> class instead.</p>

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

<p class="message-notice">The <code>League\Period\duration</code> function is deprecated since <code>version 4.2</code>. Your encouraged to use the <code>League\Period\Duration</code> class instead.</p>

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
use League\Period\Period;
use function League\Period\duration;

duration('1 DAY');                  // returns new DateInterval('P1D')
duration(2018);                     // returns new DateInterval('PT2018S')
duration(new DateInterval('PT1H')); // returns new DateInterval('PT1H')
duration(new Period('now', 'tomorrow'));
// returns (new DateTime('yesterday'))->diff(new DateTime('tomorrow'))
~~~
