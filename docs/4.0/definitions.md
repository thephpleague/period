---
layout: default
title: Concepts and arguments
---

# Definition and Argument

## Concepts

- **period** - A left closed right open interval which consists of two datepoints and the duration between them. This library assumes that the starting datepoint is included into the interval. Conversely, the ending datepoint is excluded from the specified interval. The starting datepoint is always less than or equal to the ending datepoint.

- **datepoint** - A position in time expressed as a `DateTimeImmutable` object.

- **duration** - The continuous portion of time between two datepoints is called the duration. This duration is defined as a `DateInterval` object. The duration cannot be negative.

## Arguments

Since this package relies heavily on `DateTimeImmutable` and `DateInterval` objects and because it is sometimes complicated to get your hands on such objects the package comes bundled with two simple functions that are used throughout the library to ensure typesafety.

### datepoint

`League\Period\datepoint` converts any input into a `DateTimeImmutable` object if the input can not be converted a PHP `TypeError` is triggered.

~~~php
<?php

function League\Period\datepoint(mixed $datepoint): DateTimeImmutable;
~~~

#### parameter

`$datepoint` can be:

- a `DateTimeInterface` implementing object
- a string parsable by the `DateTime` constructor.
- an integer interpreted as a timestamp.


### duration

`League\Period\duration` converts any input into a `DateInterval` object if the input can not be converted a PHP `TypeError` is triggered.

~~~php
<?php

function League\Period\duration(mixed $duration): DateInterval;
~~~

#### parameter

`$duration` can be:

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