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

/**
 * A Proxy to ease adding more methods to an Collection implementing object.
 *
 * @package League.period
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @since   4.0.0
 */
abstract class ProxyCollection implements Collection
{
    /**
     * @var Collection
     */
    private $collection;

    /**
     * Create a new instance.
     */
    public function __construct(Collection $collection)
    {
        $this->collection = $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function getInterval(): ?Interval
    {
        return $this->collection->getInterval();
    }

    /**
     * {@inheritdoc}
     */
    public function getGaps(): Collection
    {
        return $this->collection->getGaps();
    }

    /**
     * {@inheritdoc}
     */
    public function getIntersections(): Collection
    {
        return $this->collection->getIntersections();
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value): void
    {
        $this->collection->offsetSet($offset, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset): ?Interval
    {
        return $this->collection->offsetGet($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset): bool
    {
        return $this->collection->offsetExists($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset): void
    {
        $this->collection->offsetUnset($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        return $this->collection->count();
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator(): iterable
    {
        return $this->collection->getIterator();
    }

    /**
     * {@inheritdoc}
     */
    public function isEmpty(): bool
    {
        return $this->collection->isEmpty();
    }

    /**
     * {@inheritdoc}
     */
    public function getKeys(): iterable
    {
        return $this->collection->getKeys();
    }

    /**
     * {@inheritdoc}
     */
    public function getValues(): iterable
    {
        return $this->collection->getValues();
    }

    /**
     * {@inheritdoc}
     */
    public function clear(): void
    {
        $this->collection->clear();
    }

    /**
     * {@inheritdoc}
     */
    public function get($index): ?Interval
    {
        return $this->collection->get($index);
    }

    /**
     * {@inheritdoc}
     */
    public function first(): ?Interval
    {
        return $this->collection->first();
    }

    /**
     * {@inheritdoc}
     */
    public function last(): ?Interval
    {
        return $this->collection->last();
    }

    /**
     * {@inheritdoc}
     */
    public function has(Interval $interval): bool
    {
        return $this->collection->has($interval);
    }

    /**
     * {@inheritdoc}
     */
    public function indexOf(Interval $interval)
    {
        return $this->collection->indexOf($interval);
    }

    /**
     * {@inheritdoc}
     */
    public function hasIndex($index): bool
    {
        return $this->collection->hasIndex($index);
    }

    /**
     * {@inheritdoc}
     */
    public function sort(callable $callable): bool
    {
        return $this->collection->sort($callable);
    }

    /**
     * {@inheritdoc}
     */
    public function push(Interval $interval, Interval ...$intervals): int
    {
        return $this->collection->push($interval, ...$intervals);
    }

    /**
     * {@inheritdoc}
     */
    public function pop(): ?Interval
    {
        return $this->collection->pop();
    }

    /**
     * {@inheritdoc}
     */
    public function unshift(Interval $interval, Interval ...$intervals): int
    {
        return $this->collection->unshift($interval, ...$intervals);
    }

    /**
     * {@inheritdoc}
     */
    public function shift(): ?Interval
    {
        return $this->collection->shift();
    }

    /**
     * {@inheritdoc}
     */
    public function set($index, Interval $interval): void
    {
        $this->collection->set($index, $interval);
    }

    /**
     * {@inheritdoc}
     */
    public function remove(Interval $interval): bool
    {
        return $this->collection->remove($interval);
    }

    /**
     * {@inheritdoc}
     */
    public function removeIndex($index): ?Interval
    {
        return $this->collection->removeIndex($index);
    }

    /**
     * {@inheritdoc}
     */
    public function some(callable $filter): bool
    {
        return $this->collection->some($filter);
    }

    /**
     * {@inheritdoc}
     */
    public function every(callable $filter): bool
    {
        return $this->collection->every($filter);
    }

    /**
     * {@inheritdoc}
     */
    public function filter(callable $filter): Collection
    {
        return $this->collection->filter($filter);
    }

    /**
     * {@inheritdoc}
     */
    public function map(callable $mapper): Collection
    {
        return $this->collection->map($mapper);
    }

    /**
     * {@inheritdoc}
     */
    public function partition(callable $predicate): array
    {
        return $this->collection->partition($predicate);
    }

    /**
     * {@inheritdoc}
     */
    public function slice(int $offset, int $length = null): Collection
    {
        return $this->collection->slice($offset, $length);
    }

    /**
     * {@inheritdoc}
     */
    public function reduce(callable $callable, $initial = null)
    {
        return $this->collection->reduce($callable, $initial);
    }
}
