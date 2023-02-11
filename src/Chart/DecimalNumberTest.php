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

final class DecimalNumberTest extends TestCase
{
    /**
     * @param array<string> $expected
     */
    #[DataProvider('providerLetter')]
    public function testGetLabels(int $nbLabels, int $label, array $expected): void
    {
        $generator = new DecimalNumber($label);
        self::assertSame($expected, iterator_to_array($generator->generate($nbLabels), false));
    }

    /**
     * @return iterable<string, array{nbLabels:int, label:int, expected:array<string>}>
     */
    public static function providerLetter(): iterable
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
                'expected' => ['-1'],
            ],
            'labels starts at 0 (2)' => [
                'nbLabels' => 1,
                'label' => 0,
                'expected' => ['0'],
            ],
        ];
    }

    public function testFormat(): void
    {
        $generator = new DecimalNumber(42);
        self::assertSame('', $generator->format(''));
    }
}
