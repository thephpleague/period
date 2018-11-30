---
layout: default
title: The Sequence class
---

# The Sequence object

If you need to manipulate multiple `Period` instances you can now manage them easily using the provided `Sequence` class. This class is a **List** similar to an array that uses incremental integer keys.

<p class="message-info">The <code>Sequence</code> class is introduced in <code>version 4.1</code>.</p>

## The constructor

### Sequence::__construct

~~~php
public Sequence::__construct(Period ...$intervals)
~~~

Instantiate a new `Sequence` object with the given intervals.

#### Example

~~~php
use League\Period\Sequence;
use League\Period\Period;

$sequence = new Sequence(
    new Period('2018-01-01', '2018-01-31'),
    new Period('2018-02-10', '2018-02-20'),
    new Period('2018-03-01', '2018-03-31'),
    new Period('2018-01-20', '2018-03-10')
);
~~~

Once instantiated, you can use the `Sequence` object:

- as a [Period aware Container](/4.0/sequence/container/);
- or as a [Period Collection](/4.0/sequence/collection/);