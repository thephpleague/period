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
     *
     * @param array<string> $expected
     */
    public function testGetLabels(int $nbLabels, int $label, LetterCase $lettercase, array $expected): void
    {
        $generator = new RomanNumber(new DecimalNumber($label), $lettercase);
        self::assertSame($expected, iterator_to_array($generator->generate($nbLabels), false));
    }

    /**
     * @return iterable<string, array{nbLabels:int, label:int, lettercase:LetterCase, expected:array<string>}>
     */
    public function providerLetter(): iterable
    {
        return [
            'empty labels' => [
                'nbLabels' => 0,
                'label' => 1,
                'lettercase' => LetterCase::UPPER,
                'expected' => [],
            ],
            'labels starts at 3' => [
                'nbLabels' => 1,
                'label' => 3,
                'lettercase' => LetterCase::UPPER,
                'expected' => ['III'],
            ],
            'labels starts ends at 4' => [
                'nbLabels' => 2,
                'label' => 4,
                'lettercase' => LetterCase::UPPER,
                'expected' => ['IV', 'V'],
            ],
            'labels starts at 0 (1)' => [
                'nbLabels' => 1,
                'label' => -1,
                'lettercase' => LetterCase::LOWER,
                'expected' => ['i'],
            ],
            'labels starts at 0 (2)' => [
                'nbLabels' => 1,
                'label' => 0,
                'lettercase' => LetterCase::LOWER,
                'expected' => ['i'],
            ],
        ];
    }

    public function testFormat(): void
    {
        $upperRoman = new RomanNumber(new DecimalNumber(10), LetterCase::UPPER);
        $lowerRoman = new RomanNumber(new DecimalNumber(10), LetterCase::LOWER);

        self::assertSame('FOOBAR', $upperRoman->format('fOoBaR'));
        self::assertSame('foobar', $lowerRoman->format('fOoBaR'));
    }
}
