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
$sequence = new Sequence(Period::fromIso8601('!Y-m-d', '2018-01-01/2018-01-31'));
$sequence->isEmpty(); // false
~~~

### Sequence::count

Returns the number of `Period` instance contains in the `Sequence` object. The object implements PHP's `Countable` interface.

~~~php
$sequence = new Sequence(Period::fromIso80000('!Y-m-d', '[2018-01-01, 2018-01-31)'));
count($sequence); // returns 1
~~~

## Getter methods

### ArrayAccess, IteratorAggregate

The `Sequence` class implements PHP's `ArrayAccess`, `IteratorAggregate` interfaces to iterate over each interval using the `foreach` loop or access any individual `Period` instance according to its offset using array notation.

~~~php
$sequence = new Sequence(
    Period::fromIso80000('!Y-m-d', '[2018-01-01', '2018-01-31'),
    Period::fromIso80000('!Y-m-d', '[2018-02-10', '2018-02-20'),
    Period::fromIso80000('!Y-m-d', '[2018-03-01', '2018-03-31'),
    Period::fromIso80000('!Y-m-d', '[2018-01-20', '2018-03-10'),
);
foreach ($sequence as $interval) {
	//$interval is a League\Period\Period object
}

$sequence[3]->toIso80000('Y-m-d');  //returns [2018-01-20, 2018-03-10)
$sequence[-1]->toIso80000('Y-m-d'); //returns [2018-01-20, 2018-03-10)
~~~

### Sequence::get

Returns the interval found at the given offset.

<p class="message-warning">An <code>InaccessibleInterval</code> exception will be thrown if the <code>$offset</code> does not exists in the instance. In doubt, use <code>Sequence::indexOf</code> before using this method or <code>isset</code>.</p>

~~~php
$sequence = new Sequence(
    Period::fromIso80000('!Y-m-d', '[2018-01-01', '2018-01-31'),
    Period::fromIso80000('!Y-m-d', '[2018-02-10', '2018-02-20'),
    Period::fromIso80000('!Y-m-d', '[2018-03-01', '2018-03-31'),
    Period::fromIso80000('!Y-m-d', '[2018-01-20', '2018-03-10'),
);
$sequence->get(3)->format('Y-m-d'); //returns [2018-01-20, 2018-03-10)
$sequence->get(42); //throws a League\Period\InaccessibleInterval exception
$sequence[3]->format('Y-m-d');  //returns [2018-01-20, 2018-03-10)
$sequence[42]; //throws a League\Period\InaccessibleInterval exception
$sequence->get(-1)->format('Y-m-d'); //returns [2018-01-20, 2018-03-10)
$sequence[–1]->format('Y-m-d');  //returns [2018-01-20, 2018-03-10)
$sequence->get(-42); //throws a League\Period\InaccessibleInterval exception
$sequence[-42]; //throws a League\Period\InaccessibleInterval exception
~~~

## Setter methods

### Sequence::push

Adds new intervals at the end of the sequence.

<p class="message-info">This method when used with no argument leave the current instance unchanged.</p>

~~~php
$sequence = new Sequence(new Period('2018-01-01', '2018-01-31'));
$sequence->get(0)->format('Y-m-d'); // [2018-01-01, 2018-01-31)
$sequence->push(
    Period::fromIso80000('!Y-m-d', '[2018-01-01', '2018-01-31'),
    Period::fromIso80000('!Y-m-d', '[2018-02-10', '2018-02-20'),
    Period::fromIso80000('!Y-m-d', '[2018-03-01', '2018-03-31'),
    Period::fromIso80000('!Y-m-d', '[2018-01-20', '2018-03-10'),
);
$sequence->get(0)->toIso80000('Y-m-d'); // [2018-01-01, 2018-01-31)
$sequence[] = Period::fromIso8601('!Y-m-d', '2018-12-20/2018-12-21');
$sequence[4]->toIso80000('Y-m-d'); // [2018-12-20, 2018-12-21)
~~~

### Sequence::unshift

Adds new intervals at the start of the sequence.

<p class="message-notice">The sequence is re-indexed after the addition. This method when used with no argument leave the current instance unchanged.</p>

