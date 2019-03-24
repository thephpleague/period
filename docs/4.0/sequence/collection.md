---
layout: default
title: The Sequence as a Period collection class
---

# A Period Collection

The `Sequence` class provides several methods to ease accessing its content using well established Collection methods.

<p class="message-warning">Values are <strong>always</strong> indexed which means that whenever a value is removed the list is either re-indexed to avoid missing indexes or a new instance is returned.</p>

## Sequence status

### Sequence::isEmpty

Tells whether the sequence contains no interval.

~~~php
$sequence = new Sequence(new Period('2018-01-01', '2018-01-31'));
$sequence->isEmpty(); // false
~~~

### Sequence::count

Returns the number of `Period` instance contains in the `Sequence` object. The object implements PHP's `Countable` interface.

~~~php
$sequence = new Sequence(new Period('2018-01-01', '2018-01-31'));
count($sequence); // returns 1
~~~

## Getter methods

### ArrayAccess, IteratorAggregate

<p class="message-info"><code>ArrayAccess</code> support is added in <code>version 4.2</code></p>

The `Sequence` class implements PHP's `ArrayAccess`, `IteratorAggregate` interfaces so you can at any given time iterate over each interval using the `foreach` loop or access any individual `Period` instance according to its offset using array notation.

~~~php
$sequence = new Sequence(
    new Period('2018-01-01', '2018-01-31'),
    new Period('2018-02-10', '2018-02-20'),
    new Period('2018-03-01', '2018-03-31'),
    new Period('2018-01-20', '2018-03-10')
);
foreach ($sequence as $interval) {
	//$interval is a League\Period\Period object
}

$sequnce[3]; //new Period('2018-01-20', '2018-03-10')
~~~

### Sequence::get

Returns the interval found at the given offset.

<p class="message-info"><code>ArrayAccess</code> support is added in <code>version 4.2</code></p>

<p class="message-warning">An <code>InvalidIndex</code> exception will be thrown if the <code>$offset</code> does not exists in the instance. In doubt, use <code>Sequence::indexOf</code> before using this method or <code>isset</code> since <code>version 4.2</code>.</p>

~~~php
$sequence = new Sequence(
    new Period('2018-01-01', '2018-01-31'),
    new Period('2018-02-10', '2018-02-20'),
    new Period('2018-03-01', '2018-03-31'),
    new Period('2018-01-20', '2018-03-10')
);
$sequence->get(3)->format('Y-m-d'); //returns [2018-01-20, 2018-03-10)
$sequence->get(42); //throws an League\Period\InvalidIndex exception
$sequence[3]->format('Y-m-d');  //returns [2018-01-20, 2018-03-10)
$sequence[42]; //throws an League\Period\InvalidIndex exception
~~~

## Setter methods

### Sequence::push

Adds new intervals at the end of the sequence.

<p class="message-info"><code>ArrayAccess</code> support is added in <code>version 4.2</code></p>

~~~php
$sequence = new Sequence(new Period('2018-01-01', '2018-01-31'));
$sequence->get(0)->format('Y-m-d'); // [2018-01-01, 2018-01-31)
$sequence->push(
    new Period('2018-01-01', '2018-01-31'),
    new Period('2018-02-10', '2018-02-20'),
    new Period('2018-03-01', '2018-03-31'),
    new Period('2018-01-20', '2018-03-10')
);
$sequence->get(0)->format('Y-m-d'); // [2018-01-01, 2018-01-31)
$sequence[] = new Period('2018-12-20', '2018-12-21');
$sequence[4]->format('Y-m-d'); // [2018-12-20, 2018-12-21)
~~~

### Sequence::unshift

Adds new intervals at the start of the sequence.

<p class="message-notice">The sequence is re-indexed after the addition.</p>

~~~php
$sequence = new Sequence(new Period('2018-01-01', '2018-01-31'));
$sequence->get(0)->format('Y-m-d'); // [2018-01-01, 2018-01-31)
$sequence->unshift(
    new Period('2018-02-10', '2018-02-20'),
    new Period('2018-03-01', '2018-03-31'),
    new Period('2018-01-20', '2018-03-10')
);
$sequence->get(0)->format('Y-m-d'); // [2018-02-10, 2018-02-20)
~~~

### Sequence::insert

Adds intervals at a specify offset.

<p class="message-notice">The sequence is re-indexed on the right side after the addition.</p>

~~~php
$sequence = new Sequence(
    new Period('2018-01-01', '2018-02-01'),
    new Period('2018-04-01', '2018-05-01'),
);
$sequence->get(1)->format('Y-m-d'); // [2018-04-01, 2018-05-01)
$sequence->insert(1,
    new Period('2018-02-01', '2018-03-01'),
    new Period('2018-03-01', '2018-04-01')
);
count($sequence); // 4
$sequence->get(1)->format('Y-m-d'), PHP_EOL; // [2018-02-01, 2018-03-01)
~~~

### Sequence::set

Updates the interval at the specify offset.

<p class="message-info"><code>ArrayAccess</code> support is added in <code>version 4.2</code></p>

<p class="message-warning">An <code>InvalidIndex</code> exception will be thrown if the <code>$offset</code> does not exists in the instance. In doubt, use <code>Sequence::indexOf</code> before using this method or <code>isset</code> since <code>version 4.2</code>.</p>

