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
    public readonly int $startingLabel;

    public function __construct(int $startingLabel)
    {
        $this->startingLabel = $this->filterStart($startingLabel);
    }

    public function generate(int $nbLabels): Iterator
    {
        if (0 >= $nbLabels) {
            return;
        }

        $count = 0;
        $end = $this->startingLabel + $nbLabels;
        $label = $this->startingLabel;
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
    public function startingAt(int $startingLabel): self
    {
        $startingLabel = $this->filterStart($startingLabel);

        if ($startingLabel === $this->startingLabel) {
            return $this;
        }

        return new self($startingLabel);
    }

    private function filterStart(int $start): int
    {
        if (0 >= $start) {
            return 1;
        }

        return $start;
    }
}