~~~php
$sequence = new Sequence(Period::fromIso80000('!Y-m-d', '[2018-01-01', '2018-01-31'));
$sequence->get(0)->format('Y-m-d'); // [2018-01-01, 2018-01-31)
$sequence->unshift(
    Period::fromIso80000('!Y-m-d', '[2018-02-10', '2018-02-20'),
    Period::fromIso80000('!Y-m-d', '[2018-03-01', '2018-03-31'),
    Period::fromIso80000('!Y-m-d', '[2018-01-20', '2018-03-10'),
);
$sequence->get(0)->toIso80000('Y-m-d'); // [2018-02-10, 2018-02-20)
~~~

### Sequence::insert

Adds intervals at a specified offset.

<p class="message-notice">The sequence is re-indexed on the right side after the addition. This method supports negative offsets.</p>

~~~php
$sequence = new Sequence(
    Period::fromDate(new DateTimeImmutable('2018-01-01'), new DateTime('2018-02-01')),
    Period::fromDate(new DateTime('2018-04-01'), new DateTimeImmutable('2018-05-01')),
);
$sequence->get(1)->toIso80000('Y-m-d'); // [2018-04-01, 2018-05-01)
$sequence->insert(1,
    Period::fromDate('2018-02-01', '2018-03-01'),
    Period::fromDate('2018-03-01', '2018-04-01')
);
count($sequence); // 4
$sequence->get(1)->toIso80000('Y-m-d'), PHP_EOL; // [2018-02-01, 2018-03-01)
~~~

### Sequence::set

Updates the interval at the specified offset. This method supports negative offsets.

<p class="message-warning">An <code>InaccessibleInterval</code> exception will be thrown if the <code>$offset</code> does not exists in the instance. In doubt, use <code>Sequence::indexOf</code> before using this method or <code>isset</code>.</p>

~~~php
$sequence = new Sequence(
    Period::fromDate('2018-01-01', '2018-01-31'),
    Period::fromDate('2018-02-10', '2018-02-20'),
);
$sequence->set(0, Period::fromDate('2012-01-01', '2012-01-31'));
$sequence->set(42, Period::fromDate('2012-01-01', '2012-01-31')); //throws InaccessibleInterval
$sequence[1] = Period::fromDate('2012-01-01', '2012-01-31');
$sequence[42] = Period::fromDate('2012-01-01', '2012-01-31')); //throws InaccessibleInterval
$sequence[-1] = Period::fromDate('2012-01-01', '2012-01-31');
$sequence[-23] = Period::fromDate('2012-01-01', '2012-01-31')); //throws InaccessibleInterval
~~~

### Sequence::remove

Removes an interval from the collection at the given offset and returns it. This method supports negative offsets.
<p class="message-notice">The sequence is re-indexed after removal.</p>
<p class="message-warning">An <code>InaccessibleInterval</code> exception will be thrown if the <code>$offset</code> does not exists in the instance. In doubt, use <code>Sequence::indexOf</code> before using this method or <code>isset</code>.</p>

~~~php
$sequence = new Sequence(
    Period::fromIso80000('!Y-m-d', '[2018-01-01', '2018-01-31'),
    Period::fromIso80000('!Y-m-d', '[2018-02-10', '2018-02-20'),
    Period::fromIso80000('!Y-m-d', '[2018-03-01', '2018-03-31'),
    Period::fromIso80000('!Y-m-d', '[2018-01-20', '2018-03-10'),
);
$interval = $sequence->remove(3);
$sequence->remove(42);//throws InaccessibleInterval
unset($sequence[2]);
unset($sequence[42]);//throws InaccessibleInterval
unset($sequence[-2]);
unset($sequence[-25]);//throws InaccessibleInterval
~~~

### Sequence::clear

Clear the sequence by removing all intervals.

~~~php
$sequence = new Sequence(
    Period::fromIso80000('!Y-m-d', '[2018-01-01', '2018-01-31'),
    Period::fromIso80000('!Y-m-d', '[2018-02-10', '2018-02-20'),
    Period::fromIso80000('!Y-m-d', '[2018-03-01', '2018-03-31'),
    Period::fromIso80000('!Y-m-d', '[2018-01-20', '2018-03-10'),
);
count($sequence); // 4
$sequence->clear();
count($sequence); // 0
~~~

## Conversion methods

### JsonSerializable

The `Sequence` class implements PHP's `JsonSerializable` interfaces to enable exporting its content using JSON representation.

