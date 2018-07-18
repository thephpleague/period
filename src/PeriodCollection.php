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
use function array_filter;
use function array_key_exists;
use function array_map;
use function array_search;
use function array_shift;
use function array_slice;
use function count;
use function end;
use function in_array;
use function reset;
use function uasort;

class PeriodCollection implements ArrayAccess, Countable, IteratorAggregate
{
    /**
     * @var PeriodInterface[]
     */
    protected $periods = [];

    /**
     * Create a new instance.
     *
     * @param PeriodInterface[] $periods
     */
    public function __construct(iterable $periods = [])
    {
        foreach ($periods as $offset => $value) {
            $this[$offset] = $value;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        if (!$value instanceof PeriodInterface) {
            throw new Exception(sprintf(
                'a %s only contains % objects, you try to add a %s instead',
                get_class($this),
                PeriodInterface::class,
                is_object($value) ? get_class($value) : gettype($value)
            ));
        }

        if (null === $offset) {
            $this->periods[] = $value;
            return;
        }

        $this->periods[$offset] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        return $this->periods[$offset] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->periods);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        unset($this->periods[$offset]);
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->periods);
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        foreach ($this->periods as $offset => $period) {
            yield $offset => $period;
        }
    }

    /**
     * Returns a Period object which represents the current collection Boundaries.
     *
     * @return ?PeriodInterface
     */
    public function getPeriod(): ?PeriodInterface
    {
        $periods = $this->periods;
        $period = array_shift($periods);

        return null === $period ? null : $period->merge(...array_values($periods));
    }

    /**
     * Remove all the periods from the Collection.
     */
    public function clear(): void
    {
        $this->periods = [];
    }

    /**
     * Returns the string representation of the Collection Boundaries
     * or the empty string if no Period objects is present in the Collection.
     *
     * @see PeriodInterface::__toString
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getPeriod();
    }

    /**
     * Returns the first period of the collection.
     *
     * @return ?PeriodInterface
     */
    public function first(): ?PeriodInterface
    {
        $period = reset($this->periods);

        return false === $period ? null : $period;
    }

    /**
     * Returns the last period of the collection.
     *
     * @return ?PeriodInterface
     */
    public function last(): ?PeriodInterface
    {
        $period = end($this->periods);

        return false === $period ? null : $period;
    }

    /**
     * Tells whether the submitted PeriodInterface object is present in the collection.
     */
    public function has(PeriodInterface $period): bool
    {
        return in_array($period, $this->periods, true);
    }

    /**
     * Returns a collection of founded gaps between successive PeriodInterface object
     * as a new PeriodCollection.
     */
    public function gaps(): self
    {
        $periods = clone $this;
        $periods->sort(function (PeriodInterface $period1, PeriodInterface $period2) {
            return $period1->getStartDate() <=> $period2->getStartDate();
        });

        $collection = new self();
        $first = $periods->first();
        if (null === $first) {
            return $collection;
        }

        $periods->remove($first);
        foreach ($periods as $second) {
            if (!$first->overlaps($second) && !$first->abuts($second)) {
                $collection[] = $first->gap($second);
            }

            if (!$first->contains($second)) {
                $first = $second;
            }
        }

        return $collection;
    }

    /**
     * Returns a collection of founded intersection between successive PeriodInterface object
     * as a new PeriodCollection.
     */
    public function overlaps(): self
    {
        $periods = clone $this;
        $periods->sort(function (PeriodInterface $period1, PeriodInterface $period2) {
            return $period1->getStartDate() <=> $period2->getStartDate();
        });

        $collection = new self();
        $first = $periods->first();
        if (null === $first) {
            return $collection;
        }

        $periods->remove($first);
        foreach ($periods as $second) {
            if ($first->overlaps($second)) {
                $collection[] = $first->intersect($second);
            }

            if (!$first->contains($second)) {
                $first = $second;
            }
        }

        return $collection;
    }

    /**
     * Sorts the collection using a user-defined function while maitaining index association.
     */
    public function sort(callable $callable): bool
    {
        return uasort($this->periods, $callable);
    }

    /**
     * Adds the given Period from the Collection if present.
     */
    public function add(PeriodInterface $period): void
    {
        $this[] = $period;
    }

    /**
     * Removes the given Period from the Collection if present.
     */
    public function remove(PeriodInterface $period): bool
    {
        $offset = array_search($period, $this->periods, true);
        if (false === $offset) {
            return false;
        }

        unset($this->periods[$offset]);

        return true;
    }

    /**
     * Returns all the Period objects of this collection that satisfy the filter $filter.
     * The order of the Period objetcs are preserved.
     *
     * @see https://php.net/manual/en/function.array-filter.php
     */
    public function filter(callable $filter, int $flag = 0): self
    {
        return new self(array_filter($this->periods, $filter, $flag));
    }

    /**
     * Applies a mapper $mapper to all the Periods object of this collection and
     * returns a new collection with the elements returned by the mapper.
     */
    public function map(callable $mapper): self
    {
        return new self(array_map($mapper, $this->periods));
    }

    /**
     * Splits this collection into two separate collections according to a predicate.
     * returns a new collection with the elements returned by the mapper.
     *
     * @param callable $filter the filter take at most 2 variables
     *                         - the PeriodInterface object
     *                         - its current offset
     *
     * @return PeriodCollection[]
     */
    public function split(callable $filter): array
    {
        $matches = [];
        $no_matches = [];
        foreach ($this->periods as $offset => $period) {
            if (true === $filter($period, $offset)) {
                $matches[$offset] = $period;
                continue;
            }
            $no_matches[$offset] = $period;
        }

        return [new self($matches), new self($no_matches)];
    }

    /**
     * Extracts a slice of this collection into a new collection. Keys are preserved.
     *
     * @see https://php.net/manual/en/function.array-slice.php
     *
     * @param ?int $length
     */
    public function slice(int $offset, ?int $length = null): self
    {
        return new self(array_slice($this->periods, $offset, $length, true));
    }
}
