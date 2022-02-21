<?php

/**
 * League.Period (https://period.thephpleague.com)
 *
 * (c) Ignace Nyamagana Butera <nyamsprod@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace League\Period;

use ArrayAccess;
use Countable;
use Iterator;
use IteratorAggregate;
use JsonSerializable;
use TypeError;
use function array_filter;
use function array_merge;
use function array_splice;
use function array_unshift;
use function array_values;
use function count;
use function reset;
use function sprintf;
use function uasort;
use function usort;
use const ARRAY_FILTER_USE_BOTH;

/**
 * A class to manipulate interval collection.
 *
 * @package League.period
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @since   4.1.0
 */
final class Sequence implements ArrayAccess, Countable, IteratorAggregate, JsonSerializable
{
    /** @var array<Period> */
    private $intervals;

    public function __construct(Period ...$intervals)
    {
        $this->intervals = $intervals;
    }

    /**
     * Returns the sequence length as a Period instance.
     *
     * If the sequence contains no interval null is returned.
     *
     * @return ?Period
     */
    public function length(): ?Period
    {
        $period = reset($this->intervals);
        if (false === $period) {
            return null;
        }

        return $period->merge(...$this->intervals);
    }

    /**
     * DEPRECATION WARNING! This method will be removed in the next major point release.
     *
     * @deprecated 4.12.0 This method will be removed in the next major point release
     * @see Sequence::length()
     *
     * Returns the sequence length as a Period instance.
     *
     * If the sequence contains no interval null is returned.
     *
     * @return ?Period
     */
    public function boundaries(): ?Period
    {
        return $this->length();
    }

    /**
     * Returns the gaps inside the instance.
     */
    public function gaps(): self
    {
        $sequence = new self();
        $interval = null;
        foreach ($this->sorted([$this, 'sortByStartDate']) as $period) {
            if (null === $interval) {
                $interval = $period;
                continue;
            }

            if (!$interval->overlaps($period) && !$interval->abuts($period)) {
                $sequence->push($interval->gap($period));
            }

            if (!$interval->contains($period)) {
                $interval = $period;
            }
        }

        return $sequence;
    }

    /**
     * Sorts two Interval instance using their start datepoint.
     */
    private function sortByStartDate(Period $interval1, Period $interval2): int
    {
        return $interval1->getStartDate() <=> $interval2->getStartDate();
    }

    /**
     * Returns the intersections inside the instance.
     */
    public function intersections(): self
    {
        $sequence = new self();
        $current = null;
        $isPreviouslyContained = false;
        foreach ($this->sorted([$this, 'sortByStartDate']) as $period) {
            if (null === $current) {
                $current = $period;
                continue;
            }

            $isContained = $current->contains($period);
            if ($isContained && $isPreviouslyContained) {
                $sequence->push($current->intersect($period));

                continue;
            }

            if ($current->overlaps($period)) {
                $sequence->push($current->intersect($period));
            }

            $isPreviouslyContained = $isContained;
            if (!$isContained) {
                $current = $period;
            }
        }

        return $sequence;
    }

    /**
     * Returns the unions inside the instance.
     */
    public function unions(): self
    {
        $sequence = $this
            ->sorted([$this, 'sortByStartDate'])
            ->reduce([$this, 'calculateUnion'], new self())
        ;

        if ($sequence->intervals === $this->intervals) {
            return $this;
        }

        return $sequence;
    }

    /**
     * Iteratively calculate the union sequence.
     */
    private function calculateUnion(Sequence $sequence, Period $period): Sequence
    {
        if ($sequence->isEmpty()) {
            $sequence->push($period);

            return $sequence;
        }

        $index = $sequence->count() - 1;
        $interval = $sequence->get($index);
        if ($interval->overlaps($period) || $interval->abuts($period)) {
            $sequence->set($index, $interval->merge($period));

            return $sequence;
        }

        $sequence->push($period);

        return $sequence;
    }

