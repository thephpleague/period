<?php

/**
 * League.Period (https://period.thephpleague.com)
 *
 * (c) Ignace Nyamagana Butera <nyamsprod@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LeagueTest\Period\Period;

use DateInterval;
use DatePeriod;
use DateTime;
use DateTimeImmutable;
use Generator;
use League\Period\Period;
use LeagueTest\Period\TestCase;

/**
 * @coversDefaultClass \League\Period\Period
 */
class DurationRelationTest extends TestCase
{
    public function testGetDateInterval(): void
    {
        $interval = new Period(new DateTimeImmutable('2012-02-01'), new DateTimeImmutable('2012-02-02'));
        self::assertSame(1, $interval->getDateInterval()->days);
    }

    public function testGetTimestampInterval(): void
    {
        $interval = new Period(new DateTimeImmutable('2012-02-01'), new DateTimeImmutable('2012-02-02'));
        self::assertSame(86400.0, $interval->getTimestampInterval());
    }

    /**
     * @dataProvider providerGetDatePeriod
     *
     * @param DateInterval|int|string $interval
     */
    public function testGetDatePeriod($interval, int $option, int $count): void
    {
        $period = new Period(new DateTime('2012-01-12'), new DateTime('2012-01-13'));
        $range = $period->getDatePeriod($interval, $option);
        self::assertCount($count, iterator_to_array($range));
    }

    public function providerGetDatePeriod(): array
    {
        return [
            'useDateInterval' => [new DateInterval('PT1H'), 0, 24],
            'useString' => ['2 HOUR', 0, 12],
            'useInt' => [9600, 0, 9],
            'useFloat' => [14400.0, 0, 6],
            'exclude start date useDateInterval' => [new DateInterval('PT1H'), DatePeriod::EXCLUDE_START_DATE, 23],
            'exclude start date useString' => ['2 HOUR', DatePeriod::EXCLUDE_START_DATE, 11],
            'exclude start date useInt' => [9600, DatePeriod::EXCLUDE_START_DATE, 8],
            'exclude start date useFloat' => [14400.0, DatePeriod::EXCLUDE_START_DATE, 5],
        ];
    }

    /**
     * @dataProvider providerGetDatePeriodBackwards
     *
     * @param DateInterval|int|string $interval
     */
    public function testGetDatePeriodBackwards($interval, int $option, int $count): void
    {
        $period = new Period(new DateTime('2012-01-12'), new DateTime('2012-01-13'));
        $range = $period->getDatePeriodBackwards($interval, $option);
        self::assertInstanceOf(Generator::class, $range);
        self::assertCount($count, iterator_to_array($range));
    }

    public function providerGetDatePeriodBackwards(): array
    {
        return [
            'useDateInterval' => [new DateInterval('PT1H'), 0, 24],
            'useString' => ['2 HOUR', 0, 12],
            'useInt' => [9600, 0, 9],
            'useFloat' => [14400.0, 0, 6],
            'exclude start date useDateInterval' => [new DateInterval('PT1H'), DatePeriod::EXCLUDE_START_DATE, 23],
            'exclude start date useString' => ['2 HOUR', DatePeriod::EXCLUDE_START_DATE, 11],
            'exclude start date useInt' => [9600, DatePeriod::EXCLUDE_START_DATE, 8],
            'exclude start date useFloat' => [14400.0, DatePeriod::EXCLUDE_START_DATE, 5],
        ];
    }
    /**
     * @dataProvider durationCompareDataProvider
     */
    public function testDurationCompare(Period $interval1, Period $interval2, int $expected): void
    {
        self::assertSame($expected, $interval1->durationCompare($interval2));
    }

    public function durationCompareDataProvider(): array
    {
        return [
            'duration less than' => [
                new Period(new DateTime('2012-01-01'), new DateTime('2012-01-15')),
                new Period(new DateTime('2013-01-01'), new DateTime('2013-01-16')),
                -1,
            ],
            'duration greater than' => [
                new Period(new DateTime('2012-01-01'), new DateTime('2012-01-15')),
                new Period(new DateTime('2012-01-01'), new DateTime('2012-01-07')),
                1,
            ],
            'duration equals with microsecond' => [
                new Period(new DateTime('2012-01-01 00:00:00'), new DateTime('2012-01-03 00:00:00.123456')),
                new Period(new DateTime('2012-02-02 00:00:00'), new DateTime('2012-02-04 00:00:00.123456')),
                0,
            ],
        ];
    }

    /**
     * @dataProvider durationCompareInnerMethodsDataProvider
     */
    public function testDurationCompareInnerMethods(Period $period1, Period $period2, string $method, bool $expected): void
    {
        self::assertSame($expected, $period1->$method($period2));
    }

    public function durationCompareInnerMethodsDataProvider(): array
    {
        return [
            'testDurationLessThan' => [
                new Period('2012-01-01', '2012-01-07'),
                new Period('2013-01-01', '2013-02-01'),
                'durationLessThan',
                true,
            ],
            'testDurationGreaterThanReturnsTrue' => [
                new Period('2012-01-01', '2012-02-01'),
                new Period('2012-01-01', '2012-01-07'),
                'durationGreaterThan',
                true,
            ],
            'testdurationEqualsReturnsTrueWithMicroseconds' => [
                new Period('2012-01-01 00:00:00', '2012-01-03 00:00:00'),
                new Period('2012-02-02 00:00:00', '2012-02-04 00:00:00'),
                'durationEquals',
                true,
            ],
        ];
    }

