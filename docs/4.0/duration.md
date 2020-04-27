---
layout: default
title: The Duration class
---

# The Duration class

<p class="message-info">The <code>Duration</code> class is introduced in <code>version 4.2</code>.</p>

A duration is the continuous portion of time between two datepoints expressed as a `DateInterval` object. The duration cannot be negative.

The `Duration` class is introduced to ease duration manipulation. This class extends PHP's `DateInterval` class.

## Constructors

### Duration::create

<p class="message-info">Since <code>version 4.5</code> chronometer format is supported.</p>

~~~php
public Duration::create($duration): self
~~~

Converts its single input into a `Duration` object or throws a `TypeError` otherwise.

#### parameter

`$duration` can be:

- a `League\Period\Period` object;
- a `DateInterval` object;
- a float interpreted as the interval expressed in seconds.
- a string representing a chronometer format `+/-HH:MM::SS.FFFFFF`
- a string following the ISO8601 interval specification parsable by `DateInterval::__construct` *since 4.11.0*
- a string parsable by the `DateInterval::createFromDateString` method.

<p class="message-warning"><strong>WARNING:</strong> When the string is not parsable by <code>DateInterval::createFromDateString</code> a <code>DateInterval</code> object representing the <code>0</code> interval is returned as described in <a href="https://bugs.php.net/bug.php?id=50020">PHP bug #50020</a>.</p>

#### Examples

~~~php
use League\Period\Duration;
use League\Period\Period;

Duration::create('1 DAY');                  // returns new Duration('P1D')
Duration::create(2018);                     // returns new Duration('PT2018S')
Duration::create('PT1H');                   // returns new Duration('PT1H')
Duration::create(new DateInterval('PT1H')); // returns new Duration('PT1H')
Duration::create('12:30');                  // returns new Duration('PT12M30S')  
Duration::create(new Period('now', 'tomorrow'));
// returns (new DateTime('yesterday'))->diff(new DateTime('tomorrow'))
~~~

### Duration::createFromChronoString

<p class="message-info">Since <code>version 4.11</code>.</p>

You can specifically instantiate a `Duration` instance from a timer like string format `+/-HH:MM::SS.FFFFFF`.
This feature was already supported via the `Duration::create` method but is now accessible stand alone.

#### Examples

~~~php
use League\Period\Duration;

Duration::createFromChronoString('12:30');  // returns new Duration('PT12M30S')  
~~~

On error a `League\Period\Exception` will be thrown.

### Duration::createFromTimeString

<p class="message-info">Since <code>version 4.11</code>.</p>

You can specifically instantiate a `Duration` instance from a time string format in accordance with ISO8601 `+/-HH:MM::SS.FFFFFF`.
This feature differs from `Duration::createFromChronoString` method by requiring the presence of at least the hour ans the minute unit.

#### Examples

~~~php
use League\Period\Duration;

Duration::createFromChronoString('12:30');     // returns new Duration('PT12H30M')
Duration::createFromChronoString('12:30:34.8');  // returns new Duration('PT12H30M34.8S')
~~~

On error a `League\Period\Exception` will be thrown.

### Duration::createFromSeconds

<p class="message-info">Since <code>version 4.11</code>.</p>

You can specifically instantiate a `Duration` instance from seconds with optional fractions.
This feature was already supported via the `Duration::create` method but it is now accessible stand alone.

#### Examples

~~~php
use League\Period\Duration;

Duration::createFromSeconds(28.5); // returns Duration::createFromDateString('28 seconds 500000 microseconds')  
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

<p class="message-notice">Deprecated since <code>version 4.5</code>. You should use <code>Duration::format</code> instead.</p>

~~~php
public Duration::__toString(void): string
~~~

Returns the string representation of a `Duration` object using [ISO8601 time interval representation](http://en.wikipedia.org/wiki/ISO_8601#Durations).

~~~php
$duration = new Duration('PT5M0.5S');
echo $duration; // 'PT5M0.5S'
~~~

As per the specification the smallest value (ie the second) can accept a decimal fraction.

## Duration mutation method

### Adjusting duration according to a datepoint

<p class="message-info">Since <code>version 4.6</code></p>
<p class="message-notice">Replaces and deprecates the <code>withoutCarryOver</code> method introduced in <code>version 4.5</code>.</p>

~~~php
public Duration::adjustedTo($reference_date): self
~~~

Returns a new instance with recalculate duration according to the given datepoint. If the recalculate interval does not change the current object then it is returned as is, otherwise a new object is returned.

The reference datepoint can be any valid vaue accepted by the `Datepoint::create` named constructor.  

~~~php
$duration = Duration::create('29 days');
echo $duration; // 'P29D'
echo $duration->adjustedTo('2019-02-01'); // 'P1M1D' using a non leap year
echo $duration->adjustedTo('2020-02-01'); // 'P1M'   using a leap year
~~~

<p class="message-notice">The returned object depends on the date time <strong>and</strong> timezone or the <code>$reference_date</code></p>
