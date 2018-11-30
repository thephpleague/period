---
layout: default
title: The Sequence as a Period collection class
---

# A Period Collection

The `Sequence` class provides several methods to ease accessing its content using well established Collection methods.

<p class="message-warning">Values are <strong>always</strong> indexed which means that whenever a value is removed the list is either re-indexed to avoid missing indexes or a new instance is returned.</p>

## Getter methods

### Countable, IteratorAggregate

The `Sequence` class implements the PHP's `Countable` and `IteratorAggregate` interfaces so you can at any given time know the number of `Period` instance contains in the collection and iterate over each one of them using the `foreach` loop.

~~~php
$sequence = new Sequence(
    new Period('2018-01-01', '2018-01-31'),
    new Period('2018-02-10', '2018-02-20'),
    new Period('2018-03-01', '2018-03-31'),
    new Period('2018-01-20', '2018-03-10')
);
count($sequence); // 4
foreach ($sequence as $interval) {
	//$interval is a League\Period\Period object
}
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

### Sequence::isEmpty

Tells whether the sequence contains no interval.

~~~php
$sequence = new Sequence(new Period('2018-01-01', '2018-01-31'));
$sequence->isEmpty(); // false
~~~

### Sequence::get

Returns the interval found at the given offset.

<p class="message-warning">An <code>InvalidIndex</code> exception will be thrown if the <code>$offset</code> does not exists in the instance. In doubt, use <code>Sequence::indexOf</code> before using this method.</p>

~~~php
$sequence = new Sequence(
    new Period('2018-01-01', '2018-01-31'),
    new Period('2018-02-10', '2018-02-20'),
    new Period('2018-03-01', '2018-03-31'),
    new Period('2018-01-20', '2018-03-10')
);
$sequence->get(3)->format('Y-m-d'); //returns [2018-01-20, 2018-03-10)
$sequence->get(42); //throws an League\Period\InvalidIndex exception
~~~

### Sequence::sort

Sort the current instance according to the given comparison callable and maintain index association.

~~~php
$sequence = new Sequence(
    new Period('2018-01-01', '2018-01-31'),
    new Period('2017-01-01', '2017-01-31'),
    new Period('2020-01-01', '2020-01-31')
);

foreach ($sequence as $offset => $interval) {
    echo $offset; //0, 1, 2
}

$compare = static function (Period $interval1, Period $interval2): int {
    return $interval1->getEndDate() <=> $interval2->getEndDate();
};

$sequence->sort($compare);
foreach ($sequence as $offset => $interval) {
    echo $offset; // 2, 0, 1
}
~~~

## Setter methods

### Sequence::push

Adds new intervals at the end of the sequence.

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

### Sequence::set

Updates the interval at the specify offset.

<p class="message-warning">An <code>InvalidIndex</code> exception will be thrown if the <code>$offset</code> does not exists in the instance. In doubt, use <code>Sequence::indexOf</code> before using this method.</p>

~~~php
$sequence = new Sequence(
    new Period('2018-01-01', '2018-01-31'),
    new Period('2018-02-10', '2018-02-20'),
);
$sequence->set(0, new Period('2012-01-01', '2012-01-31'));
$sequence->set(42, new Period('2012-01-01', '2012-01-31')); //throws InvalidIndex
~~~

### Sequence::remove

Removes an interval from the collection at the given offset and returns it.

<p class="message-notice">The sequence is re-indexed after removal.</p>

<p class="message-warning">An <code>InvalidIndex</code> exception will be thrown if the <code>$offset</code> does not exists in the instance. In doubt, use <code>Sequence::indexOf</code> before using this method.</p>

~~~php
$sequence = new Sequence(
    new Period('2018-01-01', '2018-01-31'),
    new Period('2018-02-10', '2018-02-20'),
    new Period('2018-03-01', '2018-03-31'),
    new Period('2018-01-20', '2018-03-10')
);
$interval = $sequence->remove(3);
$sequence->remove(42);//throws InvalidIndex
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