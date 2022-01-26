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

final class DecimalNumber implements LabelGenerator
{
    public readonly int $startingAt;

    public function __construct(int $startingAt = 1)
    {
        if (0 >= $startingAt) {
            $startingAt = 1;
        }

        $this->startingAt = $startingAt;
    }

    
    public function generate(int $nbLabels): Iterator
    {
        if (0 >= $nbLabels) {
            return;
        }

        $count = 0;
        $end = $this->startingAt + $nbLabels;
        $value = $this->startingAt;
        while ($value < $end) {
            yield $count => $this->format((string) $value);

            ++$count;
            ++$value;
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
    public function startsWith(int $startingAt): self
    {
        if (0 >= $startingAt) {
            $startingAt = 1;
        }

        if ($startingAt === $this->startingAt) {
            return $this;
        }

        return new self($startingAt);
    }
}
