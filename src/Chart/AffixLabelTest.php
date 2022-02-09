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
 * @coversDefaultClass \League\Period\Chart\AffixLabel;
 */
final class AffixLabelTest extends TestCase
{
    /**
     * @dataProvider providerLetter
     *
     * @param array<string> $expected
     */
    public function testGetLabels(
        int $nbLabels,
        string $letter,
        string $prefix,
        string $suffix,
        array $expected
    ): void {
        $generator = new AffixLabel(new LatinLetter($letter), $prefix, $suffix);
        self::assertSame($expected, iterator_to_array($generator->generate($nbLabels), false));

        $generator = (new AffixLabel(new LatinLetter($letter)))->prefix($prefix)->suffix($suffix);
        self::assertSame($expected, iterator_to_array($generator->generate($nbLabels), false));
    }

    /**
     * @return array<string, array{nbLabels:int, letter:string, prefix:string, suffix:string, expected:array<int, string>}>
     */
    public function providerLetter(): iterable
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
                'letter' => '        ',
                'prefix' => '.',
                'suffix' => '.',
                'expected' => ['.A.'],
            ],
            'labels starts at 0 (2)' => [
                'nbLabels' => 1,
                'letter' => '',
                'prefix' => '.'.PHP_EOL,
                'suffix' => PHP_EOL.'.',
                'expected' => ['.A.'],
            ],
            'labels with an integer' => [
                'nbLabels' => 1,
                'letter' => '1',
                'prefix' => '.'.PHP_EOL,
                'suffix' => PHP_EOL,
                'expected' => ['.A'],
            ],
        ];
    }

    public function testGetter(): void
    {
        $generator = new AffixLabel(new RomanNumber(new DecimalNumber(10), LetterCase::UPPER));
        self::assertSame('', $generator->suffix);
        self::assertSame('', $generator->prefix);
        $new = $generator->prefix('o')->suffix('');
        self::assertNotSame($new, $generator);
        self::assertSame('o', $new->prefix);
        self::assertSame('', $new->suffix);
    }

    public function testFormat(): void
    {
        $generator = new AffixLabel(new RomanNumber(new DecimalNumber(10), LetterCase::UPPER), ':', '.');
        self::assertSame(':FOOBAR.', $generator->format('foobar'));
    }
}
