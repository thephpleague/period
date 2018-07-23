---
layout: default
title: Period objects Collections
---

# Period Collections

The `League\Period\Collection` is an **ordered map** that can also be used as a list.  
This class is heavily inspired by `Doctrine\Common\Collections\Collection`. Most of the methods name and behaviour mimics the Doctrine `Collection` interface.

~~~php
<?php

final class Collection implements ArrayAccess, Countable, IteratorAggregate
{
    public function __construct(iterable $periods = [])
    public function add(PeriodInterface $period): void;
    public function contains(PeriodInterface $period): bool;
    public function containskey($index): bool;
    public function clear(): void;
    public function filter(callable $filter, int $flag = 0): self;
    public function first(): ?PeriodInterface;
    public function get($index): ?PeriodInterface;
    public function getKeys(): array;
    public function getGaps(): self;
    public function getIntersections(): self;
    public function getValues(): array;
    public function last(): ?PeriodInterface;
    public function indexOf(PeriodInterface $period): string|bool;
    public function map(callable $mapper): self;
    public function partition(callable $predicate): array;
    public function remove(PeriodInterface $period): bool;
    public function removeIndex($index): ?PeriodInterface;
    public function set($index, PeriodInterface $period): void;
    public function slice(int $offset, int $length = null): self;
    public function sort(callable $callable): bool;
    public function toArray(): array;
}
~~~

## Ordered map and list methods

- The main difference is that the internal `PeriodInterface` comparisons are done using `PeriodInterface::equalsTo` instead of `===` because `PeriodInterface` implementing object are immutable value objects.
- The `Collection` class **is mutable**.

## PeriodInterface specific related methods

### Collection::getGaps

### Collection::getIntersections
