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

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class RomanNumberTest extends TestCase
{
    /**
     * @param array<string> $expected
     */
    #[DataProvider('providerLetter')]
    public function testGetLabels(int $nbLabels, int $label, LetterCase $lettercase, array $expected, bool $isUpper): void
    {
        $generator = new RomanNumber(new DecimalNumber($label), $lettercase);
        self::assertSame($expected, iterator_to_array($generator->generate($nbLabels), false));
        self::assertSame($isUpper, $lettercase->isUpper());
    }

    /**
     * @return iterable<string, array{nbLabels:int, label:int, lettercase:LetterCase, expected:array<string>}>
     */
    public static function providerLetter(): iterable
    {
        return [
            'empty labels' => [
                'nbLabels' => 0,
                'label' => 1,
                'lettercase' => LetterCase::Upper,
                'expected' => [],
                'isUpper' => true,
            ],
            'labels starts at 3' => [
                'nbLabels' => 1,
                'label' => 3,
                'lettercase' => LetterCase::Upper,
                'expected' => ['III'],
                'isUpper' => true,
            ],
            'labels starts ends at 4' => [
                'nbLabels' => 2,
                'label' => 4,
                'lettercase' => LetterCase::Lower,
                'expected' => ['iv', 'v'],
                'isUpper' => false,
            ],
        ];
    }

    public function testFailsToCreateRomanLabelGenerator(): void
    {
        $this->expectException(UnableToDrawChart::class);

        new RomanNumber(new DecimalNumber(0), LetterCase::Lower);
    }

    public function testFormat(): void
    {
        $upperRoman = new RomanNumber(new DecimalNumber(10), LetterCase::Upper);
        $lowerRoman = new RomanNumber(new DecimalNumber(10), LetterCase::Lower);

        self::assertSame('FOOBAR', $upperRoman->format('fOoBaR'));
        self::assertSame('foobar', $lowerRoman->format('fOoBaR'));
    }
}
