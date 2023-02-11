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

final class AffixLabelTest extends TestCase
{
    /**
     * @param array<string> $expected
     */
    #[DataProvider('providerLetter')]
    public function testGetLabels(
        int $nbLabels,
        string $letter,
        string $prefix,
        string $suffix,
        array $expected
    ): void {
        $generator = new AffixLabel(new LatinLetter($letter), $prefix, $suffix);
        self::assertSame($expected, iterator_to_array($generator->generate($nbLabels), false));
    }

    /**
     * @return array<string, array{nbLabels:int, letter:string, prefix:string, suffix:string, expected:array<int, string>}>
     */
    public static function providerLetter(): iterable
    {
        return [
            'empty labels' => [
                'nbLabels' => 0,
                'letter' => 'i',
                'prefix' => '',
                'suffix' => '',
                'expected' => [],
            ],
            'labels starts at i' => [
                'nbLabels' => 1,
                'letter' => 'i',
                'prefix' => '',
                'suffix' => '.',
                'expected' => ['i.'],
            ],
            'labels starts ends at ab' => [
                'nbLabels' => 2,
                'letter' => 'aa',
                'prefix' => '-',
                'suffix' => '',
                'expected' => ['-aa', '-ab'],
            ],
            'labels starts at 0 (1)' => [
                'nbLabels' => 1,
                'letter' => '   A     ',
                'prefix' => '.',
                'suffix' => '.',
                'expected' => ['.A.'],
            ],
        ];
    }

    public function testFailsToInstantiateNewInstanceWithCarriageReturnCharacter(): void
    {
        $this->expectException(UnableToDrawChart::class);

        new AffixLabel(labelGenerator: new LatinLetter('foobar'), labelPrefix: 'toto', labelSuffix: 'toto'.PHP_EOL);
    }

    public function testGetter(): void
    {
        $generator = new AffixLabel(new RomanNumber(new DecimalNumber(10), LetterCase::Upper));
        self::assertSame('', $generator->labelSuffix);
        self::assertSame('', $generator->labelPrefix);
    }

    public function testFormat(): void
    {
        $generator = new AffixLabel(new RomanNumber(new DecimalNumber(10), LetterCase::Upper), ':', '.');
        self::assertSame(':FOOBAR.', $generator->format('foobar'));
    }
}
