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
    private const ASCII_UPPERCASE_END = 91;
    private const ASCII_LOWERCASE_END = 123;
    private const ASCII_UPPERCASE_START = 65;
    private const ASCII_LOWERCASE_START = 97;

    public readonly string $startLabel;

    public function __construct(string $startLabel)
    {
        $this->startLabel = $this->filterLabel($startLabel);
    }

    private function filterLabel(string $str): string
    {
        $label = trim($str);

        return match (1) {
            preg_match('/^[A-Za-z]+$/', $label) => $label,
            default => throw UnableToDrawChart::dueToInvalidLabel($str, $this),
        };
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
        $label = $this->startLabel;
        while ($count < $nbLabels) {
            yield $count => $label;

            $label = self::increment($label);

            ++$count;
        }
    }

    /**
     * Increments ASCII letters like numbers in PHP.
     *
     * @see https://stackoverflow.com/questions/3567180/how-to-increment-letters-like-numbers-in-php/3567218
     */
    private static function increment(string $current): string
    {
        assert((bool)preg_match('/\A[A-Za-z]+\z/', $current));

        $increase = true;
        $letters = str_split($current);
        $nextLetters = [];

        while ([] !== $letters) {
            $nextLetter = array_pop($letters);

            if ($increase) {
                $letterAscii = ord($nextLetter) + 1;

                [$nextLetterAscii, $increase] = match ($letterAscii) {
                    self::ASCII_UPPERCASE_END => [self::ASCII_UPPERCASE_START, true],
                    self::ASCII_LOWERCASE_END => [self::ASCII_LOWERCASE_START, true],
                    default => [$letterAscii, false],
                };

                assert($nextLetterAscii <= 255);

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
