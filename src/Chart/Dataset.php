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

use Iterator;
use IteratorAggregate;
use League\Period\Period;
use League\Period\Sequence;
use MultipleIterator;
use TypeError;

use function array_column;
use function count;
use function strlen;

final class Dataset implements Data
{
    /** @var array<array{0:int|string, 1:Sequence}> */
    private array $pairs = [];
    private int $labelMaxLength = 0;
    private Period|null $length = null;

    /**
     * @param array<array{0:string|int, 1:Period|Sequence}>|Iterator<array{0:string|int, 1:Period|Sequence}>|IteratorAggregate<array{0:string|int, 1:Period|Sequence}> $pairs
     */
    public function __construct(array|Iterator|IteratorAggregate $pairs = [])
    {
        $this->appendAll($pairs);
    }

    /**
     * Creates a new collection from a countable iterable structure.
     *
     * @param iterable<array-key, Period|Sequence> $items
     */
    public static function fromItems(iterable $items, LabelGenerator $labelGenerator = new LatinLetter('A')): self
    {
        if (!is_countable($items)) {
            throw new TypeError('The submitted items collection should be countable.');
        }

        $pairs = new MultipleIterator(MultipleIterator::MIT_NEED_ALL | MultipleIterator::MIT_KEYS_ASSOC);
        $pairs->attachIterator($labelGenerator->generate(count($items)), 0);
        $pairs->attachIterator((function () use ($items): Iterator {
            foreach ($items as $key => $value) {
                yield $key => $value;
            }
        })(), 1);

        return new self($pairs);
    }

    /**
     * Creates a new collection from a generic iterable structure.
     *
     * @param iterable<string|int, Period|Sequence> $iterable
     */
    public static function fromIterable(iterable $iterable): self
    {
        $dataset = new self();
        foreach ($iterable as $label => $item) {
            $dataset->append($label, $item);
        }

        return $dataset;
    }

    /**
     * @param iterable<array{0:string|int, 1:Period|Sequence}> $pairs
     */
    public function appendAll(iterable $pairs): self
    {
        foreach ($pairs as [$label, $item]) {
            $this->append($label, $item);
        }

        return $this;
    }

    public function append(string|int $label, Period|Sequence $item): self
    {
        if ($item instanceof Period) {
            $item = new Sequence($item);
        }

        $this->setLabelMaxLength((string) $label);
        $this->setLength($item);

        $this->pairs[] = [$label, $item];

        return $this;
    }

    /**
     * Computes the label maximum length for the dataset.
     */
    private function setLabelMaxLength(string $label): void
    {
        $labelLength = strlen($label);
        if ($this->labelMaxLength < $labelLength) {
            $this->labelMaxLength = $labelLength;
        }
    }

    /**
     * Computes the Period boundary for the dataset.
     */
    private function setLength(Sequence $sequence): void
    {
        if (null === $this->length) {
            $this->length = $sequence->length();

            return;
        }

        $this->length = $this->length->merge(...$sequence);
    }

    public function count(): int
    {
        return count($this->pairs);
    }

    public function getIterator(): Iterator
    {
        foreach ($this->pairs as $pair) {
            yield $pair;
        }
    }

    /**
     * @return array<array{label:string|int, item:Sequence}>
     */
    public function jsonSerialize(): array
    {
        return array_map(
            fn (array $pair): array => ['label' => $pair[0], 'item' => $pair[1]],
            $this->pairs
        );
    }

    public function isEmpty(): bool
    {
        return [] === $this->pairs;
    }

    public function labels(): array
    {
        return array_column($this->pairs, 0);
    }

    public function items(): array
    {
        return array_column($this->pairs, 1);
    }

    public function length(): Period|null
    {
        return $this->length;
    }

    public function labelMaxLength(): int
    {
        return $this->labelMaxLength;
    }
}
