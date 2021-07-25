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
use League\Period\Period;
use League\Period\Sequence;
use MultipleIterator;
use function array_column;
use function count;
use function strlen;

final class Dataset implements Data
{
    /**
     * @var array<array{0:int|string, 1:Sequence}>
     */
    private array $pairs = [];
    private int $labelMaxLength = 0;
    private Period|null $length = null;

    /**
     * @param iterable<array{0:string|int, 1:Period|Sequence}> $pairs
     */
    public function __construct(iterable $pairs = [])
    {
        $this->appendAll($pairs);
    }

    /**
     * Creates a new collection from a countable iterable structure.
     *
     * @param array|(\Countable&iterable) $items
     * @param null|LabelGenerator|null    $labelGenerator
     */
    public static function fromItems($items, LabelGenerator|null $labelGenerator = null): self
    {
        $nbItems = count($items);
        $items = (function () use ($items): Iterator {
            /**
             * @var string|int      $key
             * @var Period|Sequence $value
             */
            foreach ($items as $key => $value) {
                yield $key => $value;
            }
        })();

        $labelGenerator = $labelGenerator ?? new LatinLetter();

        /**
         * @template-implements MultipleIterator<array{0:string|int, 1:Period|Sequence}> $pairs
         */
        $pairs = new MultipleIterator(MultipleIterator::MIT_NEED_ALL|MultipleIterator::MIT_KEYS_ASSOC);
        $pairs->attachIterator($labelGenerator->generate($nbItems), '0');
        $pairs->attachIterator($items, '1');

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
    public function appendAll(iterable $pairs): void
    {
        foreach ($pairs as [$label, $item]) {
            $this->append($label, $item);
        }
    }

    public function append(string|int $label, Period|Sequence $item): void
    {
        if ($item instanceof Period) {
            $item = new Sequence($item);
        }

        $this->setLabelMaxLength((string) $label);
        $this->setLength($item);

        $this->pairs[] = [$label, $item];
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
