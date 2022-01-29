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
use function preg_match;
use function trim;

/**
 * A class to attach a latin letter to the generated label.
 *
 * @see LabelGenerator
 */
final class LatinLetter implements LabelGenerator
{
    public readonly string $startingAt;

    public function __construct(string $startingAt = 'A')
    {
        $this->startingAt = $this->filterLetter($startingAt);
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


    public function format(string $label): string
    {
        return $label;
    }

    public function generate(int $nbLabels): Iterator
    {
        if (0 >= $nbLabels) {
            $nbLabels = 0;
        }

        $count = 0;
        $letter = $this->startingAt;
        while ($count < $nbLabels) {
            yield $count => $letter++;

            ++$count;
        }
    }

    /**
     * Return an instance with the starting Letter.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the starting Letter.
     */
    public function startsWith(string $startingAt): self
    {
        $startingAt = $this->filterLetter($startingAt);
        if ($startingAt === $this->startingAt) {
            return $this;
        }

        return new self($startingAt);
    }
}
