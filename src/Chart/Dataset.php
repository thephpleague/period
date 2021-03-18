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

use League\Period\Period;
use League\Period\Sequence;
use function array_column;
use function count;
use function strlen;

final class Dataset implements Data
{
    /**
     * @var array{0:int|string, 1:Sequence}[]
     */
    private array $pairs = [];
    private int $labelMaxLength = 0;
    private Period|null $boundaries = null;

    public function __construct(iterable $pairs = [])
    {
        $this->appendAll($pairs);
    }

    /**
     * Creates a new collection from a countable iterable structure.
     *
     * @param array|(\Countable&iterable) $items
     * @param ?LabelGenerator             $labelGenerator
     */
    public static function fromItems($items, ?LabelGenerator $labelGenerator = null): self
    {
        $nbItems = count($items);
        $items = (function () use ($items): \Iterator {
            foreach ($items as $key => $value) {
                yield $key => $value;
            }
        })();

        $labelGenerator = $labelGenerator ?? new LatinLetter();

        $pairs = new \MultipleIterator(\MultipleIterator::MIT_NEED_ALL|\MultipleIterator::MIT_KEYS_ASSOC);
        $pairs->attachIterator($labelGenerator->generate($nbItems), '0');
        $pairs->attachIterator($items, '1');

        return new self($pairs);
    }

    /**
     * Creates a new collection from a generic iterable structure.
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
     * {@inheritDoc}
     */
    public function appendAll(iterable $pairs): void
    {
        foreach ($pairs as [$label, $item]) {
            $this->append($label, $item);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function append(string|int $label, Period|Sequence $item): void
    {
        if ($item instanceof Period) {
            $item = new Sequence($item);
        }

        $this->setLabelMaxLength((string) $label);
        $this->setBoundaries($item);

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
    private function setBoundaries(Sequence $sequence): void
    {
        if (null === $this->boundaries) {
            $this->boundaries = $sequence->boundaries();

            return;
        }

        $this->boundaries = $this->boundaries->merge(...$sequence);
    }

    /**
     * {@inheritDoc}
     */
    public function count(): int
    {
        return count($this->pairs);
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator(): \Iterator
    {
        foreach ($this->pairs as $pair) {
            yield $pair;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function jsonSerialize(): array
    {
        return array_map(
            static fn (array $pair): array => ['label' => $pair[0], 'item' => $pair[1]],
            $this->pairs
        );
    }

    /**
     * {@inheritDoc}
     */
    public function isEmpty(): bool
    {
        return [] === $this->pairs;
    }

    /**
     * {@inheritDoc}
     */
    public function labels(): array
    {
        return array_column($this->pairs, 0);
    }

    /**
     * {@inheritDoc}
     */
    public function items(): array
    {
        return array_column($this->pairs, 1);
    }

    /**
     * {@inheritDoc}
     */
    public function boundaries(): ?Period
    {
        return $this->boundaries;
    }

    /**
     * {@inheritDoc}
     */
    public function labelMaxLength(): int
    {
        return $this->labelMaxLength;
    }
}
