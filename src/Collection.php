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

/**
 * A PHP Interface to represent a colection of Interval objects.
 *
 * This interface is heavily inspired by
 *
 * - Doctrine\Common\Collections\Collection interface
 * - Ds\Collection interface
 * - Ds\Sequence interface
 *
 * @see https://github.com/php-ds/polyfill/blob/master/src/Collection.php
 * @see https://github.com/php-ds/polyfill/blob/master/src/Sequence.php
 * @see https://github.com/doctrine/collections/blob/master/lib/Doctrine/Common/Collections/Collection.php
 *
 * @package League.period
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @since   4.0.0
 */
interface Collection extends ArrayAccess, Countable, IteratorAggregate
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
     * Tells whether the collection is empty.
     */
    public function isEmpty(): bool;

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
     * Tells whether at least one Interval in the current instance satisfies the predicate.
     *
     *
     * @param callable $predicate accepts 2 arguments
     *                            - the offset
     *                            - the Interval object
     *                            returns a boolean telling whether the predicate is satisfied or not.
     */
    public function exists(callable $predicate): bool;

    /**
     * Sorts the collection using a user-defined function while maitaining index association.
     *
     * @see https://php.net/manual/en/function.uasort.php
     */
    public function sort(callable $callable): bool;

    /**
     * Set the Interval object at the specified index.
     *
     * @param string|int $index
     */
    public function set($index, Interval $interval): void;

    /**
     * Remove the last item from the Collection and return it.
     */
    public function pop(): ?Interval;

    /**
     * Adds on or more Intervals object at the end of the instance.
     *
     * @param Interval ...$intervals
     */
    public function push(Interval $interval, Interval ...$intervals): int;

    /**
     * Shift an Interval off the beginning of array.
     */
    public function shift(): ?Interval;

    /**
     * Prepends one or more Interval to the beginning of the instance.
     *
     * @param Interval ...$intervals
     */
    public function unshift(Interval $interval, Interval ...$intervals): int;

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
     * @param callable $filter accepts 2 arguments
     *                         - the offset
     *                         - the Interval object
     *                         returns a boolean telling whether the predicate is satisfied or not.
     */
    public function filter(callable $filter): self;

    /**
     * Applies a mapper $mapper to all the Interval objects of this instance and
     * returns a new instance with the Interval objects returned by the mapper.
     *
     * Keys are preserved in the resulting instance.
     *
     * @param callable $mapper accepts 2 arguments
     *                         - the offset
     *                         - the Interval object
     *                         returns an Interval object
     */
    public function map(callable $mapper): self;

    /**
     * Splits this instance into two separate new instances according to a predicate.
     *
     * Keys are preserved in the resulting instance.
     *
     * @param callable $predicate accepts 2 arguments
     *                            - the offset
     *                            - the Interval object
     *                            returns a boolean telling whether the predicate is satisfied or not.
     *
     * @return Collection[] An array with two elements. The first element contains the collection
     *                      of elements where the predicate returned TRUE, the second element
     *                      contains the collection of elements where the predicate returned FALSE.
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

    /**
     * Iteratively reduces the instance to a single value using a callback.
     *
     * @param callable $callable accepts 3 arguments
     *                           - the carry
     *                           - the offset
     *                           - the Interval object
     *                           returns an updated carry value.
     *
     * @param mixed|null $initial Optional initial carry value.
     *
     * @return mixed The carry value of the final iteration, or the initial
     *               value if the map was empty.
     */
    public function reduce(callable $callable, $initial = null);
}