~~~php
$sequence = new Sequence(
    Period::fromIso80000('!Y-m-d', '[2018-01-01', '2018-01-31'),
    Period::fromIso80000('!Y-m-d', '[2018-02-10', '2018-02-20'),
    Period::fromIso80000('!Y-m-d', '[2018-03-01', '2018-03-31'),
    Period::fromIso80000('!Y-m-d', '[2018-01-20', '2018-03-10'),
);

echo json_encode($sequence, JSON_PRETTY_PRINT);
// [
//    {
//        "startDate": "2017-12-31T23:00:00.000000Z",
//        "endDate": "2018-01-30T23:00:00.000000Z",
//        "startDateIncluded": true,
//        "endDateIncluded": false
//    },
//    {
//        "startDate": "2018-02-09T23:00:00.000000Z",
//        "endDate": "2018-02-19T23:00:00.000000Z"
//        "startDateIncluded": true,
//        "endDateIncluded": false
//    },
//    {
//        "startDate": "2018-02-28T23:00:00.000000Z",
//        "endDate": "2018-03-30T22:00:00.000000Z"
//        "startDateIncluded": true,
//        "endDateIncluded": false
//    },
//    {
//        "startDate": "2018-01-19T23:00:00.000000Z",
//        "endDate": "2018-03-09T23:00:00.000000Z"
//        "startDateIncluded": true,
//        "endDateIncluded": false
//    }
//]
~~~

### Sequence::toList

Returns a native PHP array representation of the collection as a List, its keys consist of consecutive numbers from 0 to count($array)-1.

~~~php
$sequence = new Sequence(
    Period::fromIso80000('!Y-m-d', '[2018-01-01', '2018-01-31'),
    Period::fromIso80000('!Y-m-d', '[2018-02-10', '2018-02-20'),
    Period::fromIso80000('!Y-m-d', '[2018-03-01', '2018-03-31'),
    Period::fromIso80000('!Y-m-d', '[2018-01-20', '2018-03-10'),
);
$array = $sequence->toList();
~~~

<p class="message-info">Of note, the <code>Sequence::toList</code> method is not affected by the <code>Sequence::sort</code> method.</p>

~~~php
$day1 = Period::fromDay(2012, 6, 23);
$day2 = Period::fromDay(2012, 6, 12);
$sequence = new Sequence($day1, $day2);
$sequence->sort(fn (Period $period1, Period $period2): int => $period1->startDate <=> $period2->startDate);
foreach ($sequence as $offset => $period) {
// first iteration $offset = 1 and $period === $day2
// second iteration $offset = 0 and $period === $day1
}
$sequence->toList(); // returns [0 => $day2, 1 => $day1];
~~~

## Filtering the sequence

### Sequence::some

Tells whether some intervals in the current instance satisfies the predicate.

The predicate is a `Closure` whose signature is as follows:

~~~php
function(Period $interval [, int $offset]): bool
~~~

It takes up to two (2) parameters:

- `$interval` : the Sequence value which is a `Period` object
- `$offset` : the Sequence value corresponding offset

~~~php
$sequence = new Sequence(
    Period::fromIso80000('!Y-m-d', '[2018-01-01', '2018-01-31)'),
    Period::fromIso80000('!Y-m-d', '[2017-01-01', '2017-01-31)'),
    Period::fromIso80000('!Y-m-d', '[2020-01-01', '2020-01-31)'),
);

$sequence->some(fn (Period $interval): bool => $interval->contains('2018-01-15')); // true
~~~

### Sequence::every

Tells whether all intervals in the current instance satisfies the predicate.

The predicate is a `Closure` whose signature is as follows:

~~~php
function(Period $interval [, int $offset]): bool
~~~

It takes up to two (2) parameters:

- `$interval` : the Sequence value which is a `Period` object
- `$offset` : the Sequence value corresponding offset

~~~php
$sequence = new Sequence(
    Period::fromIso80000('!Y-m-d', '[2018-01-01', '2018-01-31)'),
    Period::fromIso80000('!Y-m-d', '[2017-01-01', '2017-01-31)'),
    Period::fromIso80000('!Y-m-d', '[2020-01-01', '2020-01-31)'),
);

$sequence->every(fn (Period $interval): bool => $interval->contains('2018-01-15');); // false
~~~

### Sequence::sort

Sorts the current instance according to the given comparison callable and maintain index association.

