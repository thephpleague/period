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

/**
 * League.Period (https://period.thephpleague.com).
 *
 * (c) Ignace Nyamagana Butera <nyamsprod@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace League\Period;

use PHPUnit\Framework\Attributes\DataProvider;

final class PeriodBoundsTest extends PeriodTestCase
{
    #[DataProvider('provideBounds')]
    public function testPeriodBounds(
        Period $interval,
        Bounds $rangeType,
        bool $startIncluded,
        bool $startExcluded,
        bool $endIncluded,
        bool $endExcluded
    ): void {
        self::assertTrue($rangeType === $interval->bounds);
        self::assertSame($startIncluded, $interval->bounds->isStartIncluded());
        self::assertSame($startExcluded, !$interval->bounds->isStartIncluded());
        self::assertSame($endIncluded, $interval->bounds->isEndIncluded());
        self::assertSame($endExcluded, !$interval->bounds->isEndIncluded());
    }

    /**
     * @return array<string, array{interval:Period, rangeType:Bounds, startIncluded:bool, startExcluded:bool, endIncluded:bool, endExcluded:bool}>
     */
    public static function provideBounds(): array
    {
        return [
            'left open right close' => [
                'interval' => Period::fromDay(2012, 8, 12),
                'rangeType' => Bounds::IncludeStartExcludeEnd,
                'startIncluded' => true,
                'startExcluded' => false,
                'endIncluded' => false,
                'endExcluded' => true,
            ],
            'left close right open' => [
                'interval' => Period::around('2012-08-12', '1 HOUR', Bounds::ExcludeStartIncludeEnd),
                'rangeType' => Bounds::ExcludeStartIncludeEnd,
                'startIncluded' => false,
                'startExcluded' => true,
                'endIncluded' => true,
                'endExcluded' => false,
            ],
            'left open right open' => [
                'interval' => Period::after('2012-08-12', '1 DAY', Bounds::IncludeAll),
                'rangeType' => Bounds::IncludeAll,
                'startIncluded' => true,
                'startExcluded' => false,
                'endIncluded' => true,
                'endExcluded' => false,
            ],
            'left close right close' => [
                'interval' => Period::before('2012-08-12', '1 WEEK', Bounds::ExcludeAll),
                'rangeType' => Bounds::ExcludeAll,
                'startIncluded' => false,
                'startExcluded' => true,
                'endIncluded' => false,
                'endExcluded' => true,
            ],
        ];
    }

    public function testPeriodBoundedBy(): void
    {
        $interval = Period::fromDate('2014-01-13', '2014-01-20');
        $altInterval = $interval->boundedBy(Bounds::ExcludeAll);

        self::assertEquals($altInterval->dateInterval(), $interval->dateInterval());
        self::assertTrue($interval->bounds !== $altInterval->bounds);
        self::assertSame($interval, $interval->boundedBy(Bounds::IncludeStartExcludeEnd));
    }
}
