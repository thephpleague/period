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

Duration::create('1 DAY');                  // returns new DateInterval('P1D')
Duration::create(2018);                     // returns new DateInterval('PT2018S')
Duration::create(new DateInterval('PT1H')); // returns new DateInterval('PT1H')
Duration::create(new Period('now', 'tomorrow'));
// returns (new DateTime('yesterday'))->diff(new DateTime('tomorrow'))
~~~

## Duration::__construct

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

## Duration mutation method

### Removing carry over

~~~php
public Duration::withoutCarryOver(void): self
~~~

Returns a new instance with recalculate time and date segments to remove carry over points. If the recalculate interval does not change the current object then it is returned as is, otherwise a new object is returned. In any cases the current object is not changed. The EPOCH time is used as the reference day to perform the calculation.

~~~php
$duration = Duration::create('33 days');
echo $duration; // 'P33D'
echo $duration->withoutCarryOver(); // 'P1M2D'
~~~
