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
use function array_filter;
use function array_merge;
use function array_splice;
use function array_unshift;
use function array_values;
use function count;
use function reset;
use function sort;
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
    /**
     * @var Period[]
     */
    private $intervals = [];

    /**
     * new instance.
     *
     * @param Period ...$intervals
     */
    public function __construct(Period ...$intervals)
    {
        $this->intervals = $intervals;
    }

    /**
     * Returns the sequence boundaries as a Period instance.
     *
     * If the sequence contains no interval null is returned.
     *
     * @return ?Period
     */
    public function boundaries(): ?Period
    {
        $period = reset($this->intervals);
        if (false === $period) {
            return null;
        }

        return $period->merge(...$this->intervals);
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
    public function substract(Sequence $subIntervals): self
    {
        if (count($this) == 0) {
            return new self();
        } elseif (count($subIntervals) == 0) {
            return $this;
        } elseif (count($this) == 1 && count($subIntervals) == 1) {
            return $this->get(0)->substract($subIntervals->get(0));
        } elseif (count($this) > 1) {
            $diffSequences = [];
            foreach ($this->intervals as $intervalA) {
                $tmpSequence = new self($intervalA);
                $diffSequences[] = $tmpSequence->substract($subIntervals);
            }

            $newSequence = new self();
            foreach ($diffSequences as $diffSequence) {
                foreach ($diffSequence as $sequence) {
                    $newSequence->push($sequence);
                }
            }

            return $newSequence;
        } elseif (count($this) == 1 && count($subIntervals) > 1) {
            $newSequence = new self($this->get(0));
            foreach ($subIntervals as $subInterval) {
                $tmpSequence = new self($subInterval);
                $newSequence = $newSequence->substract($tmpSequence);
            }

            return $newSequence;
        }

        return new self();
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
     * Returns the sequence boundaries as a Period instance.
     *
     * DEPRECATION WARNING! This method will be removed in the next major point release
     *
     * @deprecated deprecated since version 4.4.0
     * @see        ::boundaries
     *
     * If the sequence contains no interval null is returned.
     *
     * @return ?Period
     */
    public function getBoundaries(): ?Period
    {
        return $this->boundaries();
    }

    /**
     * Returns the intersections inside the instance.
     *
     * DEPRECATION WARNING! This method will be removed in the next major point release
     *
     * @deprecated deprecated since version 4.4.0
     * @see        ::intersections
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
     * @deprecated deprecated since version 4.4.0
     * @see        ::gaps
     */
    public function getGaps(): self
    {
        return $this->gaps();
    }

    /**
     * Returns the sum of all instances durations as expressed in seconds.
     */
    public function getTotalTimestampInterval(): float
    {
        $retval = 0;
        foreach ($this->intervals as $interval) {
            $retval += $interval->getTimestampInterval();
        }

        return $retval;
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
     * @return Period[]
     */
    public function toArray(): array
    {
        return $this->intervals;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize(): array
    {
        return $this->intervals;
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator(): Iterator
    {
        foreach ($this->intervals as $offset => $interval) {
            yield $offset => $interval;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        return count($this->intervals);
    }

    /**
     * @inheritdoc
     *
     * @param mixed $offset the index of the Period instance to validate.
     */
    public function offsetExists($offset): bool
    {
        return isset($this->intervals[$offset]);
    }

    /**
     * @inheritdoc
     * @see ::get
     *
     * @param mixed $offset the index of the Period instance to retrieve.
     */
    public function offsetGet($offset): Period
    {
        return $this->get($offset);
    }

    /**
     * @inheritdoc
     * @see ::remove
     *
     * @param mixed $offset the index of the Period instance to remove.
     */
    public function offsetUnset($offset): void
    {
        $this->remove($offset);
    }

    /**
     * @inheritdoc
     * @see ::set
     * @see ::push
     *
     * @param mixed $offset   the index of the Period to add or update.
     * @param mixed $interval the Period instance to add.
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
        return [] === $this->intervals;
    }

    /**
     * Tells whether the given interval is present in the sequence.
     *
     * @param Period ...$intervals
     */
    public function contains(Period $interval, Period ...$intervals): bool
    {
        $intervals[] = $interval;
        foreach ($intervals as $period) {
            if (false === $this->indexOf($period)) {
                return false;
            }
        }

        return true;
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
        $period = $this->intervals[$offset] ?? null;
        if (null !== $period) {
            return $period;
        }

        throw new InvalidIndex(sprintf('%s is an invalid offset in the current sequence', $offset));
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
    public function unshift(Period $interval, Period ...$intervals): void
    {
        $this->intervals = array_merge([$interval], $intervals, $this->intervals);
    }

    /**
     * Adds new intervals at the end of the sequence.
     *
     * @param Period ...$intervals
     */
    public function push(Period $interval, Period ...$intervals): void
    {
        $this->intervals = array_merge($this->intervals, [$interval], $intervals);
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
        if ($offset < 0 || $offset > count($this->intervals)) {
            throw new InvalidIndex(sprintf('%s is an invalid offset in the current sequence', $offset));
        }

        array_unshift($intervals, $interval);
        array_splice($this->intervals, $offset, 0, $intervals);
    }

    /**
     * Updates the interval at the specify offset.
     *
     * @throws InvalidIndex If the offset is illegal for the current sequence.
     */
    public function set(int $offset, Period $interval): void
    {
        $this->get($offset);
        $this->intervals[$offset] = $interval;
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
        $interval = $this->get($offset);
        unset($this->intervals[$offset]);

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
     * @param callable $func Accepts the carry, the current value and the current offset, and
     *                       returns an updated carry value.
     *
     * @param mixed|null $carry Optional initial carry value.
     *
     * @return mixed The carry value of the final iteration, or the initial
     *               value if the sequence was empty.
     */
    public function reduce(callable $func, $carry = null)
    {
        foreach ($this->intervals as $offset => $interval) {
            $carry = $func($carry, $interval, $offset);
        }

        return $carry;
    }
}
