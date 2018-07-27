<?php

/**
 * League.Period (https://period.thephpleague.com).
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
 * A class to ease handling Interval objects collection.
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
     * @var Interval[]
     */
    private $storage = [];

    /**
     * Create a new instance.
     */
    public function __construct(iterable $intervals = [])
    {
        foreach ($intervals as $offset => $value) {
            $this->offsetSet($offset, $value);
        }
    }

    /**
     * Returns a Interval which represents the smallest time range which contains all the
     * instance Interval objects.
     */
    public function getInterval(): ?Interval
    {
        $interval = null;
        foreach ($this->storage as $item) {
            if (null === $interval) {
                $interval = $item;
                continue;
            }

            if ($item->getStartDate() < $interval->getStartDate()) {
                $interval = $interval->startingOn($item->getStartDate());
            }

            if ($item->getEndDate() > $interval->getEndDate()) {
                $interval = $interval->endingOn($item->getEndDate());
            }
        }

        return $interval;
    }

    /**
     * Returns a new instance with the founded gaps inside the current instance.
     */
    public function getGaps(): Collection
    {
        $intervals = clone $this;
        $intervals->sort(function (Interval $interval1, Interval $interval2) {
            return $interval1->getStartDate() <=> $interval2->getStartDate();
        });

        $collection = new self();
        $current = $intervals->first();
        if (null === $current) {
            return $collection;
        }

        $intervals->remove($current);
        foreach ($intervals as $next) {
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
     * Returns a new instance with the founded intersections inside the current instance.
     */
    public function getIntersections(): Collection
    {
        $intervals = clone $this;
        $intervals->sort(function (Interval $interval1, Interval $interval2) {
            return $interval1->getStartDate() <=> $interval2->getStartDate();
        });

        $collection = new self();
        $current = $intervals->first();
        if (null === $current) {
            return $collection;
        }

        $intervals->remove($current);
        foreach ($intervals as $next) {
            if ($current->overlaps($next)) {
                $collection[] = $current->intersect($next);
            }

            if (!$current->contains($next)) {
                $current = $next;
            }
        }

        return $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value): void
    {
        if (!$value instanceof Interval) {
            throw new TypeError(sprintf(
                'a %s only contains % objects, you try to add a %s instead',
                Collection::class,
                Interval::class,
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
    public function offsetGet($offset): ?Interval
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
        foreach ($this->storage as $offset => $interval) {
            yield $offset => $interval;
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
     * Returns all the Interval objects of the collection.
     *
     * @return Interval[]
     */
    public function getValues(): array
    {
        return array_values($this->storage);
    }

    /**
     * Returns an array representation of the instance.
     */
    public function toArray(): array
    {
        return $this->storage;
    }

    /**
     * Remove all the Interval objects from the instance.
     */
    public function clear(): void
    {
        $this->storage = [];
    }

    /**
     * Get the Interval object at the specified index.
     *
     * @param string|int $index
     *
     * @return ?Interval
     */
    public function get($index): ?Interval
    {
        return $this->offsetGet($index);
    }

    /**
     * Returns the first Interval object of the instance.
     *
     * @return ?Interval
     */
    public function first(): ?Interval
    {
        $interval = reset($this->storage);
        if (false === $interval) {
            return null;
        }

        return $interval;
    }

    /**
     * Returns the last Interval of the instance.
     *
     * @return ?Interval
     */
    public function last(): ?Interval
    {
        $interval = end($this->storage);
        if (false === $interval) {
            return null;
        }

        return $interval;
    }

    /**
     * Tells whether the submitted Interval object is present in the collection.
     */
    public function has(Interval $interval): bool
    {
        return false !== $this->indexOf($interval);
    }

    /**
     * Returns the index of a given Interval if present in the collection
     * or false.
     *
     * @return string|int|bool
     */
    public function indexOf(Interval $interval)
    {
        foreach ($this->storage as $index => $stored_interval) {
            if ($interval->equalsTo($stored_interval)) {
                return $index;
            }
        }

        return false;
    }

    /**
     * Tells whether the index is attached to a Interval present in the collection.
     *
     * @param string|int $index
     */
    public function hasIndex($index): bool
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
     * Adds the given Interval to the Collection.
     */
    public function add(Interval $interval): void
    {
        $this->offsetSet(null, $interval);
    }

    /**
     * Set the Interval object at the specified index.
     *
     * @param string|int $index
     */
    public function set($index, Interval $interval): void
    {
        if (!is_int($index) && !is_string($index)) {
            throw new TypeError(sprintf(
                'the index must be a string or an int, you try to use a %s instead',
                is_object($index) ? get_class($index) : gettype($index)
            ));
        }

        $this->offsetSet($index, $interval);
    }

    /**
     * Removes the Interval from the instance if present.
     */
    public function remove(Interval $interval): bool
    {
        $offset = $this->indexOf($interval);
        if (false !== $offset) {
            $this->offsetUnset($offset);
        }

        return (bool) $offset;
    }

    /**
     * Removes the Interval at a given index from the instance if present.
     *
     * @param string|int $index
     *
     * @return ?Interval
     */
    public function removeIndex($index): ?Interval
    {
        $interval = $this->offsetGet($index);
        if (null !== $interval) {
            $this->offsetUnset($index);
        }

        return $interval;
    }

    /**
     * Returns all the Interval objects of this instance that satisfy the filter $filter.
     * The order of the Interval objects are preserved.
     *
     * @see https://php.net/manual/en/function.array-filter.php
     */
    public function filter(callable $filter, int $flag = 0): Collection
    {
        return new self(array_filter($this->storage, $filter, $flag));
    }

    /**
     * Applies a mapper $mapper to all the Interval objects of this instance and
     * returns a new instance with the Interval objects returned by the mapper.
     *
     * @see https://php.net/manual/en/function.array-map.php
     */
    public function map(callable $mapper): Collection
    {
        return new self(array_map($mapper, $this->storage));
    }

    /**
     * Splits this instance into two separate new instances according to a predicate.
     * Keys are preserved in the resulting instances.
     *
     * @param callable $predicate the predicate take at most 2 variables
     *                            - the current Interval object
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
        foreach ($this->storage as $offset => $interval) {
            $collection = 'no_matches';
            if (true === $predicate($interval, $offset)) {
                $collection = 'matches';
            }
            $$collection[$offset] = $interval;
        }

        return [$matches, $no_matches];
    }

    /**
     * Extracts a slice of this instance into a new instance. Keys are preserved.
     *
     * @see https://php.net/manual/en/function.array-slice.php
     *
     * @param null|int $length
     */
    public function slice(int $offset, int $length = null): Collection
    {
        return new self(array_slice($this->storage, $offset, $length, true));
    }
}
