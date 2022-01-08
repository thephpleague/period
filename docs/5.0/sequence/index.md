---
layout: default
title: The Sequence class
---

# The Sequence object

If you need to manipulate multiple `Period` instances you can now manage them easily using the provided `Sequence` class. 
This class is a **List** similar to an array that uses incremental integer keys.

## The constructor

### Sequence::__construct

~~~php
public Sequence::__construct(Period ...$intervals)
~~~

Instantiate a new `Sequence` object with the given intervals.

#### Example

~~~php
use League\Period\Period;
use League\Period\Sequence;

$sequence = new Sequence(
    Period::fromDate('2018-01-01', '2018-01-31'),
    Period::fromDate('2018-02-10', '2018-02-20'),
    Period::fromDate('2018-03-01', '2018-03-31'),
    Period::fromDate('2018-01-20', '2018-03-10')
);
~~~

Once instantiated, you can use the `Sequence` object:

- as a [Period aware Container](/5.0/sequence/container/);
- or as a [Period Collection](/5.0/sequence/collection/);
