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
use Closure;
use Countable;
use Iterator;
use IteratorAggregate;
use JsonSerializable;
use function array_filter;
use function array_merge;
use function array_splice;
use function array_unshift;
use function array_values;
use function count;
use function reset;
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
    /**
     * @var Period[]
     */
    private $periods = [];

    /**
     * new instance.
     *
     * @param Period ...$periods
     */
    public function __construct(Period ...$periods)
    {
        $this->periods = $periods;
    }

    /**
     * Returns the sequence boundaries as a Period instance.
     *
     * If the sequence contains no interval null is returned.
     *
     * @return Period|null
     */
    public function length(): Period|null
    {
        $period = reset($this->periods);
        if (false === $period) {
            return null;
        }

        return $period->merge(...$this->periods);
    }

    /**
     * Returns the gaps inside the instance.
     */
    public function gaps(): self
    {
        $sequence = new self();
        $interval = null;
        foreach ($this->sorted(Closure::fromCallable([$this, 'sortByStartDate'])) as $period) {
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
    private function sortByStartDate(Period $period1, Period $period2): int
    {
        return $period1->startDate() <=> $period2->startDate();
    }

    /**
     * Returns the intersections inside the instance.
     */
    public function intersections(): self
    {
        $current = null;
        $isPreviouslyContained = false;
        $reducer = function (Sequence $sequence, Period $period) use (&$current, &$isPreviouslyContained): Sequence {
            if (null === $current) {
                $current = $period;

                return $sequence;
            }

            $isContained = $current->contains($period);
            if ($isContained && $isPreviouslyContained) {
                $sequence->push($current->intersect($period));

                return $sequence;
            }

            if ($current->overlaps($period)) {
                $sequence->push($current->intersect($period));
            }

            $isPreviouslyContained = $isContained;
            if (!$isContained) {
                $current = $period;
            }

            return $sequence;
        };

        return $this->sorted(Closure::fromCallable([$this, 'sortByStartDate']))->reduce($reducer, new self());
    }

    /**
     * Returns the unions inside the instance.
     */
    public function unions(): self
    {
        $sequence = $this
            ->sorted(Closure::fromCallable([$this, 'sortByStartDate']))
            ->reduce(Closure::fromCallable([$this, 'calculateUnion']), new self())
        ;

        if ($sequence->periods === $this->periods) {
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

        $new = $sequence->reduce(Closure::fromCallable([$this, 'subtractOne']), $this);
        if ($new->periods === $this->periods) {
            return $this;
        }

        return $new;
    }

    /**
     * subtract an Interval from a Sequence.
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
     * Returns the sum of all instances durations as expressed in seconds.
     */
    public function totalTimestampInterval(): int
    {
        return array_reduce(
            $this->periods,
            fn (int $timestamp, Period $period): int => $timestamp + $period->timestampInterval(),
            0
        );
    }

    /**
     * Tells whether some intervals in the current instance satisfies the predicate.
     */
    public function some(Closure $predicate): bool
    {
        foreach ($this->periods as $offset => $interval) {
            if (true === $predicate($interval, $offset)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Tells whether all intervals in the current instance satisfies the predicate.
     */
    public function every(Closure $predicate): bool
    {
        foreach ($this->periods as $offset => $interval) {
            if (true !== $predicate($interval, $offset)) {
                return false;
            }
        }

        return [] !== $this->periods;
    }

    /**
     * Returns the array representation of the sequence.
     *
     * @return Period[]
     */
    public function toArray(): array
    {
        return $this->periods;
    }

    /**
     * {@inheritDoc}
     */
    public function jsonSerialize(): array
    {
        return $this->periods;
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator(): Iterator
    {
        foreach ($this->periods as $offset => $interval) {
            yield $offset => $interval;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function count(): int
    {
        return count($this->periods);
    }

    /**
     * @inheritDoc
     *
     * @param mixed $offset the integer index of the Period instance to validate.
     */
    public function offsetExists($offset): bool
    {
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
        $max = count($this->periods);

        return match (true) {
            [] === $this->periods, 0 > $max + $offset, 0 > $max - $offset - 1 => null,
            0 > $offset => $max + $offset,
            default => $offset,
        };
    }

    /**
     * @inheritDoc
     * @param mixed $offset the integer index of the Period instance to retrieve.
     *
     * @throws CannotAccessPeriod If the offset is illegal for the current sequence
     *@see ::get
     *
     */
    public function offsetGet($offset): Period
    {
        return $this->get($offset);
    }

    /**
     * @inheritDoc
     * @param mixed $offset the integer index of the Period instance to remove
     *
     * @throws CannotAccessPeriod If the offset is illegal for the current sequence
     *@see ::remove
     *
     */
    public function offsetUnset($offset): void
    {
        $this->remove($offset);
    }

    /**
     * @inheritDoc
     * @param mixed $offset   the integer index of the Period to add or update
     * @param mixed $interval the Period instance to add
     *
     * @throws CannotAccessPeriod If the offset is illegal for the current sequence
     *
     * @see ::push
     * @see ::set
     */
    public function offsetSet($offset, $interval): void
    {
        if (null !== $offset) {
            $this->set($offset, $interval);
            return;
        }

        $this->push($interval);
    }

    /**
     * Tells whether the sequence is empty.
     */
    public function isEmpty(): bool
    {
        return [] === $this->periods;
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
        foreach ($this->periods as $offset => $period) {
            if ($period->equals($interval)) {
                return $offset;
            }
        }

        return false;
    }

    /**
     * Returns the interval specified at a given offset.
     *
     * @throws CannotAccessPeriod If the offset is illegal for the current sequence
     */
    public function get(int $offset): Period
    {
        $index = $this->filterOffset($offset);
        if (null === $index) {
            throw CannotAccessPeriod::dueToInvalidIndex($offset);
        }

        return $this->periods[$index];
    }

    /**
     * Sort the current instance according to the given comparison closure
     * and maintain index association.
     *
     * Returns true on success or false on failure
     */
    public function sort(Closure $compare): bool
    {
        return uasort($this->periods, $compare);
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
        $this->periods = array_merge($intervals, $this->periods);
    }

    /**
     * Adds new intervals at the end of the sequence.
     *
     * @param Period ...$intervals
     */
    public function push(Period ...$intervals): void
    {
        $this->periods = array_merge($this->periods, $intervals);
    }

    /**
     * Inserts new intervals at the specified offset of the sequence.
     *
     * The sequence is re-indexed after addition
     *
     * @param Period ...$intervals
     *
     * @throws CannotAccessPeriod If the offset is illegal for the current sequence.
     */
    public function insert(int $offset, Period $interval, Period ...$intervals): void
    {
        if (0 === $offset) {
            $this->unshift($interval, ...$intervals);

            return;
        }

        if (count($this->periods) === $offset) {
            $this->push($interval, ...$intervals);

            return;
        }

        $index = $this->filterOffset($offset);
        if (null === $index) {
            throw CannotAccessPeriod::dueToInvalidIndex($offset);
        }

        array_unshift($intervals, $interval);
        array_splice($this->periods, $index, 0, $intervals);
    }

    /**
     * Updates the interval at the specify offset.
     *
     * @throws CannotAccessPeriod If the offset is illegal for the current sequence.
     */
    public function set(int $offset, Period $interval): void
    {
        $index = $this->filterOffset($offset);
        if (null === $index) {
            throw CannotAccessPeriod::dueToInvalidIndex($offset);
        }

        $this->periods[$index] = $interval;
    }

    /**
     * Removes an interval from the sequence at the given offset and returns it.
     *
     * The sequence is re-indexed after removal
     *
     * @throws CannotAccessPeriod If the offset is illegal for the current sequence.
     */
    public function remove(int $offset): Period
    {
        $index = $this->filterOffset($offset);
        if (null === $index) {
            throw CannotAccessPeriod::dueToInvalidIndex($offset);
        }

        $interval = $this->periods[$index];
        unset($this->periods[$index]);
        $this->periods = array_values($this->periods);

        return $interval;
    }

    /**
     * Filters the sequence according to the given predicate.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the interval which validate the predicate.
     */
    public function filter(Closure $predicate): self
    {
        $intervals = array_filter($this->periods, $predicate, ARRAY_FILTER_USE_BOTH);
        if ($intervals === $this->periods) {
            return $this;
        }

        return new self(...$intervals);
    }

    /**
     * Removes all intervals from the sequence.
     */
    public function clear(): void
    {
        $this->periods = [];
    }

    /**
     * Returns an instance sorted according to the given comparison closure
     * but does not maintain index association.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the sorted intervals. The key are re-indexed
     */
    public function sorted(Closure $compare): self
    {
        $intervals = $this->periods;
        usort($intervals, $compare);
        if ($intervals === $this->periods) {
            return $this;
        }

        return new self(...$intervals);
    }

    /**
     * Returns an instance where the given function is applied to each element in
     * the collection. The Closure MUST return a Period object and takes a Period
     * and its associated key as argument.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the returned intervals.
     */
    public function map(Closure $func): self
    {
        $intervals = [];
        foreach ($this->periods as $offset => $interval) {
            $intervals[$offset] = $func($interval, $offset);
        }

        if ($intervals === $this->periods) {
            return $this;
        }

        $mapped = new self();
        $mapped->periods = $intervals;

        return $mapped;
    }

    /**
     * Iteratively reduces the sequence to a single value using a callback.
     *
     * @param Closure $func Accepts the carry, the current value and the current offset, and
     *                      returns an updated carry value.
     *
     * @param mixed|null $carry Optional initial carry value.
     *
     * @return mixed The carry value of the final iteration, or the initial
     *               value if the sequence was empty.
     */
    public function reduce(Closure $func, $carry = null)
    {
        foreach ($this->periods as $offset => $interval) {
            $carry = $func($carry, $interval, $offset);
        }

        return $carry;
    }
}
