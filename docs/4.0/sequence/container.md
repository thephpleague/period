---
layout: default
title: The Sequence as a Period aware container
---

# A Period Container

The `Sequence` class is design to ease gathering information about multiple `Period` instance.

## Accessing intervals information

### Sequence::isEmpty

Tells whether the sequence contains no interval.

~~~php
$sequence = new Sequence(new Period('2018-01-01', '2018-01-31'));
$sequence->isEmpty(); // false
~~~

### Sequence::getBoundaries

Returns the sequence boundaries as a `Period` instance. If the sequence is empty `null` is returned.

~~~php
$sequence = new Sequence(
    new Period('2018-01-01', '2018-01-31'),
    new Period('2018-02-10', '2018-02-20'),
    new Period('2018-03-01', '2018-03-31'),
    new Period('2018-01-20', '2018-03-10')
);
$sequence->getBoundaries()->format('Y-m-d'); // [2018-01-01, 2018-03-10)
(new Sequence())->getBoundaries(); // null
~~~

### Sequence::getGaps

Returns the gaps inside the instance. The method returns a new `Sequence` object containing the founded
gaps expressed as `Period` objects.

~~~php
$sequence = new Sequence(
    new Period('2018-01-01', '2018-01-31'),
    new Period('2017-01-01', '2017-01-31'),
    new Period('2020-01-01', '2020-01-31')
);
$gaps = $sequence->getGaps(); // a new Sequence object
count($gaps); // 2
~~~

### Sequence::getIntersections

Returns the intersections inside the instance. The method returns a new `Sequence` object containing the founded
intersections expressed as `Period` objects.

~~~php
$sequence = new Sequence(
    new Period('2018-01-01', '2018-01-31'),
    new Period('2017-01-01', '2017-01-31'),
    new Period('2020-01-01', '2020-01-31')
);
$intersections = $sequence->getIntersections(); // a new Sequence object
$intersections->isEmpty(); // true
~~~

### Sequence::indexOf

Returns the offset of the given `Period` object. The comparison of two intervals is done using `Period::equals` method. If no offset is found `false` is returned.

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

## Performing sequence manipulations

### Sequence::some

Tells whether some intervals in the current instance satisfies the predicate.

The predicate is a `callable` whose signature is as follows:

~~~php
function(Period $interval [, int $offset]): bool
~~~

It takes up to two (2) parameters:

- `$interval` : the Sequence value which is a `Period` object
- `$offset` : the Sequence value corresponding offset

~~~php
$sequence = new Sequence(
    new Period('2018-01-01', '2018-01-31'),
    new Period('2017-01-01', '2017-01-31'),
    new Period('2020-01-01', '2020-01-31')
);

$predicate = static function (Period $interval): bool {
    return $interval->contains('2018-01-15');
};

$sequence->some($predicate); // true
~~~

### Sequence::every

Tells whether all intervals in the current instance satisfies the predicate.

The predicate is a `callable` whose signature is as follows:

~~~php
function(Period $interval [, int $offset]): bool
~~~

It takes up to two (2) parameters:

- `$interval` : the Sequence value which is a `Period` object
- `$offset` : the Sequence value corresponding offset

~~~php
$sequence = new Sequence(
    new Period('2018-01-01', '2018-01-31'),
    new Period('2017-01-01', '2017-01-31'),
    new Period('2020-01-01', '2020-01-31')
);

$predicate = static function (Period $interval): bool {
    return $interval->contains('2018-01-15');
};

$sequence->every($predicate); // false
~~~

### Sequence::filter

Filters the sequence according to the given predicate. This method **MUST** retain the state of the current instance, and return an instance that contains the filtered intervals with their keys re-indexed.

The predicate is a `callable` whose signature is as follows:

~~~php
function(Period $interval [, int $offset]): bool
~~~

It takes up to two (2) parameters:

- `$interval` : the Sequence value which is a `Period` object
- `$offset` : the Sequence value corresponding offset

~~~php
$sequence = new Sequence(
    new Period('2018-01-01', '2018-01-31'),
    new Period('2019-01-01', '2019-01-31'),
    new Period('2020-01-01', '2020-01-31')
);

$predicate = static function (Period $interval): bool {
    return $interval->equals(new Period('2018-01-01', '2018-01-31'));
};

$newSequence = $sequence->filter($predicate);
count($sequence); // 3
count($newSequence); //1
~~~

### Sequence::sorted

Returns an instance sorted according to the given comparison callable but does not maintain index association. This method **MUST** retain the state of the current instance, and return an instance that contains the sorted intervals with their keys re-indexed.

The comparison algorithm is a `callable` whose signature is as follows:

~~~php
function(Period $interval1, Period $interval2): int
~~~

It must return an integer less than, equal to, or greater than zero if the first argument is considered to be respectively less than, equal to, or greater than the second.

~~~php
$sequence = new Sequence(
    new Period('2018-01-01', '2018-01-31'),
    new Period('2017-01-01', '2017-01-31'),
    new Period('2020-01-01', '2020-01-31')
);

$compare = static function (Period $interval1, Period $interval2): int {
    return $interval1->getEndDate() <=> $interval2->getEndDate();
};

$newSequence = $sequence->sorted($compare);
foreach ($sequence as $offset => $interval) {
    echo $offset; //0, 1, 2
}

foreach ($newSequence as $offset => $interval) {
    echo $offset; // 2, 0, 1
}
~~~

### Sequence::map

<p class="message-info">new since <code>version 4.2</code></p>

Map the sequence according to the given function. This method **MUST** retain the state of the current instance, and return an instance that contains the mapped intervals. The keys are not indexed.

The mapper is a `callable` whose signature is as follows:

~~~php
function(Period $interval [, int $offset]): Period
~~~

It takes up to two (2) parameters:

- `$interval` : the Sequence value which is a `Period` object
- `$offset` : the Sequence value corresponding offset

~~~php
$sequence = new Sequence(
    new Period('2018-01-01', '2018-01-31'),
    new Period('2019-01-01', '2019-01-31'),
    new Period('2020-01-01', '2020-01-31')
);

$func = static function (Period $interval): Period {
    return $interval->moveEndDate('+ 1 DAY');
};

$newSequence = $sequence->map($func);
count($sequence); // 3
count($newSequence); //3
$newSequence->get(2)->format('Y-m-d'); // [2020-01-01, 2020-02-01)
~~~