~~~php
$sequence = new Sequence(
    new Period('2018-01-01', '2018-01-31'),
    new Period('2018-02-10', '2018-02-20'),
);
$sequence->set(0, new Period('2012-01-01', '2012-01-31'));
$sequence->set(42, new Period('2012-01-01', '2012-01-31')); //throws InvalidIndex
$sequence[1] = new Period('2012-01-01', '2012-01-31');
$sequence[42] = new Period('2012-01-01', '2012-01-31')); //throws InvalidIndex
~~~

### Sequence::remove

Removes an interval from the collection at the given offset and returns it.

<p class="message-info"><code>ArrayAccess</code> support is added in <code>version 4.2</code></p>

<p class="message-notice">The sequence is re-indexed after removal.</p>

<p class="message-warning">An <code>InvalidIndex</code> exception will be thrown if the <code>$offset</code> does not exists in the instance. In doubt, use <code>Sequence::indexOf</code> before using this method or <code>isset</code> since <code>version 4.2</code>.</p>

~~~php
$sequence = new Sequence(
    new Period('2018-01-01', '2018-01-31'),
    new Period('2018-02-10', '2018-02-20'),
    new Period('2018-03-01', '2018-03-31'),
    new Period('2018-01-20', '2018-03-10')
);
$interval = $sequence->remove(3);
$sequence->remove(42);//throws InvalidIndex
unset($sequence[2]);
unset($sequence[42]);//throws InvalidIndex
~~~

### Sequence::clear

Clear the sequence by removing all intervals.

~~~php
$sequence = new Sequence(
    new Period('2018-01-01', '2018-01-31'),
    new Period('2018-02-10', '2018-02-20'),
    new Period('2018-03-01', '2018-03-31'),
    new Period('2018-01-20', '2018-03-10')
);
count($sequence); // 4
$sequence->clear();
count($sequence); // 0
~~~

## Conversion methods

### JsonSerializable

The `Sequence` class implements PHP's `JsonSerializable` interfaces so you can export its content using JSON representation.

~~~php
$sequence = new Sequence(
    new Period('2018-01-01', '2018-01-31'),
    new Period('2018-02-10', '2018-02-20'),
    new Period('2018-03-01', '2018-03-31'),
    new Period('2018-01-20', '2018-03-10')
);

echo json_encode($sequence, JSON_PRETTY_PRINT);
// [
//    {
//        "startDate": "2017-12-31T23:00:00.000000Z",
//        "endDate": "2018-01-30T23:00:00.000000Z"
//    },
//    {
//        "startDate": "2018-02-09T23:00:00.000000Z",
//        "endDate": "2018-02-19T23:00:00.000000Z"
//    },
//    {
//        "startDate": "2018-02-28T23:00:00.000000Z",
//        "endDate": "2018-03-30T22:00:00.000000Z"
//    },
//    {
//        "startDate": "2018-01-19T23:00:00.000000Z",
//        "endDate": "2018-03-09T23:00:00.000000Z"
//    }
//]
~~~

### Sequence::toArray

Returns a native PHP array representation of the collection.

~~~php
$sequence = new Sequence(
    new Period('2018-01-01', '2018-01-31'),
    new Period('2018-02-10', '2018-02-20'),
    new Period('2018-03-01', '2018-03-31'),
    new Period('2018-01-20', '2018-03-10')
);
$array = $sequence->toArray();
~~~

## Filtering the sequence

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

### Sequence::sort

Sorts the current instance according to the given comparison callable and maintain index association.

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

foreach ($sequence as $offset => $interval) {
    echo $offset, ' -> ', $interval->format('Y-m-d'), PHP_EOL; //0 -> [2018-01-01, 2018-01-31)...
}

$compare = static function (Period $interval1, Period $interval2): int {
    return $interval1->getEndDate() <=> $interval2->getEndDate();
};

$sequence->sort($compare);
foreach ($sequence as $offset => $interval) {
    echo $offset, ' -> ', $interval->format('Y-m-d'), PHP_EOL; //1 -> [2017-01-01, 2017-01-31)...
}
~~~

## Manipulations methods

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
    echo $offset, ' -> ', $interval->format('Y-m-d'), PHP_EOL; //0 -> [2018-01-01, 2018-01-31)...
}

foreach ($newSequence as $offset => $interval) {
    echo $offset, ' -> ', $interval->format('Y-m-d'), PHP_EOL; //0 -> [2017-01-01, 2017-01-31)...
}
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

### Sequence::reduce

<p class="message-info">new since <code>version 4.4</code></p>

Iteratively reduces the sequence to a single value using a callback. The returned value is the carry value of the final iteration, or the initial value if the sequence was empty.

The reducer is a `callable` whose signature is as follows:

~~~php
function($carry, Period $interval [, int $offset]): mixed
~~~

It takes up to three (3) parameters:

- `$carry` : the optional initial carry value or null
- `$interval` : the Sequence value which is a `Period` object
- `$offset` : the Sequence value corresponding offset

~~~php
$sequence = new Sequence(
    new Period('2018-01-01', '2018-01-31'),
    new Period('2019-01-01', '2019-01-31'),
    new Period('2020-01-01', '2020-01-31')
);

$func = static function ($carry, Period $interval): Period {
    if (null === $carry) {
        return $interval;
    }
    return $carry->merge($interval);
};

$mergePeriod = $sequence->reduce($func);
$mergePeriod->format('Y-m-d'); // [2018-01-01, 2020-01-31)
~~~
