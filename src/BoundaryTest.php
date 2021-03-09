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
final class BoundaryTest extends TestCase
{
    /**
     * @var string
     */
    private $timezone;

    public function setUp(): void
    {
        $this->timezone = date_default_timezone_get();
    }

    public function tearDown(): void
    {
        date_default_timezone_set($this->timezone);
    }

    /**
     * @dataProvider providerGetRangType
     */
    public function testGetRangeType(
        Period $interval,
        string $rangeType,
        bool $startIncluded,
        bool $startExcluded,
        bool $endIncluded,
        bool $endExcluded
    ): void {
        self::assertSame($rangeType, $interval->getBoundaryType());
        self::assertSame($startIncluded, $interval->isStartIncluded());
        self::assertSame($startExcluded, $interval->isStartExcluded());
        self::assertSame($endIncluded, $interval->isEndIncluded());
        self::assertSame($endExcluded, $interval->isEndExcluded());
    }

    public function providerGetRangType(): array
    {
        return [
            'left open right close' => [
                'interval' => Period::fromDay(2012, 8, 12),
                'rangeType' => Period::INCLUDE_START_EXCLUDE_END,
                'startIncluded' => true,
                'startExcluded' => false,
                'endIncluded' => false,
                'endExcluded' => true,
            ],
            'left close right open' => [
                'interval' => Period::around('2012-08-12', '1 HOUR', Period::EXCLUDE_START_INCLUDE_END),
                'rangeType' => Period::EXCLUDE_START_INCLUDE_END,
                'startIncluded' => false,
                'startExcluded' => true,
                'endIncluded' => true,
                'endExcluded' => false,
            ],
            'left open right open' => [
                'interval' => Period::after('2012-08-12', '1 DAY', Period::INCLUDE_ALL),
                'rangeType' => Period::INCLUDE_ALL,
                'startIncluded' => true,
                'startExcluded' => false,
                'endIncluded' => true,
                'endExcluded' => false,
            ],
            'left close right close' => [
                'interval' => Period::before('2012-08-12', '1 WEEK', Period::EXCLUDE_ALL),
                'rangeType' => Period::EXCLUDE_ALL,
                'startIncluded' => false,
                'startExcluded' => true,
                'endIncluded' => false,
                'endExcluded' => true,
            ],
        ];
    }

    public function testWithBoundaryType(): void
    {
        $interval = Period::fromDatepoint(new DateTime('2014-01-13'), new DateTime('2014-01-20'));
        $altInterval = $interval->withBoundaryType(Period::EXCLUDE_ALL);
        self::assertEquals($interval->getDateInterval(), $interval->getDateInterval());
        self::assertNotEquals($interval->getBoundaryType(), $altInterval->getBoundaryType());
        self::assertSame($interval, $interval->withBoundaryType(Period::INCLUDE_START_EXCLUDE_END));
    }

    public function testWithBoundaryTypeFails(): void
    {
        $this->expectException(Exception::class);
        $interval = Period::fromDatepoint(new DateTime('2014-01-13'), new DateTime('2014-01-20'));
        $interval->withBoundaryType('foobar');
    }
}
