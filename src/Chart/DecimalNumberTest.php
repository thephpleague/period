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
 * @coversDefaultClass \League\Period\Chart\DecimalNumber;
 */
final class DecimalNumberTest extends TestCase
{
    /**
     * @dataProvider providerLetter
     *
     * @param array<string> $expected
     */
    public function testGetLabels(int $nbLabels, int $label, array $expected): void
    {
        $generator = new DecimalNumber($label);
        self::assertSame($expected, iterator_to_array($generator->generate($nbLabels), false));
    }

    /**
     * @return iterable<string, array{nbLabels:int, label:int, expected:array<string>}>
     */
    public function providerLetter(): iterable
    {
        return [
            'empty labels' => [
                'nbLabels' => 0,
                'label' => 1,
                'expected' => [],
            ],
            'labels starts at 3' => [
                'nbLabels' => 1,
                'label' => 3,
                'expected' => ['3'],
            ],
            'labels starts ends at 4' => [
                'nbLabels' => 2,
                'label' => 4,
                'expected' => ['4', '5'],
            ],
            'labels starts at 0 (1)' => [
                'nbLabels' => 1,
                'label' => -1,
                'expected' => ['1'],
            ],
            'labels starts at 0 (2)' => [
                'nbLabels' => 1,
                'label' => 0,
                'expected' => ['1'],
            ],
        ];
    }

    public function testStartWith(): void
    {
        $generator = new DecimalNumber(42);
        self::assertSame(42, $generator->startLabel);
        $new = $generator->startingOn(69);
        self::assertNotSame($new, $generator);
        self::assertSame(69, $new->startLabel);
        self::assertSame($generator, $generator->startingOn(42));
        self::assertSame(1, (new DecimalNumber(-3))->startLabel);
        self::assertSame(1, $generator->startingOn(-3)->startLabel);
    }

    public function testFormat(): void
    {
        $generator = new DecimalNumber(42);
        self::assertSame('', $generator->format(''));
    }
}
