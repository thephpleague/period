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
use function strtolower;
use function strtoupper;

/**
 * A class to attach a roman number to the generated label.
 *
 * @see LabelGenerator
 */
final class RomanNumber implements LabelGenerator
{
    private const CHARACTER_MAP = [
        'M'  => 1000, 'CM' => 900,  'D' => 500,
        'CD' => 400,   'C' => 100, 'XC' => 90,
        'L'  => 50,   'XL' => 40,   'X' => 10,
        'IX' => 9,     'V' => 5,   'IV' => 4,
        'I'  => 1,
    ];

    public function __construct(
        public readonly DecimalNumber $decimalNumber,
        public readonly LetterCase $letterCase
    ) {
        if ($this->decimalNumber->startLabel < 1) {
            throw UnableToDrawChart::dueToInvalidLabel($this->decimalNumber->startLabel, $this);
        }
    }

    public function generate(int $nbLabels): Iterator
    {
        foreach ($this->decimalNumber->generate($nbLabels) as $key => $label) {
            yield $key => $this->convert($label);
        }
    }

    public function format(string $label): string
    {
        if (LetterCase::Upper === $this->letterCase) {
            return strtoupper($label);
        }

        return strtolower($label);
    }

    /**
     * Convert a integer number into its roman representation.
     *
     * @see https://stackoverflow.com/a/15023547
     */
    private function convert(string $number): string
    {
        $numberInt = (int) $number;
        $retVal = '';
        while ($numberInt > 0) {
            foreach (self::CHARACTER_MAP as $roman => $int) {
                if ($numberInt >= $int) {
                    $numberInt -= $int;
                    $retVal .= $roman;
                    break;
                }
            }
        }

        if (LetterCase::Lower === $this->letterCase) {
            return strtolower($retVal);
        }

        return $retVal;
    }
}
