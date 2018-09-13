---
layout: default
title: Period cheat sheet
---

# Definitions

## Date and time references

- **datepoint** - A period consists of a continuous portion of time between two positions in time called datepoints. This library assumes that the starting datepoint is included into the period. Conversely, the ending datepoint is excluded from the specified period. The starting datepoint is always less than or equal to the ending datepoint. The datepoints are defined as `DateTimeImmutable` objects.

- **duration** - The continuous portion of time between datepoints is called the duration. This duration is defined as a `DateInterval` object. The duration cannot be negative.

## Arguments

At his core the `Period` class only uses `DateTimeImmutable` and `DateInterval` objects. But because it is sometime complicated to get your  hands on such objects the package comes bundles with two simple functions that are used throughout the library to ensure typesafety.

- `League\Period\datepoint`: This function converts any input into a `DateTimeImmutable` object if the input can not be converted a PHP `TypeError` is triggered. The function accepts:

	- `DateTimeInterface` implementing objects
	- a string parsable by the `DateTime` constructor.
    - an integer interpreted as a timestamp.

- `League\Period\duration`: This function converts any input into a `DateInterval` object if the input can not be converted a PHP `TypeError` is triggered. The function accepts:

    - a `DateInterval` object;
    - a string parsable by the `DateInterval::createFromDateString` method.
    - an integer interpreted as the interval expressed in seconds.

<p class="message-warning"><strong>WARNING:</strong> When the string is not parsable by <code>DateInterval::createFromDateString</code> a <code>DateInterval</code> object representing the <code>0</code> interval is returned.</p>

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

Theses functions are used by the `Period` class and the helper functions to allow broader input acceptance.