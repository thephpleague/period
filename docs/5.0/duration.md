---
layout: default
title: The Duration class
---

# The Duration class

A duration is the continuous portion of time between two datepoints expressed as a `DateInterval` object. The duration cannot be negative.

The `Duration` class is introduced to ease duration manipulation. This class decorates PHP's `DateInterval` class to provide additional
means to create a `DateInterval` instance.

## Constructors

The `Duration::__construct` method is private, as such, to instantiate a new `Duration` object use one of the following named constructors:

### Duration::fromInterval

~~~php
public Duration::fromInterval(DateInterval $duration): self
~~~

Returns a `Duration` instance from a `DateInterval` object.

### Duration::fromDateString

~~~php
public Duration::fromDateString(string $duration): self
~~~

Returns a `Duration` instance from a string that can be interpreted by `DateInterval::createFromDateString` named constructor.

### Duration::fromChronoString

~~~php
public Duration::fromChronoString(string $duration): self
~~~

Returns a `Duration` instance from a string representing a chronometer format `+/-HH:MM::SS.FFFFFF`

<p class="message-notice">The hour and fraction units are optionals</p>

### Duration::fromTimeString

~~~php
public Duration::fromTimeString(string $duration): self
~~~

Returns a `Duration` instance from a string representing a time string format in accordance with ISO8601 `+/-HH:MM::SS.FFFFFF`.

This feature differs from `Duration::fromChronoString` method by requiring the presence of at least the hour and the minute unit.

<p class="message-notice">The second and fraction units are optionals</p>

### Duration::fromSeconds

~~~php
public Duration::fromSeconds(int $seconds, int $fractions): self
~~~

Returns a `Duration` instance from a second and its fraction both expressed as integer values.

### Duration::fromIsoString

~~~php
public Duration::fromIsoString(string $duration): self
~~~

Returns a `Duration` instance from an ISO8601 interval specification parsable by `DateInterval::__construct` but not only.
This method also handles the presence of fractions in the second part.

All these methods converts their inputs into a `Duration` object or throws an exception otherwise.

#### Examples

~~~php
use League\Period\Duration;

Duration::fromDateInterval(new DateInterval('PT1H'));     // is equivalent to new Duration(new DateInterval('PT1H'))
Duration::fromDateString('1 DAY');                    // is equivalent to new Duration(DateInterval::createFromDateString('1 DAY'))
Duration::fromSeconds(2018, 300_000);                 // is equivalent to new Duration(new DateInterval('PT2018.3S'))
Duration::fromChronoString('12:30');                  // is equivalent to new Duration(new DateInterval('PT12M30S'))
Duration::fromChronoString('12:30:34.8');             // is equivalent to new Duration(new DateInterval('PT12H30M34.8S'))
Duration::fromTimeString('12:30');                    // is equivalent to new Duration(new DateInterval('PT12H30M'))
Duration::fromTimeString('12:30:34.8');               // is equivalent to new Duration(new DateInterval('PT12H30M34.8S'))
~~~

## Accessing the underlying DateInterval instance

To access the decorated `DateInterval` instance use the `Duration::toInterval` method.

~~~php
public readonly DateInterval Duration::interval
~~~

#### Examples

~~~php
$dateInterval = Duration::fromChronoString('12:30')->interval; //returns a DateInterval object
~~~

## Duration mutation method

### Adjusting duration according to a datepoint

~~~php
public Duration::adjustedTo(DateTimeInterface $date): self
~~~

Returns a new instance with recalculate duration according to the given datepoint.

~~~php
$duration = Duration::create('29 days');                              // is equivalent to new Duration(DateInterval::createFromDateString('29 days'))
$duration->adjustedTo(new DateTime('2019-02-01'));                    // is equivalent to new DateInterval('P1M1D') using a non leap year
$duration->adjustedTo(DatePoint::fromDateString('2020-02-01')->date); // is equivalent to new DateInterval('P1M') using a leap year
// in both cases the interval `days` property stays at 29 days
~~~

<p class="message-notice">The returned object depends on the date time <strong>and</strong> timezone of the <code>$date</code></p>
