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
 * A class to ease handling Interval objects collection.
 *
 * This class is heavily inspired by the Doctrine\Common\Collections\Collection interface
 *
 * @package League.period
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @since   4.0.0
 */
abstract class ProxySequence implements Sequence
{
    /**
     * @var Sequence
     */
    private $sequence;

    /**
     * Create a new instance.
     */
    public function __construct(Sequence $sequence)
    {
        $this->sequence = $sequence;
    }

    /**
     * {@inheritdoc}
     */
    public function getInterval(): ?Interval
    {
        return $this->sequence->getInterval();
    }

    /**
     * {@inheritdoc}
     */
    public function getGaps(): Sequence
    {
        return $this->sequence->getGaps();
    }

    /**
     * {@inheritdoc}
     */
    public function getIntersections(): Sequence
    {
        return $this->sequence->getIntersections();
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value): void
    {
        $this->sequence->offsetSet($offset, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset): ?Interval
    {
        return $this->sequence[$offset] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset): bool
    {
        return isset($this->sequence[$offset]);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset): void
    {
        unset($this->sequence[$offset]);
    }

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        return $this->sequence->count();
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator(): iterable
    {
        return $this->sequence->getIterator();
    }

    /**
     * {@inheritdoc}
     */
    public function getKeys(): iterable
    {
        return $this->sequence->getKeys();
    }

    /**
     * {@inheritdoc}
     */
    public function getValues(): iterable
    {
        return $this->sequence->getValues();
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return $this->sequence->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function clear(): void
    {
        $this->sequence->clear();
    }

    /**
     * {@inheritdoc}
     */
    public function get($index): ?Interval
    {
        return $this->sequence[$index];
    }

    /**
     * {@inheritdoc}
     */
    public function first(): ?Interval
    {
        return $this->sequence->first();
    }

    /**
     * {@inheritdoc}
     */
    public function last(): ?Interval
    {
        return $this->sequence->last();
    }

    /**
     * {@inheritdoc}
     */
    public function has(Interval $interval): bool
    {
        return $this->sequence->has($interval);
    }

    /**
     * {@inheritdoc}
     */
    public function indexOf(Interval $interval)
    {
        return $this->sequence->indexOf($interval);
    }

    /**
     * {@inheritdoc}
     */
    public function hasIndex($index): bool
    {
        return $this->sequence->hasIndex($index);
    }

    /**
     * {@inheritdoc}
     */
    public function sort(callable $callable): bool
    {
        return $this->sequence->sort($callable);
    }

    /**
     * {@inheritdoc}
     */
    public function add(Interval $interval): void
    {
        $this->sequence->add($interval);
    }

    /**
     * {@inheritdoc}
     */
    public function set($index, Interval $interval): void
    {
        $this->sequence->set($index, $interval);
    }

    /**
     * {@inheritdoc}
     */
    public function remove(Interval $interval): bool
    {
        return $this->sequence->remove($interval);
    }

    /**
     * {@inheritdoc}
     */
    public function removeIndex($index): ?Interval
    {
        return $this->sequence->removeIndex($index);
    }

    /**
     * {@inheritdoc}
     */
    public function filter(callable $filter, int $flag = 0): Sequence
    {
        return $this->sequence->filter($filter, $flag);
    }

    /**
     * {@inheritdoc}
     */
    public function map(callable $mapper): Sequence
    {
        return $this->sequence->map($mapper);
    }

    /**
     * {@inheritdoc}
     */
    public function partition(callable $predicate): array
    {
        return $this->sequence->partition($predicate);
    }

    /**
     * {@inheritdoc}
     */
    public function slice(int $offset, int $length = null): Sequence
    {
        return $this->sequence->slice($offset, $length);
    }
}
