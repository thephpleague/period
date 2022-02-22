---
layout: default
title: The Sequence as a Period aware container
---

# A Period Container

The `Sequence` class is design to ease gathering information about multiple `Period` instance.

## Period detection

Because `Period` is a value object, comparison between two instances is done using the `Period::equals` method instead of `==` or `===`.

### Sequence::indexOf

Returns the first offset of the a `Period` object who's equals to the submitted instance.

~~~php
$sequence = new Sequence(Period::after('2018-01-01', '30 DAYS'));
$sequence->indexOf(Period::after('2018-01-01', '30 DAYS')); // 0
$sequence->indexOf(DatePoint::fromDateString('2012-06-03')->day()); // false
~~~

### Sequence::contains

~~~php
public function Sequence::contains(Period $interval, Period ...$intervals);
~~~

Tells whether the sequence contains all the submitted intervals.

~~~php
$sequence = new Sequence(Period::after('2018-01-01', '30 DAYS'));
$sequence->contains(
    Period::fromMonth(2018, 3),
    Period::fromIso80000('!Y-m-d', '[2018-01-20, 2018-03-10)')
); // false
~~~

## Sequence information

### Sequence bounds

Returns the sequence bounds as a `Period` instance. If the sequence is empty `null` is returned.

~~~php
$sequence = new Sequence(
    Period::fromIso80000('!Y-m-d', '[2018-01-01', '2018-01-31)'),
    Period::fromIso80000('!Y-m-d', '[2018-02-10', '2018-02-20)'),
    Period::fromIso80000('!Y-m-d', '[2018-03-01', '2018-03-31)'),
    Period::fromIso80000('!Y-m-d', '[2018-01-20', '2018-03-10)')
);
$sequence->length()->toIso80000('Y-m-d'); // [2018-01-01, 2018-03-31)
(new Sequence())->length();               // null
~~~

### Sequence gaps

Returns the gaps inside the instance. The method returns a new `Sequence` object containing the founded
gaps expressed as `Period` objects.

~~~php
$sequence = new Sequence(
    Period::fromIso80000('!Y-m-d', '[2018-01-01', '2018-01-31)'),
    Period::fromIso80000('!Y-m-d', '[2017-01-01', '2017-01-31)'),
    Period::fromIso80000('!Y-m-d', '[2020-01-01', '2020-01-31)')
);
$gaps = $sequence->gaps(); // a new Sequence object
count($gaps); // 2
~~~

### Sequence intersections

Returns the intersections inside the instance. The method returns a new `Sequence` object containing the founded
intersections expressed as `Period` objects.

~~~php
$sequence = new Sequence(
    Period::fromIso80000('!Y-m-d', '[2018-01-01', '2018-01-31)'),
    Period::fromIso80000('!Y-m-d', '[2017-01-01', '2017-01-31)'),
    Period::fromIso80000('!Y-m-d', '[2020-01-01', '2020-01-31)')
);
$intersections = $sequence->intersections(); // a new Sequence object
$intersections->isEmpty(); // true
~~~

### Sequence unions

Returns the unions inside the instance. The method returns a new `Sequence` object containing the calculated unions expressed as `Period` objects.

~~~php
$sequence = new Sequence(
    Period::fromIso8601('!Y-m-d', '2018-01-01/2018-01-31'),
    Period::fromIso8601('!Y-m-d', '2017-01-01/2017-01-31'),
    Period::fromIso8601('!Y-m-d', '2018-01-15/2018-02-15')
);
$unions = $sequence->unions(); // a new Sequence object
count($unions); // returns 2
~~~

### Sequence total duration

Returns the sum of all instances durations as expressed in seconds.

~~~php
$sequence = new Sequence(
    Period::fromMonth(2017, 1),
    Period::fromMonth(2018, 1)
);
$timestamp = $sequence->totalTimeDuration(); // an integer
~~~

## Container manipulations

### Sequence::subtract

~~~php
Sequence::subtract(Sequence $sequence): Sequence
~~~

This method enables subtracting a `Sequence` instance from another one. It internally use `Period::subtract` and as such is not commutative.

<p class="message-notice"><strong>warning:</strong> This method is not optimized for subtracting two large collection of <code>Sequence</code> objects.</p>

The following diagram gives you an overview of how the method works:

[![](/media/sequence-substract2.png "Sequence subtraction: How it works")](/media/sequence-substract.png)

#### Examples

~~~php
$presence = new Sequence(
    Period::fromIso8601('!Y-m-d', '2000-01-01/2000-01-10'),
    Period::fromIso8601('!Y-m-d', '2000-01-12/2000-01-20')
);

$absence = new Sequence(
    Period::fromIso8601('!Y-m-d', '2000-01-08/2000-01-10'),
    Period::fromIso8601('!Y-m-d', '2000-01-12/2000-01-15')
);

$diff = $presence->subtract($absence);
$diff[0]->toIso80000('Y-m-d'); //[2000-01-01, 2000-01-08)
$diff[1]->toIso80000('Y-m-d'); //[2000-01-15, 2000-01-20)

$diff = $absence->subtract($presence);
$diff->isEmpty(); // true
~~~
