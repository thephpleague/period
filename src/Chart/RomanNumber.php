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

    public function __construct(public readonly DecimalNumber $decimalNumber, public readonly Casing $case = Casing::UPPER)
    {
    }


    public function generate(int $nbLabels): Iterator
    {
        foreach ($this->decimalNumber->generate($nbLabels) as $key => $label) {
            yield $key => $this->convert($label);
        }
    }

    public function format(string $label): string
    {
        if (Casing::UPPER === $this->case) {
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

        if (Casing::LOWER === $this->case) {
            return strtolower($retVal);
        }

        return $retVal;
    }

    /**
     * Tells whether the roman letter is upper cased.
     */
    public function isUpper(): bool
    {
        return Casing::UPPER === $this->case;
    }

    /**
     * Return an instance with the starting Letter.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the starting Letter.
     */
    public function startsWith(int $startingAt): self
    {
        $labelGenerator = $this->decimalNumber->startsWith($startingAt);
        if ($labelGenerator === $this->decimalNumber) {
            return $this;
        }

        return new self($labelGenerator, $this->case);
    }

    /**
     * Return an instance with the new letter case setting.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the letter case setting.
     */
    public function case(Casing $case): self
    {
        if ($case === $this->case) {
            return $this;
        }

        return new self($this->decimalNumber, $case);
    }
}
