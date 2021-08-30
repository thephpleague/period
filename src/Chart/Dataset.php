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
use function gettype;
use function is_scalar;
use function method_exists;
use function strlen;

final class Dataset implements Data
{
    /**
     * @var array<int, array{0:string, 1:Sequence}>.
     */
    private $pairs = [];

    /**
     * @var int
     */
    private $labelMaxLength = 0;

    /**
     * @var Period|null
     */
    private $boundaries;

    /**
     * constructor.
     */
    public function __construct(iterable $pairs = [])
    {
        $this->appendAll($pairs);
    }

    /**
     * Creates a new collection from a countable iterable structure.
     *
     * @param array|(\Countable&iterable) $items
     * @param ?LabelGenerator $labelGenerator
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
    public function append($label, $item): void
    {
        if (!is_scalar($label) && !method_exists($label, '__toString')) {
            throw new \TypeError('The label passed to '.__METHOD__.' must be a scalar or an stringable object, '.gettype($label).' given.');
        }

        if ($item instanceof Period) {
            $item = new Sequence($item);
        }

        if (!$item instanceof Sequence) {
            throw new \TypeError('The item passed to '.__METHOD__.' must be a '.Period::class.' or a '.Sequence::class.' instance, '.gettype($item).' given.');
        }

        $label = (string) $label;
        $this->setLabelMaxLength($label);
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
            $this->boundaries = $sequence->length();

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
        $mapper = static function (array $pair): array {
            return ['label' => $pair[0], 'item' => $pair[1]];
        };

        return array_map($mapper, $this->pairs);
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
