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

use function preg_match;
use function trim;

final class LatinLetter implements LabelGenerator
{
    /**
     * @var string
     */
    private $str;

    /**
     * New instance.
     */
    public function __construct(string $str = 'A')
    {
        $this->str = $this->filterLetter($str);
    }

    public function filterLetter(string $str): string
    {
        $str = trim($str);
        if ('' === $str) {
            return '0';
        }

        if (1 !== preg_match('/^[A-Za-z]+$/', $str)) {
            return 'A';
        }

        return $str;
    }

    /**
     * {@inheritdoc}
     */
    public function format(string $label): string
    {
        return $label;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(int $nbLabels): \Iterator
    {
        if (0 >= $nbLabels) {
            $nbLabels = 0;
        }

        $count = 0;
        $letter = $this->str;
        while ($count < $nbLabels) {
            yield $count => $letter++;

            ++$count;
        }
    }

    /**
     * Returns the starting Letter.
     */
    public function startingAt(): string
    {
        return $this->str;
    }

    /**
     * Return an instance with the starting Letter.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the starting Letter.
     */
    public function startsWith(string $str): self
    {
        $str = $this->filterLetter($str);
        if ($str === $this->str) {
            return $this;
        }

        $clone = clone $this;
        $clone->str = $str;

        return $clone;
    }
}
