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
            yield $count => $letter;

            $letter = self::increment($letter);

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

    /**
     * Increments Letters like numbers in PHP.
     *
     * @see https://stackoverflow.com/questions/3567180/how-to-increment-letters-like-numbers-in-php/3567218
     */
    private static function increment(string $previous): string
    {
        static $asciiUpperCaseInterval = [65, 91];
        static $asciiLowerCaseInterval = [97, 123];

        $next = '';
        $increase = true;
        $letters = str_split($previous);

        while ([] !== $letters) {
            $letter = array_pop($letters);

            if ($increase) {
                $letterAscii = ord($letter) + 1;

                [$letterAscii, $increase] = match ($letterAscii) {
                    $asciiUpperCaseInterval[1] => [$asciiUpperCaseInterval[0], true],
                    $asciiLowerCaseInterval[1] => [$asciiLowerCaseInterval[0], true],
                    default => [$letterAscii, false],
                };

                $letter = chr($letterAscii);
                if ($increase && [] === $letters) {
                    $letter .= $letter;
                }
            }

            $next = $letter.$next;
        }

        return $next;
    }
}
