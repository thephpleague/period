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

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \League\Period\Chart\RomanNumber;
 */
final class RomanNumberTest extends TestCase
{
    /**
     * @dataProvider providerLetter
     */
    public function testGetLabels(int $nbLabels, int $label, int $lettercase, array $expected): void
    {
        $generator = new RomanNumber(new DecimalNumber($label), $lettercase);
        self::assertSame($expected, iterator_to_array($generator->generate($nbLabels), false));
    }

    /**
     * @return iterable<string, array{nbLabels:int, label:int, lettercase:int, expected:array<string>}>
     */
    public function providerLetter(): iterable
    {
        return [
            'empty labels' => [
                'nbLabels' => 0,
                'label' => 1,
                'lettercase' => RomanNumber::UPPER,
                'expected' => [],
            ],
            'labels starts at 3' => [
                'nbLabels' => 1,
                'label' => 3,
                'lettercase' => 42,
                'expected' => ['III'],
            ],
            'labels starts ends at 4' => [
                'nbLabels' => 2,
                'label' => 4,
                'lettercase' => RomanNumber::UPPER,
                'expected' => ['IV', 'V'],
            ],
            'labels starts at 0 (1)' => [
                'nbLabels' => 1,
                'label' => -1,
                'lettercase' => RomanNumber::LOWER,
                'expected' => ['i'],
            ],
            'labels starts at 0 (2)' => [
                'nbLabels' => 1,
                'label' => 0,
                'lettercase' => RomanNumber::LOWER,
                'expected' => ['i'],
            ],
        ];
    }

    public function testStartWith(): void
    {
        $generator = new RomanNumber(new DecimalNumber(42));
        self::assertSame(42, $generator->decimalNumber->startingAt);
        $new = $generator->startsWith(69);
        self::assertNotSame($new, $generator);
        self::assertSame(69, $new->decimalNumber->startingAt);
        self::assertSame($generator, $generator->startsWith(42));
        self::assertSame(1, (new DecimalNumber(-3))->startingAt);
        self::assertSame(1, $generator->startsWith(-3)->decimalNumber->startingAt);
    }

    public function testLetterCase(): void
    {
        $generator = new RomanNumber(new DecimalNumber(1));
        self::assertTrue($generator->isUpper());
        $new = $generator->withLetterCase(RomanNumber::LOWER);
        self::assertFalse($new->isUpper());
        $alt = $new->withLetterCase(RomanNumber::LOWER);
        self::assertSame($alt, $new);
    }

    public function testFormat(): void
    {
        $generator = new RomanNumber(new DecimalNumber(10));
        $newGenerator = $generator->withLetterCase(RomanNumber::LOWER);
        self::assertSame('FOOBAR', $generator->format('fOoBaR'));
        self::assertSame('foobar', $newGenerator->format('fOoBaR'));
    }
}
