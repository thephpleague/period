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

namespace LeagueTest\Period\Chart;

use League\Period\Chart\LatinLetter;
use League\Period\Chart\ReverseLabel;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \League\Period\Chart\ReverseLabel;
 */
final class ReverseLabelTest extends TestCase
{
    /**
     * @dataProvider providerLetter
     */
    public function testGetLabels(int $nbLabels, string $letter, array $expected): void
    {
        $generator = new \League\Period\Chart\ReverseLabel(new \League\Period\Chart\LatinLetter($letter));
        self::assertSame($expected, iterator_to_array($generator->generate($nbLabels), false));
    }

    public function providerLetter(): iterable
    {
        return [
            'empty labels' => [
                'nbLabels' => 0,
                'letter' => 'i',
                'expected' => [],
            ],
            'labels starts at i' => [
                'nbLabels' => 2,
                'letter' => 'i',
                'expected' => ['j', 'i'],
            ],
            'labels starts ends at ab' => [
                'nbLabels' => 2,
                'letter' => 'aa',
                'expected' => ['ab', 'aa'],
            ],
        ];
    }

    public function testFormat(): void
    {
        $generator = new ReverseLabel(new LatinLetter('AA'));
        self::assertSame('', $generator->format(''));
    }
}
