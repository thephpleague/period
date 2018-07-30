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

use TypeError;
use function array_key_exists;
use function array_keys;
use function array_pop;
use function array_push;
use function array_shift;
use function array_slice;
use function array_unshift;
use function array_values;
use function count;
use function end;
use function get_class;
use function gettype;
use function is_object;
use function reset;
use function sprintf;
use function uasort;

/**
 * A class to ease handling Interval objects collection.
 *
 * @package League.period
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @since   4.0.0
 */
final class Sequence implements Collection
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
     * {@inheritdoc}
     */
    public function getInterval(): ?Interval
    {
        $reducer = function ($carry, $index, Interval $interval) {
            if ($interval->getStartDate() < $carry->getStartDate()) {
                $carry = $carry->startingOn($interval->getStartDate());
            }

            if ($interval->getEndDate() > $carry->getEndDate()) {
                $carry = $carry->endingOn($interval->getEndDate());
            }

            return $carry;
        };

        return $this->reduce($reducer, $this->first());
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function isEmpty(): bool
    {
        return [] === $this->storage;
    }

    /**
     * {@inheritdoc}
     */
    public function getKeys(): iterable
    {
        return array_keys($this->storage);
    }

    /**
     * {@inheritdoc}
     */
    public function getValues(): iterable
    {
        return array_values($this->storage);
    }

    /**
     * {@inheritdoc}
     */
    public function clear(): void
    {
        $this->storage = [];
    }

    /**
     * {@inheritdoc}
     */
    public function get($index): ?Interval
    {
        return $this->offsetGet($index);
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function has(Interval $interval): bool
    {
        return false !== $this->indexOf($interval);
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function hasIndex($index): bool
    {
        return $this->offsetExists($index);
    }

    /**
     * {@inheritdoc}
     */
    public function sort(callable $callable): bool
    {
        return uasort($this->storage, $callable);
    }

    /**
     * {@inheritdoc}
     */
    public function set($index, Interval $interval): void
    {
        $this->offsetSet($index, $interval);
    }

    /**
     * {@inheritdoc}
     */
    public function push(Interval $interval, Interval ...$intervals): int
    {
        return array_push($this->storage, $interval, ...$intervals);
    }

    /**
     * {@inheritdoc}
     */
    public function pop(): ?Interval
    {
        return array_pop($this->storage);
    }

    /**
     * {@inheritdoc}
     */
    public function unshift(Interval $interval, Interval ...$intervals): int
    {
        return array_unshift($this->storage, $interval, ...$intervals);
    }

    /**
     * {@inheritdoc}
     */
    public function shift(): ?Interval
    {
        return array_shift($this->storage);
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function exists(callable $filter): bool
    {
        foreach ($this as $offset => $interval) {
            if (true === $filter($offset, $interval)) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function filter(callable $filter): Collection
    {
        $collection = new self();
        foreach ($this as $offset => $interval) {
            if (true === $filter($offset, $interval)) {
                $collection->set($offset, $interval);
            }
        }

        return $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function map(callable $mapper): Collection
    {
        $collection = new self();
        foreach ($this as $offset => $interval) {
            $collection->set($offset, $mapper($offset, $interval));
        }

        return $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function partition(callable $predicate): array
    {
        $matches = new self();
        $no_matches = new self();
        foreach ($this->storage as $offset => $interval) {
            $collection = 'no_matches';
            if (true === $predicate($offset, $interval)) {
                $collection = 'matches';
            }
            $$collection[$offset] = $interval;
        }

        return [$matches, $no_matches];
    }

    /**
     * {@inheritdoc}
     */
    public function slice(int $offset, int $length = null): Collection
    {
        return new self(array_slice($this->storage, $offset, $length, true));
    }

    /**
     * {@inheritdoc}
     */
    public function reduce(callable $callable, $initial = null)
    {
        $carry = $initial;
        foreach ($this->storage as $offset => $value) {
            $carry = $callable($carry, $offset, $value);
        }

        return $carry;
    }
}