The comparison algorithm is a `Closure` whose signature is as follows:

~~~php
function(Period $interval1, Period $interval2): int
~~~

It must return an integer less than, equal to, or greater than zero if the first argument is considered to be respectively less than, equal to, or greater than the second.

~~~php
$sequence = new Sequence(
    Period::fromIso80000('!Y-m-d', '[2018-01-01', '2018-01-31)'),
    Period::fromIso80000('!Y-m-d', '[2017-01-01', '2017-01-31)'),
    Period::fromIso80000('!Y-m-d', '[2020-01-01', '2020-01-31)'),
);

foreach ($sequence as $offset => $interval) {
    echo $offset, ' -> ', $interval->toIso80000('Y-m-d'), PHP_EOL; //0 -> [2018-01-01, 2018-01-31)...
}

$sequence->sort(fn (Period $interval1, Period $interval2): int => $interval1->endDate() <=> $interval2->endDate());
foreach ($sequence as $offset => $interval) {
    echo $offset, ' -> ', $interval->toIso80000('Y-m-d'), PHP_EOL; //1 -> [2017-01-01, 2017-01-31)...
}
~~~

## Manipulations methods

### Sequence::sorted

Returns an instance sorted according to the given comparison callable but does not maintain index association. This method **MUST** retain the state of the current instance, and return an instance that contains the sorted intervals with their keys re-indexed.

The comparison algorithm is a `Closure` whose signature is as follows:

~~~php
function(Period $interval1, Period $interval2): int
~~~

It must return an integer less than, equal to, or greater than zero if the first argument is considered to be respectively less than, equal to, or greater than the second.

~~~php
$sequence = new Sequence(
    Period::fromIso80000('!Y-m-d', '[2018-01-01', '2018-01-31)'),
    Period::fromIso80000('!Y-m-d', '[2017-01-01', '2017-01-31)'),
    Period::fromIso80000('!Y-m-d', '[2020-01-01', '2020-01-31)'),
);

$newSequence = $sequence->sorted(fn (Period $interval1, Period $interval2): int => $interval1->endDate() <=> $interval2->endDate());
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
    Period::after('2018-01-01', '30 DAYS'),
    Period::after('2019-01-01', '30 DAYS'),
    Period::after('2020-01-01', '30 DAYS'),
);

$newSequence = $sequence->filter(fn (Period $interval): bool => $interval->equals(Period::before('2018-01-31', '30 days')));
count($sequence); // 3
count($newSequence); //1
~~~

### Sequence::map

Map the sequence according to the given function. This method **MUST** retain the state of the current instance, and return an instance that contains the mapped intervals. The keys are not indexed.

The mapper is a `Closure` whose signature is as follows:

~~~php
function(Period $interval [, int $offset]): Period
~~~

It takes up to two (2) parameters:

- `$interval` : the Sequence value which is a `Period` object
- `$offset` : the Sequence value corresponding offset

~~~php
$sequence = new Sequence(
    Period::after('2018-01-01', '30 DAYS'),
    Period::after('2019-01-01', '30 DAYS'),
    Period::after('2020-01-01', '30 DAYS'),
);

$newSequence = $sequence->map(fn (Period $interval): Period => $interval->moveEndDate('+ 1 DAY'));
count($sequence); // 3
count($newSequence); //3
$newSequence->get(2)->toIso80000('Y-m-d'); // [2020-01-01, 2020-02-01)
~~~

### Sequence::reduce

Iteratively reduces the sequence to a single value using a callback. The returned value is the carry value of the final iteration, or the initial value if the sequence was empty.

The reducer is a `Closure` whose signature is as follows:

~~~php
function($carry, Period $interval [, int $offset]): mixed
~~~

It takes up to three (3) parameters:

- `$carry` : the optional initial carry value or null
- `$interval` : the Sequence value which is a `Period` object
- `$offset` : the Sequence value corresponding offset

~~~php
$sequence = new Sequence(
    Period::after('2018-01-01', '30 DAYS'),
    Period::after('2019-01-01', '30 DAYS'),
    Period::after('2020-01-01', '30 DAYS'),
);

$mergePeriod = $sequence->reduce(fn ($carry, Period $interval): Period => null === $carry ? $interval : $carry->merge($interval));
$mergePeriod->toIso80000('Y-m-d'); // [2018-01-01, 2020-01-31)
~~~
