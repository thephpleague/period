---
layout: default
title: The Sequence class
---

# The Duration object

<p class="message-info">The <code>Duration</code> class is introduced in <code>version 4.2</code>.</p>

A duration is the continuous portion of time between two datepoints expressed as a `DateInterval` object. The duration cannot be negative.

The `Duration` class is introduced to ease duration manipulation. This class extends PHP's `DateInterval` class by adding a new named constructor.

## Named constructor

### Duration::create

~~~php
public Duration::create($duration): self
~~~

Converts its single input into a `Duration` object or throws a `TypeError` otherwise.

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

Duration::create('1 DAY');                  // returns new Duration('P1D')
Duration::create(2018);                     // returns new Duration('PT2018S')
Duration::create(new DateInterval('PT1H')); // returns new Duration('PT1H')
Duration::create(new Period('now', 'tomorrow'));
// returns (new DateTime('yesterday'))->diff(new DateTime('tomorrow'))
~~~

### Duration::fromChrono

~~~php
public Duration::fromChrono(string $chrono): self
~~~

Converts its single input, a string representation of a chronometer into a `Duration` object or throws a `Exception` otherwise.

#### paramter

`$chrono` is a representation of time without any date part which according to the following format `H:M:S.f`.  
The chronometer unit are always positive or equal to `0` except for the second unit which accept a fraction part.

When the string is not parsable, an `Exception` is thrown.

### Examples

~~~php
use League\Period\Duration;
use League\Period\Period;

Duration::fromChrono('28');         // returns new Duration('PT28S')
Duration::fromChrono('12:30');      // returns new Duration('PT12M30S')
Duration::fromChrono('02:03:25.8'); // returns new Duration('PT2H3M25.8S')
~~~

## Default constructor

The constructor supports fraction on the smallest value.

For instance, the following is works while throwing an Exception on `DateInterval`.

~~~php
use League\Period\Duration;

$duration = new Duration('PT5M0.5S');
$duration->f; //0.5;

new DateInterval('PT5M0.5S'); // will throw an Exception
~~~

## Duration representations

### String representation

~~~php
public Duration::__toString(void): string
~~~

Returns the string representation of a `Duration` object using [ISO8601 time interval representation](http://en.wikipedia.org/wiki/ISO_8601#Durations).

~~~php
$duration = new Duration('PT5M0.5S');
echo $duration; // 'PT5M0.5S'
~~~

As per the specification the smallest value (ie the second) can accept a decimal fraction.