    public function testDateIntervalDiff(): void
    {
        $orig = Period::after('2012-01-01', '1 HOUR');
        $alt = Period::after('2012-01-01', '2 HOUR');
        self::assertSame(1, $orig->dateIntervalDiff($alt)->h);
        self::assertSame(0, $orig->dateIntervalDiff($alt)->days);
    }

    public function testTimestampIntervalDiff(): void
    {
        $orig = Period::after('2012-01-01', '1 HOUR');
        $alt = Period::after('2012-01-01', '2 HOUR');
        self::assertEquals(-3600, $orig->timestampIntervalDiff($alt));
    }

    public function testDateIntervalDiffPositionIrrelevant(): void
    {
        $orig = Period::after('2012-01-01', '1 HOUR');
        $alt = Period::after('2012-01-01', '2 HOUR');
        $fromOrig = $orig->dateIntervalDiff($alt);
        $fromOrig->invert = 1;
        self::assertEquals($fromOrig, $alt->dateIntervalDiff($orig));
    }

    public function testSplit(): void
    {
        $period = new Period(new DateTime('2012-01-12'), new DateTime('2012-01-13'));
        $range = $period->split(new DateInterval('PT1H'));
        $i = 0;
        foreach ($range as $innerPeriod) {
            ++$i;
        }
        self::assertSame(24, $i);
    }

    public function testSplitMustRecreateParentObject(): void
    {
        $period = new Period(new DateTime('2012-01-12'), new DateTime('2012-01-13'));
        $range = $period->split(new DateInterval('PT1H'));
        $total = null;
        foreach ($range as $part) {
            if (null === $total) {
                $total = $part;
                continue;
            }
            $total = $total->endingOn($part->getEndDate());
        }
        self::assertInstanceOf(Period::class, $total);
        self::assertTrue($total->equals($period));
    }

    public function testSplitWithLargeInterval(): void
    {
        $period = new Period(new DateTime('2012-01-12'), new DateTime('2012-01-13'));
        $range = [];
        foreach ($period->split(new DateInterval('P1Y')) as $innerPeriod) {
            $range[] = $innerPeriod;
        }
        self::assertCount(1, $range);
        self::assertTrue($range[0]->equals($period));
    }

    public function testSplitWithInconsistentInterval(): void
    {
        $last = null;
        $period = new Period(new DateTime('2012-01-12'), new DateTime('2012-01-13'));

        foreach ($period->split(new DateInterval('PT10H')) as $innerPeriod) {
            $last = $innerPeriod;
        }
        self::assertNotNull($last);
        self::assertSame(14400.0, $last->getTimestampInterval());
    }

    public function testSplitBackwards(): void
    {
        $period = new Period(new DateTime('2015-01-01'), new DateTime('2015-01-04'));
        $range = $period->splitBackwards(new DateInterval('P1D'));
        $list = [];
        foreach ($range as $innerPeriod) {
            $list[] = $innerPeriod;
        }

        $result = array_map(function (Period $range) {
            return [
                'start' => $range->getStartDate()->format('Y-m-d H:i:s'),
                'end'   => $range->getEndDate()->format('Y-m-d H:i:s'),
            ];
        }, $list);

        $expected = [
            [
                'start' => '2015-01-03 00:00:00',
                'end'   => '2015-01-04 00:00:00',
            ],
            [
                'start' => '2015-01-02 00:00:00',
                'end'   => '2015-01-03 00:00:00',
            ],
            [
                'start' => '2015-01-01 00:00:00',
                'end'   => '2015-01-02 00:00:00',
            ],
        ];
        self::assertSame($expected, $result);
    }

    public function testSplitBackwardsWithInconsistentInterval(): void
    {
        $period = new Period(new DateTime('2010-01-01'), new DateTime('2010-01-02'));
        $last = null;
        foreach ($period->splitBackwards(new DateInterval('PT10H')) as $innerPeriod) {
            $last = $innerPeriod;
        }

        self::assertNotNull($last);
        self::assertEquals(14400.0, $last->getTimestampInterval());
    }

    public function testSplitDaylightSavingsDayIntoHoursEndInterval(): void
    {
        date_default_timezone_set('Canada/Central');
        $period = new Period(new DateTime('2018-11-04 00:00:00.000000'), new DateTime('2018-11-04 05:00:00.000000'));
        $splits = $period->split(new DateInterval('PT30M'));
        $i = 0;
        foreach ($splits as $inner_period) {
            ++$i;
        }
        self::assertSame(10, $i);
    }

    public function testSplitBackwardsDaylightSavingsDayIntoHoursStartInterval(): void
    {
        date_default_timezone_set('Canada/Central');
        $period = new Period(new DateTime('2018-04-11 00:00:00.000000'), new DateTime('2018-04-11 05:00:00.000000'));
        $splits = $period->splitBackwards(new DateInterval('PT30M'));
        $i = 0;
        foreach ($splits as $inner_period) {
            ++$i;
        }
        self::assertSame(10, $i);
    }
}
