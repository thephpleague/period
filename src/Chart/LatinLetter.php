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
use function array_pop;
use function chr;
use function implode;
use function preg_match;
use function str_split;
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

    private function filterLetter(string $str): string
    {
        $str = trim($str);
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
     * Increments ASCII Letters like numbers in PHP.
     *
     * @see https://stackoverflow.com/questions/3567180/how-to-increment-letters-like-numbers-in-php/3567218
     */
    private static function increment(string $previous): string
    {
        static $asciiUpperCaseBounds = ['start' => 65, 'end' => 91];
        static $asciiLowerCaseBounds = ['start' => 97, 'end' => 123];

        $increase = true;
        $letters = str_split($previous);
        $nextLetters = [];

        while ([] !== $letters) {
            $nextLetter = array_pop($letters);

            if ($increase) {
                $letterAscii = ord($nextLetter) + 1;

                [$nextLetterAscii, $increase] = match ($letterAscii) {
                    $asciiUpperCaseBounds['end'] => [$asciiUpperCaseBounds['start'], true],
                    $asciiLowerCaseBounds['end'] => [$asciiLowerCaseBounds['start'], true],
                    default => [$letterAscii, false],
                };

                $nextLetter = chr($nextLetterAscii);
                if ($increase && [] === $letters) {
                    $nextLetter .= $nextLetter;
                }
            }

            $nextLetters = [$nextLetter, ...$nextLetters];
        }

        return implode('', $nextLetters);
    }
}
