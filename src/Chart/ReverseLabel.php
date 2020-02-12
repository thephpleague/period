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

use function iterator_to_array;

final class ReverseLabel implements LabelGenerator
{
    /**
     * @var LabelGenerator
     */
    private $labelGenerator;

    /**
     * New instance.
     */
    public function __construct(LabelGenerator $labelGenerator)
    {
        $this->labelGenerator = $labelGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(int $nbLabels): \Iterator
    {
        $iterable = $this->labelGenerator->generate($nbLabels);
        $data = iterator_to_array($iterable, false);

        for (end($data); null !== ($key = key($data)); prev($data)) {
            yield $key => current($data);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function format(string $label): string
    {
        return $this->labelGenerator->format($label);
    }
}
