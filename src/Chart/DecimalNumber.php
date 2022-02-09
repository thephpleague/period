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

/**
 * A class to attach a decimal number to the generated label.
 *
 * @see LabelGenerator
 */
final class DecimalNumber implements LabelGenerator
{
    public readonly int $startLabel;

    public function __construct(int $startLabel)
    {
        $this->startLabel = $this->filterStart($startLabel);
    }

    public function generate(int $nbLabels): Iterator
    {
        if (0 >= $nbLabels) {
            return;
        }

        $count = 0;
        $end = $this->startLabel + $nbLabels;
        $label = $this->startLabel;
        while ($label < $end) {
            yield $count => $this->format((string) $label);

            ++$count;
            ++$label;
        }
    }

    public function format(string $label): string
    {
        return $label;
    }

    /**
     * Return an instance with the starting Letter.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the starting Letter.
     */
    public function startingOn(int $startLabel): self
    {
        $startLabel = $this->filterStart($startLabel);

        if ($startLabel === $this->startLabel) {
            return $this;
        }

        return new self($startLabel);
    }

    private function filterStart(int $start): int
    {
        if (0 >= $start) {
            return 1;
        }

        return $start;
    }
}
