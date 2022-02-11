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
    public function __construct(public readonly int $startLabel)
    {
    }

    public function format(string $label): string
    {
        return $label;
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
}
