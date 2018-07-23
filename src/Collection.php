<?php

/**
 * League.Uri (https://period.thephpleague.com).
 *
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @license https://github.com/thephpleague/period/blob/master/LICENSE (MIT License)
 * @version 4.0.0
 * @link    https://github.com/thephpleague/period
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace League\Period;

use ArrayAccess;
use Countable;
use IteratorAggregate;
use TypeError;
use function array_filter;
use function array_key_exists;
use function array_keys;
use function array_map;
use function array_slice;
use function array_values;
use function count;
use function end;
use function get_class;
use function gettype;
use function is_int;
use function is_object;
use function is_string;
use function reset;
use function sprintf;
use function uasort;

/**
 * A class to ease handling PeriodInterface objects collection.
 *
 * This class is heavily inspired by the Doctrine\Common\Collections\Collection interface
 *
 * @package League.period
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @since   4.0.0
 */
final class Collection implements ArrayAccess, Countable, IteratorAggregate
{
    /**
     * @var PeriodInterface[]
     */
    protected $storage = [];

    /**
     * Create a new instance.
     *
     * @param PeriodInterface[] $periods
     */
    public function __construct(iterable $periods = [])
    {
        foreach ($periods as $offset => $value) {
            $this->offsetSet($offset, $value);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value): void
    {
        if (!$value instanceof PeriodInterface) {
            throw new TypeError(sprintf(
                'a %s only contains % objects, you try to add a %s instead',
                Collection::class,
                PeriodInterface::class,
                is_object($value) ? get_class($value) : gettype($value)
            ));
        }

        if (null === $offset) {
            $this->storage[] = $value;
            return;
        }

        $this->storage[$offset] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset): ?PeriodInterface
    {
        return $this->storage[$offset] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->storage);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset): void
    {
        unset($this->storage[$offset]);
    }

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        return count($this->storage);
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator(): iterable
    {
        foreach ($this->storage as $offset => $period) {
            yield $offset => $period;
        }
    }

    /**
     * Returns all the keys of the collection.
     *
     * @return string[]
     */
    public function getKeys(): array
    {
        return array_keys($this->storage);
    }

    /**
     * Returns all the PeriodInterface objects of the collection.
     *
     * @return PeriodInterface[]
     */
    public function getValues(): array
    {
        return array_values($this->storage);
    }

    /**
     * Returns an array representation of the collection.
     */
    public function toArray(): array
    {
        return $this->storage;
    }

    /**
     * Remove all the periods from the Collection.
     */
    public function clear(): void
    {
        $this->storage = [];
    }

    /**
     * Get the PeriodInterface object at the specified index.
     *
     * @param string|int $index
     *
     * @return ?PeriodInterface
     */
    public function get($index): ?PeriodInterface
    {
        return $this->offsetGet($index);
    }

    /**
     * Returns the first period of the collection.
     *
     * @return ?PeriodInterface
     */
    public function first(): ?PeriodInterface
    {
        $period = reset($this->storage);
        if (false === $period) {
            return null;
        }

        return $period;
    }

    /**
     * Returns the last period of the collection.
     *
     * @return ?PeriodInterface
     */
    public function last(): ?PeriodInterface
    {
        $period = end($this->storage);
        if (false === $period) {
            return null;
        }

        return $period;
    }

    /**
     * Tells whether the submitted PeriodInterface object is present in the collection.
     */
    public function contains(PeriodInterface $period): bool
    {
        return false !== $this->indexOf($period);
    }

    /**
     * Returns the index of a given PeriodInterface if present in the collection
     * or false.
     *
     * @return string|int|bool
     */
    public function indexOf(PeriodInterface $period)
    {
        foreach ($this->storage as $index => $stored_period) {
            if ($period->equalsTo($stored_period)) {
                return $index;
            }
        }

        return false;
    }

    /**
     * Tells whether the index is attached to a PeriodInterface present in the collection.
     *
     * @param string|int $index
     */
    public function containskey($index): bool
    {
        return $this->offsetExists($index);
    }

    /**
     * Sorts the collection using a user-defined function while maitaining index association.
     *
     * @see https://php.net/manual/en/function.uasort.php
     */
    public function sort(callable $callable): bool
    {
        return uasort($this->storage, $callable);
    }

