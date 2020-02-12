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

use function in_array;
use function strtolower;
use function strtoupper;

final class RomanNumber implements LabelGenerator
{
    public const UPPER = 1;
    public const LOWER = 2;
    private const CHARACTER_MAP = [
        'M'  => 1000, 'CM' => 900,  'D' => 500,
        'CD' => 400,   'C' => 100, 'XC' => 90,
        'L'  => 50,   'XL' => 40,   'X' => 10,
        'IX' => 9,     'V' => 5,   'IV' => 4,
        'I'  => 1,
    ];

    /**
     * @var DecimalNumber
     */
    private $decimalNumber;

    /**
     * @var int
     */
    private $case;

    /**
     * New instance.
     */
    public function __construct(DecimalNumber $decimalNumber, int $case = self::UPPER)
    {
        $this->decimalNumber = $decimalNumber;
        $this->case = $this->filterLetterCase($case);
    }

    /**
     * filter letter case state.
     */
    private function filterLetterCase(int $case): int
    {
        if (!in_array($case, [self::UPPER, self::LOWER], true)) {
            return self::UPPER;
        }

        return $case;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(int $nbLabels): \Iterator
    {
        foreach ($this->decimalNumber->generate($nbLabels) as $key => $label) {
            yield $key => $this->convert($label);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function format(string $label): string
    {
        if (self::UPPER === $this->case) {
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
        $retVal = '';
        while ($number > 0) {
            foreach (self::CHARACTER_MAP as $roman => $int) {
                if ($number >= $int) {
                    $number -= $int;
                    $retVal .= $roman;
                    break;
                }
            }
        }

        if (self::LOWER === $this->case) {
            return strtolower($retVal);
        }

        return $retVal;
    }

    /**
     * Returns the starting Letter.
     */
    public function startingAt(): int
    {
        return $this->decimalNumber->startingAt();
    }

    /**
     * Tells whether the roman letter is upper cased.
     */
    public function isUpper(): bool
    {
        return self::UPPER === $this->case;
    }

    /**
     * Tells whether the roman letter is lower cased.
     */
    public function isLower(): bool
    {
        return self::LOWER === $this->case;
    }

    /**
     * Return an instance with the starting Letter.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the starting Letter.
     */
    public function startsWith(int $int): self
    {
        $labelGenerator = $this->decimalNumber->startsWith($int);
        if ($labelGenerator === $this->decimalNumber) {
            return $this;
        }

        $clone = clone $this;
        $clone->decimalNumber = $labelGenerator;

        return $clone;
    }

    /**
     * Return an instance with the new letter case setting.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the letter case setting.
     */
    public function withLetterCase(int $case): self
    {
        $case = $this->filterLetterCase($case);
        if ($case === $this->case) {
            return $this;
        }

        $clone = clone $this;
        $clone->case = $case;

        return $clone;
    }
}
