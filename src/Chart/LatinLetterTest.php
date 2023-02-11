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

final class LatinLetterTest extends TestCase
{
    public function testFailsToCreateNewInstanceWithEmptyString(): void
    {
        $this->expectException(UnableToDrawChart::class);

        new LatinLetter('');
    }

    public function testFailsToCreateNewInstanceWithInvalidString(): void
    {
        $this->expectException(UnableToDrawChart::class);

        new LatinLetter('F0obar');
    }

    /**
     * @param array<string> $expected
     */
    #[DataProvider('providerLetter')]
    public function testGetLabels(int $nbLabels, string $letter, array $expected): void
    {
        $generator = new LatinLetter($letter);
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
                'nbLabels' => 1,
                'letter' => 'i',
                'expected' => ['i'],
            ],
            'labels starts ends at ab' => [
                'nbLabels' => 2,
                'letter' => 'aa',
                'expected' => ['aa', 'ab'],
            ],
            'labels starts ends at z' => [
                'nbLabels' => 3,
                'letter' => 'z',
                'expected' => ['z', 'aa', 'ab'],
            ],
            'labels starts at 0 (1)' => [
                'nbLabels' => 1,
                'letter' => '   A     ',
                'expected' => ['A'],
            ],
            'labels starts at 0 (2)' => [
                'nbLabels' => 1,
                'letter' => 'A',
                'expected' => ['A'],
            ],
        ];
    }

    public function testFormat(): void
    {
        $generator = new LatinLetter('i');
        self::assertSame('foobar', $generator->format('foobar'));
    }
}
