<?php

/**
 * League.Period (https://period.thephpleague.com)
 *
 * (c) Ignace Nyamagana Butera <nyamsprod@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace League\Period;

use DateTime;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \League\Period\Period
 */
final class PeriodBoundsTest extends TestCase
{
    private string $timezone;

    protected function setUp(): void
    {
        $this->timezone = date_default_timezone_get();
    }

    protected function tearDown(): void
    {
        date_default_timezone_set($this->timezone);
    }

    /**
     * @dataProvider providerGetRangType
     */
    public function testGetRangeType(
        Period $interval,
        Bounds $rangeType,
        bool $startIncluded,
        bool $startExcluded,
        bool $endIncluded,
        bool $endExcluded
    ): void {
        self::assertTrue($rangeType === $interval->bounds());
        self::assertSame($startIncluded, $interval->bounds()->isLowerIncluded());
        self::assertSame($startExcluded, !$interval->bounds()->isLowerIncluded());
        self::assertSame($endIncluded, $interval->bounds()->isUpperIncluded());
        self::assertSame($endExcluded, !$interval->bounds()->isUpperIncluded());
    }

    /**
     * @return array<string, array{interval:Period, rangeType:Bounds, startIncluded:bool, startExcluded:bool, endIncluded:bool, endExcluded:bool}>
     */
    public function providerGetRangType(): array
    {
        return [
            'left open right close' => [
                'interval' => Period::fromDay(2012, 8, 12),
                'rangeType' => Bounds::INCLUDE_LOWER_EXCLUDE_UPPER,
                'startIncluded' => true,
                'startExcluded' => false,
                'endIncluded' => false,
                'endExcluded' => true,
            ],
            'left close right open' => [
                'interval' => Period::around(new DateTime('2012-08-12'), Duration::fromDateString('1 HOUR'), Bounds::EXCLUDE_LOWER_INCLUDE_UPPER),
                'rangeType' => Bounds::EXCLUDE_LOWER_INCLUDE_UPPER,
                'startIncluded' => false,
                'startExcluded' => true,
                'endIncluded' => true,
                'endExcluded' => false,
            ],
            'left open right open' => [
                'interval' => Period::after(new DateTime('2012-08-12'), Duration::fromDateString('1 DAY'), Bounds::INCLUDE_ALL),
                'rangeType' => Bounds::INCLUDE_ALL,
                'startIncluded' => true,
                'startExcluded' => false,
                'endIncluded' => true,
                'endExcluded' => false,
            ],
            'left close right close' => [
                'interval' => Period::before(new DateTime('2012-08-12'), Duration::fromDateString('1 WEEK'), Bounds::EXCLUDE_ALL),
                'rangeType' => Bounds::EXCLUDE_ALL,
                'startIncluded' => false,
                'startExcluded' => true,
                'endIncluded' => false,
                'endExcluded' => true,
            ],
        ];
    }

    public function testWithBoundaryType(): void
    {
        $interval = Period::fromDate(new DateTime('2014-01-13'), new DateTime('2014-01-20'));
        $altInterval = $interval->withBounds(Bounds::EXCLUDE_ALL);
        self::assertEquals($interval->dateInterval(), $interval->dateInterval());
        self::assertFalse($interval->bounds() === $altInterval->bounds());
        self::assertSame($interval, $interval->withBounds(Bounds::INCLUDE_LOWER_EXCLUDE_UPPER));
    }
}
