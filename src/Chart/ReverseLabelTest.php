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

final class ReverseLabelTest extends TestCase
{
    /**
     * @param array<string> $expected
     */
    #[DataProvider('providerLetter')]
    public function testGetLabels(int $nbLabels, string $letter, array $expected): void
    {
        $generator = new ReverseLabel(new LatinLetter($letter));
        self::assertSame($expected, iterator_to_array($generator->generate($nbLabels), false));
    }

    /**
     * @return iterable<string, array{nbLabels:int, letter:string, expected:array<string>}>
     */
    public static function providerLetter(): iterable
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
