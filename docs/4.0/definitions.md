---
layout: default
title: Concepts and arguments
---

# Definition and Argument

## Concepts

- **datepoint** - An interval consists of two positions in time called datepoints and the continuous portion of time between them. This library assumes that the starting datepoint is included into the interval. Conversely, the ending datepoint is excluded from the specified interval. The starting datepoint is always less than or equal to the ending datepoint. The datepoints are expressed as `DateTimeImmutable` objects.

- **duration** - The continuous portion of time between datepoints is called the duration. This duration is defined as a `DateInterval` object. The duration cannot be negative.

## Arguments

At its core the `Period` class only uses `DateTimeImmutable` and `DateInterval` objects. But because it is sometimes complicated to get your hands on such objects the package comes bundle with two simple functions that are used throughout the library to ensure typesafety.

### League\Period\datepoint

`League\Period\datepoint` converts any input into a `DateTimeImmutable` object if the input can not be converted a PHP `TypeError` is triggered.  
The function accepts:

- `DateTimeInterface` implementing objects
- a string parsable by the `DateTime` constructor.
- an integer interpreted as a timestamp.

### League\Period\duration

`League\Period\duration` converts any input into a `DateInterval` object if the input can not be converted a PHP `TypeError` is triggered.  
The function accepts:

- a `League\Period\Period` object;
- a `DateInterval` object;
- a string parsable by the `DateInterval::createFromDateString` method.
- an integer interpreted as the interval expressed in seconds.

<p class="message-warning"><strong>WARNING:</strong> When the string is not parsable by <code>DateInterval::createFromDateString</code> a <code>DateInterval</code> object representing the <code>0</code> interval is returned as described in <a href="https://bugs.php.net/bug.php?id=50020">PHP bug #50020</a>.</p>

### Examples

~~~php
<?php

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