    /**
     * DEPRECATION WARNING! This method will be removed in the next major point release.
     *
     * @deprecated 4.9.0  This method will be removed in the next major point release
     * @see Period::subtract
     */
    public function substract(Sequence $sequence): self
    {
        return $this->subtract($sequence);
    }

    /**
     * Subtract a Sequence from the current instance.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains subtracted intervals.
     */
    public function subtract(Sequence $sequence): self
    {
        if ($this->isEmpty()) {
            return $this;
        }

        $new = $sequence->reduce([$this, 'subtractOne'], $this);
        if ($new->intervals === $this->intervals) {
            return $this;
        }

        return $new;
    }

    /**
     * Substract an Interval from a Sequence.
     */
    private function subtractOne(Sequence $sequence, Period $interval): self
    {
        if ($sequence->isEmpty()) {
            return $sequence;
        }

        $reducer = function (Sequence $sequence, Period $period) use ($interval): Sequence {
            $subtract = $period->subtract($interval);
            if (!$subtract->isEmpty()) {
                $sequence->push(...$subtract);
            }

            return $sequence;
        };

        return $sequence->reduce($reducer, new self());
    }

    /**
     * Returns the sequence length as a Period instance.
     *
     * DEPRECATION WARNING! This method will be removed in the next major point release
     *
     * @deprecated 4.4.0 This method will be removed in the next major point release
     * @see Sequence::boundaries
     *
     * If the sequence contains no interval null is returned.
     *
     * @return ?Period
     */
    public function getBoundaries(): ?Period
    {
        return $this->length();
    }

    /**
     * Returns the intersections inside the instance.
     *
     * DEPRECATION WARNING! This method will be removed in the next major point release
     *
     * @deprecated 4.4.0 This method will be removed in the next major point release
     * @see Sequence::intersections
     */
    public function getIntersections(): self
    {
        return $this->intersections();
    }

    /**
     * Returns the gaps inside the instance.
     *
     * DEPRECATION WARNING! This method will be removed in the next major point release
     *
     * @deprecated 4.4.0 This method will be removed in the next major point release
     * @see Sequence::gaps
     */
    public function getGaps(): self
    {
        return $this->gaps();
    }

    /**
     * Returns the sum of all instances durations as expressed in seconds.
     */
    public function totalTimeDuration(): float
    {
        $retval = 0.0;
        foreach ($this->intervals as $interval) {
            $retval += $interval->timeDuration();
        }

        return $retval;
    }

    /**
     * DEPRECATION WARNING! This method will be removed in the next major point release.
     *
     * @deprecated 4.12.0 This method will be removed in the next major point release
     * @see Sequence::totalTimeDuration()
     *
     * Returns the sum of all instances durations as expressed in seconds.
     */
    public function getTotalTimestampInterval(): float
    {
        return $this->totalTimeDuration();
    }

