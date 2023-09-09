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

use function array_reverse;
use function iterator_to_array;

/**
 * A class to revert the order of the generated labels.
 *
 * @see LabelGenerator
 */
final class ReverseLabel implements LabelGenerator
{
    public function __construct(public readonly LabelGenerator $labelGenerator)
    {
    }

    public function generate(int $nbLabels): Iterator
    {
        $data = iterator_to_array($this->labelGenerator->generate($nbLabels), false);

        foreach (array_reverse($data, false) as $offset => $value) {
            yield $offset => $value;
        }
    }

    public function format(string $label): string
    {
        return $this->labelGenerator->format($label);
    }
}
