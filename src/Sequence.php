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
interface Sequence extends ArrayAccess, Countable, IteratorAggregate
{
    /**
     * Returns a Interval which represents the smallest time range which contains all the
     * instance Interval objects.
     */
    public function getInterval(): ?Interval;

    /**
     * Returns a new instance with the founded gaps inside the current instance.
     */
    public function getGaps(): self;

    /**
     * Returns a new instance with the founded intersections inside the current instance.
     */
    public function getIntersections(): self;

    /**
     * Returns all the keys of the collection.
     *
     * @return string[]
     */
    public function getKeys(): iterable;

    /**
     * Returns all the Interval objects of the collection.
     *
     * @return Interval[]
     */
    public function getValues(): iterable;

    /**
     * Returns an array representation of the instance.
     */
    public function toArray(): array;

    /**
     * Remove all the Interval objects from the instance.
     */
    public function clear(): void;

    /**
     * Get the Interval object at the specified index.
     *
     * @param string|int $index
     *
     * @return ?Interval
     */
    public function get($index): ?Interval;

    /**
     * Returns the first Interval object of the instance.
     *
     * @return ?Interval
     */
    public function first(): ?Interval;

    /**
     * Returns the last Interval of the instance.
     *
     * @return ?Interval
     */
    public function last(): ?Interval;

    /**
     * Tells whether the submitted Interval object is present in the collection.
     */
    public function has(Interval $interval): bool;

    /**
     * Returns the index of a given Interval if present in the collection
     * or false.
     *
     * @return string|int|bool
     */
    public function indexOf(Interval $interval);

    /**
     * Tells whether the index is attached to a Interval present in the collection.
     *
     * @param string|int $index
     */
    public function hasIndex($index): bool;

    /**
     * Sorts the collection using a user-defined function while maitaining index association.
     *
     * @see https://php.net/manual/en/function.uasort.php
     */
    public function sort(callable $callable): bool;

    /**
     * Adds the given Interval to the Collection.
     */
    public function add(Interval $interval): void;

    /**
     * Set the Interval object at the specified index.
     *
     * @param string|int $index
     */
    public function set($index, Interval $interval): void;

    /**
     * Removes the Interval from the instance if present.
     */
    public function remove(Interval $interval): bool;

    /**
     * Removes the Interval at a given index from the instance if present.
     *
     * @param string|int $index
     *
     * @return ?Interval
     */
    public function removeIndex($index): ?Interval;

    /**
     * Returns all the Interval objects of this instance that satisfy the filter $filter.
     * The order of the Interval objects are preserved.
     *
     * @see https://php.net/manual/en/function.array-filter.php
     */
    public function filter(callable $filter, int $flag = 0): self;

    /**
     * Applies a mapper $mapper to all the Interval objects of this instance and
     * returns a new instance with the Interval objects returned by the mapper.
     *
     * @see https://php.net/manual/en/function.array-map.php
     */
    public function map(callable $mapper): self;

    /**
     * Splits this instance into two separate new instances according to a predicate.
     * Keys are preserved in the resulting instances.
     *
     * @param callable $predicate the predicate take at most 2 variables
     *                            - the current Interval object
     *                            - the current offset
     *
     * @return Sequence[] An array with two elements. The first element contains the collection
     *                    of elements where the predicate returned TRUE, the second element
     *                    contains the collection of elements where the predicate returned FALSE.
     */
    public function partition(callable $predicate): array;

    /**
     * Extracts a slice of this instance into a new instance. Keys are preserved.
     *
     * @see https://php.net/manual/en/function.array-slice.php
     *
     * @param null|int $length
     */
    public function slice(int $offset, int $length = null): self;
}
