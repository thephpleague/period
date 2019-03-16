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
$sequence = new Sequence(new Period('2018-01-01', '2018-01-31'));
$sequence->indexOf(new Period('2018-03-01', '2018-03-31')); // 0
$sequence->indexOf(Datepoint::create('2012-06-03')->getDay()); // false
~~~

### Sequence::contains

~~~php
public function Sequence::contains(Period $interval, Period ...$intervals);
~~~

Tells whether the sequence contains all the submitted intervals.

~~~php
$sequence = new Sequence(new Period('2018-01-01', '2018-01-31'));
$sequence->contains(
    new Period('2018-03-01', '2018-03-31'),
    new Period('2018-01-20', '2018-03-10')
); // false
~~~

## Sequence information

### Sequence boundaries

<p class="message-info"><code>Sequence::boundaries</code> since <code>version 4.4</code>.</p>
<p class="message-warning"><code>Sequence::getBoundaries</code> is deprecated and will be remove in the next major release.</p>

Returns the sequence boundaries as a `Period` instance. If the sequence is empty `null` is returned.

~~~php
$sequence = new Sequence(
    new Period('2018-01-01', '2018-01-31'),
    new Period('2018-02-10', '2018-02-20'),
    new Period('2018-03-01', '2018-03-31'),
    new Period('2018-01-20', '2018-03-10')
);
$sequence->boundaries()->format('Y-m-d'); // [2018-01-01, 2018-03-10)
(new Sequence())->boundaries(); // null
~~~

### Sequence gaps

<p class="message-info"><code>Sequence::gaps</code> since <code>version 4.4</code>.</p>
<p class="message-warning"><code>Sequence::getGaps</code> is deprecated and will be remove in the next major release.</p>

Returns the gaps inside the instance. The method returns a new `Sequence` object containing the founded
gaps expressed as `Period` objects.

~~~php
$sequence = new Sequence(
    new Period('2018-01-01', '2018-01-31'),
    new Period('2017-01-01', '2017-01-31'),
    new Period('2020-01-01', '2020-01-31')
);
$gaps = $sequence->gaps(); // a new Sequence object
count($gaps); // 2
~~~

### Sequence intersections

<p class="message-info"><code>Sequence::intersections</code> since <code>version 4.4</code>.</p>
<p class="message-warning"><code>Sequence::getIntersections</code> is deprecated and will be remove in the next major release.</p>

Returns the intersections inside the instance. The method returns a new `Sequence` object containing the founded
intersections expressed as `Period` objects.

~~~php
$sequence = new Sequence(
    new Period('2018-01-01', '2018-01-31'),
    new Period('2017-01-01', '2017-01-31'),
    new Period('2020-01-01', '2020-01-31')
);
$intersections = $sequence->intersections(); // a new Sequence object
$intersections->isEmpty(); // true
~~~

### Sequence unions

<p class="message-info">Since <code>version 4.4</code></p>

Returns the unions inside the instance. The method returns a new `Sequence` object containing the calculated unions expressed as `Period` objects.

~~~php
$sequence = new Sequence(
    new Period('2018-01-01', '2018-01-31'),
    new Period('2017-01-01', '2017-01-31'),
    new Period('2018-01-15', '2018-02-15')
);
$unions = $sequence->unions(); // a new Sequence object
count($unions); // returns 2
~~~

#### Sequence total timestamp interval

<p class="message-info">Since <code>version 4.7</code></p>

Returns the sum of all instances durations as expressed in seconds.

~~~php
$sequence = new Sequence(
    Period::fromMonth(2017, 1),
    Period::fromMonth(2018, 1)
);
$timestamp = $sequence->getTotalTimestampInterval(); // a float
~~~

<p class="message-notice">The return value will always be lesser or equals to the result of <code>Sequence::boundaries()->getTimestampInterval()</code></p>
.