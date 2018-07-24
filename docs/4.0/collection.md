---
layout: default
title: Period objects Collection
---

# Period Collections

The `League\Period\Collection` is an **ordered map** that can also be used as a list of `PeriodInterface` objects.  
This class is heavily inspired by `Doctrine\Common\Collections\Collection` but also feature specific methods to deal with collection of time range objects.

## Time range related methods

### Collection::getGaps

Returns a `Collection` instance of all the gaps present in the current collection.

~~~php
$retval = $collection->getGaps();
//$retval is a Collection instance containing all the gaps from $collection
~~~

### Collection::getIntersections

Returns a `Collection` instance of all the intersections present in the current collection.

~~~php
$retval = $collection->getIntersections();
//$retval is a Collection instance containing all the intersection from $collection
~~~

## Ordered map and list methods

The class implements the following PHP interfaces:

- `ArrayAccess`
- `Countable`
- `IteratorAggregate`

In addition theses methods are also available:

### Collection::add

Append a `PeriodInterface` object to the current `Collection`.

~~~php
$collection->add(Period::createFromDay('2018-02-03'));
~~~

### Collection::contains

Tell whether the given `PeriodInterface` object is present in the current `Collection`. Because `PeriodInterface` objects are immutable, the method uses the `PeriodInterface::equalsTo` method for comparison.

~~~php
$retval = $collection->contains(Period::createFromDay('2018-02-03'));
//$retval is a boolean
~~~

### Collection::containsKey

Tell whether a `PeriodInterface` object present in the current `Collection` is attached using the `$index` key.

~~~php
$retval = $collection->containsKey('first');
//$retval is a boolean
~~~

### Collection::clear

Remove all the `PeriodInterface` objects present in the current `Collection`.

~~~php
$collection->clear();
~~~

### Collection::filter

Filter the current `Collection` using a predicate function. If the predicate function returns `true`, the `PeriodInterface` and its related index are added in the returned `Collection` instance.

~~~php
$month = Period::createFromMonth('2018-03-01');
$retval = $collection->filter(function (PeriodInterface $period) use ($month) {
    return $month->overlaps($period);
});
//$retval is a Collection containing PeriodInterface which overlaps the month of 2018-03
//if no PeriodInterface are found $retval will be empty.
~~~

__NOTE__: the `Collection::filter` methods uses the same argument in the same order as `array_filter`.


### Collection::get

Returns the `PeriodInterface` at the specified `$index`in the current `Collection`. If not object is found or the key is invalid, `null` is returned.

~~~php
$retval = $collection->get('foo');
//$retval is a PeriodInterface object or ǹull
~~~

### Collection::getKeys

Returns all the indexes/keys present in the current `Collection`.

~~~php
$retval = $collection->getKeys();
//$retval is an iterable
~~~

### Collection::getValues

Returns all `PeriodInterface` objects present in the current `Collection`.

~~~php
$retval = $collection->getValues();
//$retval is an iterable
~~~

### Collection::first

Returns the first `PeriodInterface` object present in the current `Collection` or `null` if the collection is
empty.

~~~php
$retval = $collection->first();
//$retval is a PeriodInterface or null
~~~

### Collection::last

Returns the last `PeriodInterface` object present in the current `Collection` or `null` if the collection is
empty.

~~~php
$retval = $collection->last();
//$retval is a PeriodInterface or null
~~~

### Collection::indexOf

Returns the index attached to the submitted `PeriodInterface` object present in the current `Collection` or `false` the `PeriodInterface` object is not found.

~~~php
$retval = $collection->indexOf(Period::createFromDay('2018-05-03'));
//$retval is a string or false
~~~

### Collection::map

Apply the given function to each `PeriodInterface` in the collection and returns a new `Collection` instance containing the modified `PeriodInterface` objects. Indexes are preserved.

~~~php
$retval = $collection->map(function (PeriodInterface $period) {
    return $period->endingOn($period->getEndDate()->add(new DateInterval('P1D')));
});
//$retval is a new Collection where each PeriodInterface object as a new ending datepoint.
~~~

### Collection::partition

Partitions this `Collection` instance in two `Collection` instances according to a predicate. Keys are preserved in the resulting collections. The first instance contains all the `PeriodInterface` objects and their related index which verify the predicate. The second instance contains the remaining objects and their indexes.

~~~php
$month = Period::createFromMonth('2018-03-01');
$retval = $collection->partition(function (PeriodInterface $period) use ($month) {
    return $month->overlaps($period);
});
//$retval is an array containing 2 Collection instance.
~~~

__NOTE__: the predicate first argument is the `PeriodInterface` object, its second optional argument is its index.

### Collection::remove

Removes the `PeriodInterface` object from the `Collection`. If not object was removed `false` is returned otherwise `true` is returned.

~~~php
$month = Period::createFromMonth('2018-03-01');
$retval = $collection->remove($month);
//$retval is a boolean
~~~

### Collection::removeIndex

Removes the `PeriodInterface` object at the specified index from the `Collection`. If not object was found `null` is returned otherwise the remove the `PeriodInterface` object is returned.

~~~php
$retval = $collection->removeIndex('foo');
//$retval is a PeriodInterface object or null
~~~

### Collection::set

Sets a `PeriodInterface` object in the collection at the specified key/index.

~~~php
$retval = $collection->set('foo', Period::createFromWeek(2018, 21));
//$retval is null
~~~

### Collection::slice

Extracts a slice of `$length` `PeriodInterface` objects starting at position `$offset` from the `Collection`.

Calling this method will only return the selected slice and NOT change the elements contained in the collection slice is called on.

__NOTE__: the `Collection::slice` methods uses the same argument in the same order as `array_slice`.

~~~php
$retval = $collection->slice(0, 3);
//$retval is a Collection of maximumn the 3 first PeriodInterfaces objects.
~~~

### Collection::sort

Sorts the `Collection` with a user defined comparison function while maintaining the index assocation.

__NOTE__: the `Collection::sort` methods uses the same argument as `uasort`.

~~~php
$collection->sort(function (PeriodInterface $period1, PeriodInterface $period2) {
    return $period2->compareDuration($period1);
});
//the collection PeriodInterface are now sorted according to their duration.
~~~

### Collection::toArray

Gets a native PHP array representation of the `Collection` object.

~~~php
$retval = $collection->toArray();
//$retval is an array
~~~