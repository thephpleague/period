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
        $iterable = $this->labelGenerator->generate($nbLabels);
        $data = iterator_to_array($iterable, false);

        for (end($data); null !== ($key = key($data)); prev($data)) {
            yield $key => (string) current($data);
        }
    }

    public function format(string $label): string
    {
        return $this->labelGenerator->format($label);
    }
}