    /**
     * Adds the given Period from the Collection if present.
     */
    public function add(PeriodInterface $period): void
    {
        $this->offsetSet(null, $period);
    }

    /**
     * Set the PeriodInterface object at the specified index.
     *
     * @param string|int $index
     */
    public function set($index, PeriodInterface $period): void
    {
        if (!is_int($index) && !is_string($index)) {
            throw new TypeError(sprintf(
                'the index must be a string or an int, you try to use a %s instead',
                is_object($index) ? get_class($index) : gettype($index)
            ));
        }

        $this->offsetSet($index, $period);
    }

    /**
     * Remove the PeriodInterface from the Collection if present.
     */
    public function remove(PeriodInterface $period): bool
    {
        $offset = $this->indexOf($period);
        if (false !== $offset) {
            $this->offsetUnset($offset);
        }

        return (bool) $offset;
    }

    /**
     * Remove the PeriodInterface at a given index from the Collection if present.
     *
     * @param string|int $index
     *
     * @return ?PeriodInterface
     */
    public function removeIndex($index): ?PeriodInterface
    {
        $period = $this->offsetGet($index);
        if (null !== $period) {
            $this->offsetUnset($index);
        }

        return $period;
    }

    /**
     * Returns all the Period objects of this collection that satisfy the filter $filter.
     * The order of the Period objetcs are preserved.
     *
     * @see https://php.net/manual/en/function.array-filter.php
     */
    public function filter(callable $filter, int $flag = 0): self
    {
        return new self(array_filter($this->storage, $filter, $flag));
    }

    /**
     * Applies a mapper $mapper to all the Periods object of this collection and
     * returns a new collection with the elements returned by the mapper.
     *
     * @see https://php.net/manual/en/function.array-map.php
     */
    public function map(callable $mapper): self
    {
        return new self(array_map($mapper, $this->storage));
    }

    /**
     * Splits this collection into two separate collections according to a predicate.
     * Keys are preserved in the resulting collections.
     *
     * @param callable $predicate the predicate take at most 2 variables
     *                            - the current PeriodInterface object
     *                            - the current offset
     *
     * @return Collection[] An array with two elements. The first element contains the collection
     *                      of elements where the predicate returned TRUE, the second element
     *                      contains the collection of elements where the predicate returned FALSE.
     */
    public function partition(callable $predicate): array
    {
        $matches = new self();
        $no_matches = new self();
        foreach ($this->storage as $offset => $period) {
            $collection = 'no_matches';
            if (true === $predicate($period, $offset)) {
                $collection = 'matches';
            }
            $$collection[$offset] = $period;
        }

        return [$matches, $no_matches];
    }

    /**
     * Extracts a slice of this collection into a new collection. Keys are preserved.
     *
     * @see https://php.net/manual/en/function.array-slice.php
     *
     * @param null|int $length
     */
    public function slice(int $offset, int $length = null): self
    {
        return new self(array_slice($this->storage, $offset, $length, true));
    }

    /**
     * Returns a new instance with the founded gaps inside the current collection.
     */
    public function getGaps(): self
    {
        $periods = clone $this;
        $periods->sort(function (PeriodInterface $period1, PeriodInterface $period2) {
            return $period1->getStartDate() <=> $period2->getStartDate();
        });

        $collection = new self();
        $current = $periods->first();
        if (null === $current) {
            return $collection;
        }

        $periods->remove($current);
        foreach ($periods as $next) {
            if (!$current->overlaps($next) && !$current->abuts($next)) {
                $collection[] = $current->gap($next);
            }

            if (!$current->contains($next)) {
                $current = $next;
            }
        }

        return $collection;
    }

    /**
     * Returns a new instance with the founded intersections inside the current collection.
     */
    public function getIntersections(): self
    {
        $periods = clone $this;
        $periods->sort(function (PeriodInterface $period1, PeriodInterface $period2) {
            return $period1->getStartDate() <=> $period2->getStartDate();
        });

        $collection = new self();
        $current = $periods->first();
        if (null === $current) {
            return $collection;
        }

        $periods->remove($current);
        foreach ($periods as $next) {
            if ($current->overlaps($next)) {
                $collection[] = $current->intersect($next);
            }

            if (!$current->contains($next)) {
                $current = $next;
            }
        }

        return $collection;
    }
}
