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

namespace League\Period\Chart;

use Countable;
use Iterator;
use League\Period\Period;
use League\Period\Sequence;

interface Data extends Countable, \IteratorAggregate, \JsonSerializable
{
    /**
     * Returns the number of pairs.
     */
    public function count(): int;

    /**
     * Returns the pairs.
     *
     * @return \Iterator<int, array{0: int|string, 1: Sequence}>
     */
    public function getIterator(): Iterator;

    /**
     * @var array<int, array{label:string, item:Sequence}>.
     */
    public function jsonSerialize(): array;

    /**
     * Tells whether the instance is empty.
     */
    public function isEmpty(): bool;

    /**
     * @return array<string|int>
     */
    public function labels(): iterable;

    /**
     * @return Sequence[]
     */
    public function items(): iterable;

    /**
     * Returns the dataset length.
     */
    public function length(): ?Period;

    /**
     * Returns the label maximum length.
     */
    public function labelMaxLength(): int;

    /**
     * Add a new pair to the collection.
     * @param string|int      $label
     * @param Period|Sequence $item
     */
    public function append(string|int $label, Period|Sequence $item): void;

    /**
     * Add a collection of pairs to the collection.
     */
    public function appendAll(iterable $pairs): void;
}