    /**
     * Tells whether some intervals in the current instance satisfies the predicate.
     */
    public function some(callable $predicate): bool
    {
        foreach ($this->intervals as $offset => $interval) {
            if (true === $predicate($interval, $offset)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Tells whether all intervals in the current instance satisfies the predicate.
     */
    public function every(callable $predicate): bool
    {
        foreach ($this->intervals as $offset => $interval) {
            if (true !== $predicate($interval, $offset)) {
                return false;
            }
        }

        return [] !== $this->intervals;
    }

    /**
     * Returns the array representation of the sequence.
     *
     * @return array<int, Period>
     */
    public function toArray(): array
    {
        return $this->intervals;
    }

    /**
     * {@inheritDoc}
     */
    public function jsonSerialize(): array
    {
        return $this->intervals;
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator(): Iterator
    {
        foreach ($this->intervals as $offset => $interval) {
            yield $offset => $interval;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function count(): int
    {
        return count($this->intervals);
    }

    /**
     * @inheritDoc
     *
     * @param mixed $offset the integer index of the Period instance to validate.
     */
    public function offsetExists($offset): bool
    {
        if (!is_int($offset)) {
            throw new TypeError('Argument #1 ($offset) must be of type integer, '.gettype($offset).' given.');
        }

        return null !== $this->filterOffset($offset);
    }

    /**
     * Filter and format the Sequence offset.
     *
     * This methods allows the support of negative offset
     *
     * if no offset is found null is returned otherwise the return type is int
     */
    private function filterOffset(int $offset): ?int
    {
        if ([] === $this->intervals) {
            return null;
        }

        $max = count($this->intervals);
        if (0 > $max + $offset) {
            return null;
        }

        if (0 > $max - $offset - 1) {
            return null;
        }

        if (0 > $offset) {
            return $max + $offset;
        }

        return $offset;
    }

    /**
     * @inheritDoc
     * @see Sequence::get
     *
     * @param mixed $offset the integer index of the Period instance to retrieve.
     *
     * @throws InvalidIndex If the offset is illegal for the current sequence
     */
    public function offsetGet($offset): Period
    {
        if (!is_int($offset)) {
            throw new TypeError('Argument #1 ($offset) must be of type integer, '.gettype($offset).' given.');
        }

        return $this->get($offset);
    }

    /**
     * @inheritDoc
     * @see Sequence::remove
     *
     * @param mixed $offset the integer index of the Period instance to remove
     *
     * @throws InvalidIndex If the offset is illegal for the current sequence
     */
    public function offsetUnset($offset): void
    {
        if (!is_int($offset)) {
            throw new TypeError('Argument #1 ($offset) must be of type integer, '.gettype($offset).' given.');
        }

        $this->remove($offset);
    }

    /**
     * @inheritDoc
     * @param mixed $offset the integer index of the Period to add or update
     * @param mixed $value  the Period instance to add
     *
     * @throws InvalidIndex If the offset is illegal for the current sequence
     *
     * @see Sequence::push
     * @see Sequence::set
     */
    public function offsetSet($offset, $value): void
    {
        if (!is_int($offset) && !is_null($offset)) {
            throw new TypeError('Argument #1 ($offset) must be of type integer, '.gettype($value).' given.');
        }

        if (!$value instanceof Period) {
            throw new TypeError('Argument #2 ($interval) must be of type League\Period\Period, '.gettype($value).' given.');
        }

        if (null !== $offset) {
            $this->set($offset, $value);
            return;
        }

        $this->push($value);
    }

    /**
     * Tells whether the sequence is empty.
     */
    public function isEmpty(): bool
    {
        return [] === $this->intervals;
    }

    /**
     * Tells whether the given interval is present in the sequence.
     *
     * @param Period ...$intervals
     */
    public function contains(Period ...$intervals): bool
    {
        foreach ($intervals as $period) {
            if (false === $this->indexOf($period)) {
                return false;
            }
        }

        return [] !== $intervals;
    }

    /**
     * Attempts to find the first offset attached to the submitted interval.
     *
     * If no offset is found the method returns boolean false.
     *
     * @return int|bool
     */
    public function indexOf(Period $interval)
    {
        foreach ($this->intervals as $offset => $period) {
            if ($period->equals($interval)) {
                return $offset;
            }
        }

        return false;
    }

    /**
     * Returns the interval specified at a given offset.
     *
     * @throws InvalidIndex If the offset is illegal for the current sequence
     */
    public function get(int $offset): Period
    {
        $index = $this->filterOffset($offset);
        if (null === $index) {
            throw new InvalidIndex(sprintf('%s is an invalid offset in the current sequence', $offset));
        }

        return $this->intervals[$index];
    }

    /**
     * Sort the current instance according to the given comparison callable
     * and maintain index association.
     *
     * Returns true on success or false on failure
     */
    public function sort(callable $compare): bool
    {
        return uasort($this->intervals, $compare);
    }

    /**
     * Adds new intervals at the front of the sequence.
     *
     * The sequence is re-indexed after addition
     *
     * @param Period ...$intervals
     */
    public function unshift(Period ...$intervals): void
    {
        $this->intervals = array_merge($intervals, $this->intervals);
    }

    /**
     * Adds new intervals at the end of the sequence.
     *
     * @param Period ...$intervals
     */
    public function push(Period ...$intervals): void
    {
        $this->intervals = array_merge($this->intervals, $intervals);
    }

    /**
     * Inserts new intervals at the specified offset of the sequence.
     *
     * The sequence is re-indexed after addition
     *
     * @param Period ...$intervals
     *
     * @throws InvalidIndex If the offset is illegal for the current sequence.
     */
    public function insert(int $offset, Period $interval, Period ...$intervals): void
    {
        if (0 === $offset) {
            $this->unshift($interval, ...$intervals);

            return;
        }

        if (count($this->intervals) === $offset) {
            $this->push($interval, ...$intervals);

            return;
        }

        $index = $this->filterOffset($offset);
        if (null === $index) {
            throw new InvalidIndex(sprintf('%s is an invalid offset in the current sequence', $offset));
        }

        array_unshift($intervals, $interval);
        array_splice($this->intervals, $index, 0, $intervals);
    }

    /**
     * Updates the interval at the specify offset.
     *
     * @throws InvalidIndex If the offset is illegal for the current sequence.
     */
    public function set(int $offset, Period $interval): void
    {
        $index = $this->filterOffset($offset);
        if (null === $index) {
            throw new InvalidIndex(sprintf('%s is an invalid offset in the current sequence', $offset));
        }

        $this->intervals[$index] = $interval;
    }

    /**
     * Removes an interval from the sequence at the given offset and returns it.
     *
     * The sequence is re-indexed after removal
     *
     * @throws InvalidIndex If the offset is illegal for the current sequence.
     */
    public function remove(int $offset): Period
    {
        $index = $this->filterOffset($offset);
        if (null === $index) {
            throw new InvalidIndex(sprintf('%s is an invalid offset in the current sequence', $offset));
        }

        $interval = $this->intervals[$index];
        unset($this->intervals[$index]);
        $this->intervals = array_values($this->intervals);

        return $interval;
    }

    /**
     * Filters the sequence according to the given predicate.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the interval which validate the predicate.
     */
    public function filter(callable $predicate): self
    {
        $intervals = array_filter($this->intervals, $predicate, ARRAY_FILTER_USE_BOTH);
        if ($intervals === $this->intervals) {
            return $this;
        }

        return new self(...$intervals);
    }

    /**
     * Removes all intervals from the sequence.
     */
    public function clear(): void
    {
        $this->intervals = [];
    }

    /**
     * Returns an instance sorted according to the given comparison callable
     * but does not maintain index association.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the sorted intervals. The key are re-indexed
     */
    public function sorted(callable $compare): self
    {
        $intervals = $this->intervals;
        usort($intervals, $compare);
        if ($intervals === $this->intervals) {
            return $this;
        }

        return new self(...$intervals);
    }

    /**
     * Returns an instance where the given function is applied to each element in
     * the collection. The callable MUST return a Period object and takes a Period
     * and its associated key as argument.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the returned intervals.
     */
    public function map(callable $func): self
    {
        $intervals = [];
        foreach ($this->intervals as $offset => $interval) {
            $intervals[$offset] = $func($interval, $offset);
        }

        if ($intervals === $this->intervals) {
            return $this;
        }

        $mapped = new self();
        $mapped->intervals = $intervals;

        return $mapped;
    }

    /**
     * Iteratively reduces the sequence to a single value using a callback.
     *
     * @template TReduceInitial
     * @template TReduceReturnType
     *
     * @param callable(TReduceInitial|TReduceReturnType, Period, array-key=): TReduceReturnType $func
     * @param TReduceInitial $carry
     *
     * @return TReduceInitial|TReduceReturnType
     */
    public function reduce(callable $func, $carry = null)
    {
        foreach ($this->intervals as $offset => $interval) {
            $carry = $func($carry, $interval, $offset);
        }

        return $carry;
    }
}
