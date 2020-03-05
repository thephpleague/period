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

namespace LeagueTest\Period\Chart\Label;

use League\Period\Chart\Label\AffixLabel;
use League\Period\Chart\Label\DecimalNumber;
use League\Period\Chart\Label\LatinLetter;
use League\Period\Chart\Label\RomanNumber;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \League\Period\Chart\Label\AffixLabel;
 */
final class AffixLabelTest extends TestCase
{
    /**
     * @dataProvider providerLetter
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

        $generator = (new AffixLabel(new LatinLetter($letter)))->withPrefix($prefix)->withSuffix($suffix);
        self::assertSame($expected, iterator_to_array($generator->generate($nbLabels), false));
    }

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
                'expected' => ['.0.'],
            ],
            'labels starts at 0 (2)' => [
                'nbLabels' => 1,
                'letter' => '',
                'prefix' => '.'.PHP_EOL,
                'suffix' => PHP_EOL.'.',
                'expected' => ['.0.'],
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
        $generator = new \League\Period\Chart\Label\AffixLabel(new RomanNumber(new DecimalNumber(10)));
        self::assertSame('', $generator->suffix());
        self::assertSame('', $generator->prefix());
        $new = $generator->withPrefix('o')->withSuffix('');
        self::assertNotSame($new, $generator);
        self::assertSame('o', $new->prefix());
        self::assertSame('', $new->suffix());
    }

    public function testFormat(): void
    {
        $generator = new \League\Period\Chart\Label\AffixLabel(new RomanNumber(new DecimalNumber(10)), ':', '.');
        self::assertSame(':FOOBAR.', $generator->format('foobar'));
    }
}
