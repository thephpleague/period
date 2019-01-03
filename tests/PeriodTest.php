<?php

/**
 * League.Period (https://period.thephpleague.com)
 *
 * (c) Ignace Nyamagana Butera <nyamsprod@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LeagueTest\Period;

use DateInterval;
use DatePeriod;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Generator;
use League\Period\Exception;
use League\Period\Period;
use PHPUnit\Framework\TestCase;
use TypeError;
use function League\Period\instant;
use function League\Period\interval_after;
use function League\Period\month;

class PeriodTest extends TestCase
{
    /**
     * @var string
     */
    protected $timezone;

    public function setUp(): void
    {
        $this->timezone = date_default_timezone_get();
    }

    public function tearDown(): void
    {
        date_default_timezone_set($this->timezone);
    }

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

    public function testIsBeforeDateTimeInterface(): void
    {
        $orig = new Period(new DateTimeImmutable('2012-01-01'), new DateTimeImmutable('2012-02-01'));
        self::assertTrue($orig->isBefore(new DateTime('2015-01-01')));
        self::assertFalse($orig->isBefore(new DateTime('2010-01-01')));
    }

    public function testIsBeforeInterval(): void
    {
        $orig = new Period(new DateTimeImmutable('2012-01-01'), new DateTimeImmutable('2012-02-01'));
        $alt  = new Period(new DateTimeImmutable('2012-04-01'), new DateTimeImmutable('2012-06-01'));
        self::assertTrue($orig->isBefore($alt));
        self::assertFalse($alt->isBefore($orig));
    }

    public function testIsBeforeIntervalWithAbutsIntervals(): void
    {
        $orig = new Period(new DateTimeImmutable('2012-01-01'), new DateTimeImmutable('2012-02-01'));
        $alt =  new Period(new DateTimeImmutable('2012-02-01'), new DateTimeImmutable('2012-02-01 01:00:00'));
        self::assertTrue($orig->isBefore($alt));
    }

    public function testIsAfterDateTimeInterface(): void
    {
        $orig = new Period(new DateTimeImmutable('2012-01-01'), new DateTimeImmutable('2012-02-01'));
        self::assertFalse($orig->isAfter(new DateTime('2015-01-01')));
        self::assertTrue($orig->isAfter(new DateTime('2010-01-01')));
    }

    public function testIsAfterInterval(): void
    {
        $orig = new Period(new DateTimeImmutable('2012-01-01'), new DateTimeImmutable('2012-02-01'));
        $alt  = new Period(new DateTimeImmutable('2012-04-01'), new DateTimeImmutable('2012-06-01'));
        self::assertFalse($orig->isAfter($alt));
        self::assertTrue($alt->isAfter($orig));
    }

    public function testIsAfterDateTimeInterfaceAbuts(): void
    {
        $orig = new Period(new DateTimeImmutable('2012-01-01'), new DateTimeImmutable('2012-02-01'));
        self::assertTrue($orig->isBefore($orig->getEndDate()));
        self::assertFalse($orig->isAfter($orig->getStartDate()));
    }

    /**
     * @dataProvider abutsDataProvider
     */
    public function testAbuts(Period $interval, Period $arg, bool $expected): void
    {
        self::assertSame($expected, $interval->abuts($arg));
    }

    public function abutsDataProvider(): array
    {
        return [
            'testAbutsReturnsTrueWithEqualDatePoints' => [
                new Period(new DateTimeImmutable('2012-01-01'), new DateTimeImmutable('2012-02-01')),
                new Period(new DateTimeImmutable('2012-02-01'), new DateTimeImmutable('2012-05-01')),
                true,
            ],
            'testAbutsReturnsFalseWithoutEqualDatePoints' => [
                new Period(new DateTimeImmutable('2012-01-01'), new DateTimeImmutable('2012-02-01')),
                new Period(new DateTimeImmutable('2012-01-01'), new DateTimeImmutable('2012-03-01')),
                false,
            ],
        ];
    }

    /**
     * @dataProvider overlapsDataProvider
     */
    public function testOverlaps(Period $interval, Period $arg, bool $expected): void
    {
        self::assertSame($expected, $interval->overlaps($arg));
    }

    public function overlapsDataProvider(): array
    {
        return [
            'overlaps returns false with gapped intervals' => [
                new Period(new DateTimeImmutable('2014-03-01'), new DateTimeImmutable('2014-04-01')),
                new Period(new DateTimeImmutable('2013-04-01'), new DateTimeImmutable('2013-05-01')),
                false,
            ],
            'overlaps returns false with abuts intervals' => [
                new Period(new DateTimeImmutable('2014-03-01'), new DateTimeImmutable('2014-04-01')),
                new Period(new DateTimeImmutable('2014-04-01'), new DateTimeImmutable('2014-05-01')),
                false,
            ],
            'overlaps returns' => [
                new Period(new DateTimeImmutable('2014-03-01'), new DateTimeImmutable('2014-04-01')),
                new Period(new DateTimeImmutable('2014-03-15'), new DateTimeImmutable('2014-04-07')),
                true,
            ],
            'overlaps returns with equals intervals' => [
                new Period(new DateTimeImmutable('2014-03-01'), new DateTimeImmutable('2014-04-01')),
                new Period(new DateTimeImmutable('2014-03-01'), new DateTimeImmutable('2014-04-01')),
                true,
            ],
            'overlaps returns with contained intervals' => [
                new Period(new DateTimeImmutable('2014-03-01'), new DateTimeImmutable('2014-04-01')),
                new Period(new DateTimeImmutable('2014-03-13'), new DateTimeImmutable('2014-03-15')),
                true,
            ],
            'overlaps returns with contained intervals backwards' => [
                new Period(new DateTimeImmutable('2014-03-13'), new DateTimeImmutable('2014-03-15')),
                new Period(new DateTimeImmutable('2014-03-01'), new DateTimeImmutable('2014-04-01')),
                true,
            ],
        ];
    }

    /**
     * @dataProvider containsDataProvider
     *
     * @param DateTimeInterface|Period|string $arg
     */
    public function testContains(Period $interval, $arg, bool $expected): void
    {
        self::assertSame($expected, $interval->contains($arg));
    }

    public function containsDataProvider(): array
    {
        return [
            'contains returns true with a DateTimeInterface object' => [
                new Period(new DateTimeImmutable('2014-03-10'), new DateTimeImmutable('2014-03-15')),
                new DateTime('2014-03-12'),
                true,
            ],
            'contains returns true with a Period object' => [
                new Period(new DateTimeImmutable('2014-01-01'), new DateTimeImmutable('2014-06-01')),
                new Period(new DateTimeImmutable('2014-01-01'), new DateTimeImmutable('2014-04-01')),
                true,
            ],
            'contains returns false with a DateTimeInterface object' => [
                new Period(new DateTimeImmutable('2014-03-13'), new DateTimeImmutable('2014-03-15')),
                new DateTime('2015-03-12'),
                false,
            ],
            'contains returns false with a DateTimeInterface object after the interval' => [
                new Period(new DateTimeImmutable('2014-03-13'), new DateTimeImmutable('2014-03-15')),
                '2012-03-12',
                false,
            ],
            'contains returns false with a DateTimeInterface object before the interval' => [
                new Period(new DateTimeImmutable('2014-03-13'), new DateTimeImmutable('2014-03-15')),
                '2014-04-01',
                false,
            ],
            'contains returns false with abuts interval' => [
                new Period(new DateTimeImmutable('2014-01-01'), new DateTimeImmutable('2014-04-01')),
                new Period(new DateTimeImmutable('2014-01-01'), new DateTimeImmutable('2014-06-01')),
                false,
            ],
            'contains returns true with a Period objects sharing the same end date' => [
                new Period(new DateTimeImmutable('2015-01-01'), new DateTimeImmutable('2016-01-01')),
                new Period(new DateTimeImmutable('2015-12-01'), new DateTimeImmutable('2016-01-01')),
                true,
            ],
            'contains returns false with O duration Period object' => [
                new Period(new DateTimeImmutable('2012-03-12'), new DateTimeImmutable('2012-03-12')),
                new DateTime('2012-03-12'),
                false,
            ],
        ];
    }

    /**
     * @dataProvider durationCompareDataProvider
     */
    public function testdurationCompare(Period $interval1, Period $interval2, int $expected): void
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
     * @dataProvider equalsDataProvider
     */
    public function testequals(Period  $interval1, Period $interval2, bool $expected): void
    {
        self::assertSame($expected, $interval1->equals($interval2));
    }

    public function equalsDataProvider(): array
    {
        return [
            'returns true' => [
                new Period(new DateTime('2012-01-01 00:00:00'), new DateTime('2012-01-03 00:00:00')),
                new Period(new DateTime('2012-01-01 00:00:00'), new DateTime('2012-01-03 00:00:00')),
                true,
            ],
            'returns false' => [
                new Period(new DateTime('2012-01-01'), new DateTime('2012-01-15')),
                new Period(new DateTime('2012-01-01'), new DateTime('2012-01-07')),
                false,
            ],
            'returns false is argument order independent' => [
                new Period(new DateTime('2012-01-01'), new DateTime('2012-01-07')),
                new Period(new DateTime('2012-01-01'), new DateTime('2012-01-15')),
                false,
            ],
        ];
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

    public function testStartingOn(): void
    {
        $expected = new DateTime('2012-03-02');
        $interval = new Period(new DateTime('2014-01-13'), new DateTime('2014-01-20'));
        $newInterval = $interval->startingOn($expected);
        self::assertTrue($newInterval->getStartDate() == $expected);
        self::assertEquals($interval->getStartDate(), new DateTimeImmutable('2014-01-13'));
        self::assertSame($interval->startingOn($interval->getStartDate()), $interval);
    }

    public function testStartingOnFailedWithWrongStartDate(): void
    {
        self::expectException(Exception::class);
        $interval = new Period(new DateTime('2014-01-13'), new DateTime('2014-01-20'));
        $interval->startingOn(new DateTime('2015-03-02'));
    }

    public function testEndingOn(): void
    {
        $expected  = new DateTime('2015-03-02');
        $interval = new Period(new DateTime('2014-01-13'), new DateTime('2014-01-20'));
        $newInterval = $interval->endingOn($expected);
        self::assertTrue($newInterval->getEndDate() == $expected);
        self::assertEquals($interval->getEndDate(), new DateTimeImmutable('2014-01-20'));
        self::assertSame($interval->endingOn($interval->getEndDate()), $interval);
    }

    public function testEndingOnFailedWithWrongEndDate(): void
    {
        self::expectException(Exception::class);
        $interval = new Period(new DateTime('2014-01-13'), new DateTime('2014-01-20'));
        $interval->endingOn(new DateTime('2012-03-02'));
    }

    public function testExpand(): void
    {
        $interval = (new Period(new DateTime('2012-02-02'), new DateTime('2012-02-03')))->expand(new DateInterval('P1D'));
        self::assertEquals(new DateTimeImmutable('2012-02-01'), $interval->getStartDate());
        self::assertEquals(new DateTimeImmutable('2012-02-04'), $interval->getEndDate());
    }

    public function testExpandRetunsSameInstance(): void
    {
        $interval = new Period(new DateTime('2012-02-02'), new DateTime('2012-02-03'));
        self::assertSame($interval->expand(new DateInterval('PT0S')), $interval);
    }

    public function testShrink(): void
    {
        $dateInterval = new DateInterval('PT12H');
        $dateInterval->invert = 1;
        $interval = (new Period(new DateTime('2012-02-02'), new DateTime('2012-02-03')))->expand($dateInterval);
        self::assertEquals(new DateTimeImmutable('2012-02-02 12:00:00'), $interval->getStartDate());
        self::assertEquals(new DateTimeImmutable('2012-02-02 12:00:00'), $interval->getEndDate());
    }

    public function testExpandThrowsException(): void
    {
        self::expectException(Exception::class);
        $dateInterval = new DateInterval('P1D');
        $dateInterval->invert = 1;
        $interval = (new Period(new DateTime('2012-02-02'), new DateTime('2012-02-03')))->expand($dateInterval);
    }

    public function testIntersect(): void
    {
        $orig = new Period(new DateTime('2011-12-01'), new DateTime('2012-04-01'));
        $alt = new Period(new DateTime('2012-01-01'), new DateTime('2012-03-01'));
        self::assertTrue($orig->intersect($alt)->equals(new Period('2012-01-01', '2012-03-01')));
    }

    public function testIntersectThrowsExceptionWithNoOverlappingTimeRange(): void
    {
        self::expectException(Exception::class);
        $orig = new Period(new DateTime('2013-01-01'), new DateTime('2013-02-01'));
        $alt = new Period(new DateTime('2012-01-01'), new DateTime('2012-03-01'));
        $orig->intersect($alt);
    }

    public function testGap(): void
    {
        $orig = new Period(new DateTime('2011-12-01'), new DateTime('2012-02-01'));
        $alt = new Period(new DateTime('2012-06-01'), new DateTime('2012-09-01'));
        $gap = $orig->gap($alt);

        self::assertEquals($orig->getEndDate(), $gap->getStartDate());
        self::assertEquals($alt->getStartDate(), $gap->getEndDate());
        self::assertTrue($gap->equals($alt->gap($orig)));
    }

    public function testGapThrowsExceptionWithOverlapsInterval(): void
    {
        self::expectException(Exception::class);
        $orig = new Period(new DateTime('2011-12-01'), new DateTime('2012-02-01'));
        $alt = new Period(new DateTime('2011-12-10'), new DateTime('2011-12-15'));
        $orig->gap($alt);
    }

    public function testGapWithSameStartingInterval(): void
    {
        self::expectException(Exception::class);
        $orig = new Period(new DateTime('2011-12-01'), new DateTime('2012-02-01'));
        $alt = new Period(new DateTime('2011-12-01'), new DateTime('2011-12-15'));
        $orig->gap($alt);
    }

    public function testGapWithSameEndingInterval(): void
    {
        self::expectException(Exception::class);
        $orig = new Period(new DateTime('2011-12-01'), new DateTime('2012-02-01'));
        $alt = new Period(new DateTime('2012-01-15'), new DateTime('2012-02-01'));
        $orig->gap($alt);
    }

    public function testGapWithAdjacentInterval(): void
    {
        $orig = new Period(new DateTime('2011-12-01'), new DateTime('2012-02-01'));
        $alt = new Period(new DateTime('2012-02-01'), new DateTime('2012-02-02'));
        self::assertEquals(0, $orig->gap($alt)->getTimestampInterval());
    }

    public function testMove(): void
    {
        $interval = new Period(new DateTime('2016-01-01 15:32:12'), new DateTime('2016-01-15 12:00:01'));
        $moved = $interval->move(new DateInterval('P1D'));
        self::assertFalse($interval->equals($moved));
        self::assertTrue($interval->move(new DateInterval('PT0S'))->equals($interval));
    }

    public function testMoveSupportStringIntervals(): void
    {
        $interval = new Period(new DateTime('2016-01-01 15:32:12'), new DateTime('2016-01-15 12:00:01'));
        $advanced = $interval->move(DateInterval::createFromDateString('1 DAY'));
        $alt = new Period(new DateTime('2016-01-02 15:32:12'), new DateTime('2016-01-16 12:00:01'));
        self::assertTrue($alt->equals($advanced));
    }

    public function testMoveWithInvertedInterval(): void
    {
        $orig = new Period(new DateTime('2016-01-01 15:32:12'), new DateTime('2016-01-15 12:00:01'));
        $alt = new Period(new DateTime('2016-01-02 15:32:12'), new DateTime('2016-01-16 12:00:01'));
        $duration = new DateInterval('P1D');
        $duration->invert = 1;
        self::assertTrue($orig->equals($alt->move($duration)));
    }

    public function testMoveWithInvertedStringInterval(): void
    {
        $orig = new Period(new DateTime('2016-01-01 15:32:12'), new DateTime('2016-01-15 12:00:01'));
        $alt = new Period(new DateTime('2016-01-02 15:32:12'), new DateTime('2016-01-16 12:00:01'));
        self::assertTrue($orig->equals($alt->move(DateInterval::createFromDateString('-1 DAY'))));
    }

    public function testDiffThrowsException(): void
    {
        $interval1 = new Period(new DateTimeImmutable('2015-01-01'), new DateTimeImmutable('2016-01-01'));
        $interval2 = new Period(new DateTimeImmutable('2013-01-01'), new DateTimeImmutable('2014-01-01'));

        self::expectException(Exception::class);
        $interval1->diff($interval2);
    }

    public function testDiffWithEqualsPeriod(): void
    {
        $period = new Period(new DateTimeImmutable('2013-01-01'), new DateTimeImmutable('2014-01-01'));
        $alt = new Period(new DateTimeImmutable('2013-01-01'), new DateTimeImmutable('2014-01-01'));
        [$diff1, $diff2] = $alt->diff($period);
        self::assertNull($diff1);
        self::assertNull($diff2);
        self::assertEquals($alt->diff($period), $period->diff($alt));
    }

    public function testDiffWithPeriodSharingStartingDatepoints(): void
    {
        $period = new Period(new DateTimeImmutable('2013-01-01'), new DateTimeImmutable('2014-01-01'));
        $alt = new Period(new DateTimeImmutable('2013-01-01'), new DateTimeImmutable('2013-04-01'));
        [$diff1, $diff2] = $alt->diff($period);
        self::assertInstanceOf(Period::class, $diff1);
        self::assertNull($diff2);
        self::assertEquals(new DateTimeImmutable('2013-04-01'), $diff1->getStartDate());
        self::assertEquals(new DateTimeImmutable('2014-01-01'), $diff1->getEndDate());
        self::assertEquals($alt->diff($period), $period->diff($alt));
    }

    public function testDiffWithPeriodSharingEndingDatepoints(): void
    {
        $period = new Period(new DateTimeImmutable('2013-01-01'), new DateTimeImmutable('2014-01-01'));
        $alt = new Period(new DateTimeImmutable('2013-10-01'), new DateTimeImmutable('2014-01-01'));
        [$diff1, $diff2] = $alt->diff($period);
        self::assertInstanceOf(Period::class, $diff1);
        self::assertNull($diff2);
        self::assertEquals(new DateTimeImmutable('2013-01-01'), $diff1->getStartDate());
        self::assertEquals(new DateTimeImmutable('2013-10-01'), $diff1->getEndDate());
        self::assertEquals($alt->diff($period), $period->diff($alt));
    }

    public function testDiffWithOverlapsPeriod(): void
    {
        $period = new Period(new DateTimeImmutable('2013-01-01 10:00:00'), new DateTimeImmutable('2013-01-01 13:00:00'));
        $alt = new Period(new DateTimeImmutable('2013-01-01 11:00:00'), new DateTimeImmutable('2013-01-01 14:00:00'));
        [$diff1, $diff2] = $alt->diff($period);
        self::assertInstanceOf(Period::class, $diff1);
        self::assertInstanceOf(Period::class, $diff2);
        self::assertSame(3600.0, $diff1->getTimestampInterval());
        self::assertSame(3600.0, $diff2->getTimestampInterval());
        self::assertEquals($alt->diff($period), $period->diff($alt));
    }

    public function testToString(): void
    {
        date_default_timezone_set('Africa/Nairobi');
        $period = new Period('2014-05-01', '2014-05-08');
        $res = (string) $period;
        self::assertContains('2014-04-30T21:00:00', $res);
        self::assertContains('2014-05-07T21:00:00', $res);
    }

    public function testJsonSerialize(): void
    {
        $period = month(2015, 4);
        $json = json_encode($period);
        self::assertInternalType('string', $json);
        $res = json_decode($json);

        self::assertEquals($period->getStartDate(), new DateTimeImmutable($res->startDate));
        self::assertEquals($period->getEndDate(), new DateTimeImmutable($res->endDate));
    }

    public function testFormat(): void
    {
        date_default_timezone_set('Africa/Nairobi');
        self::assertSame('[2015-04, 2015-05)', month(2015, 4)->format('Y-m'));
        self::assertSame('[2015-04-01 Africa/Nairobi, 2015-04-01 Africa/Nairobi)', instant('2015-04-01')->format('Y-m-d e'));
    }

    public function testConstructorThrowTypeError(): void
    {
        self::expectException(TypeError::class);
        new Period(new DateTime(), []);
    }

    public function testSetState(): void
    {
        $period = new Period('2014-05-01', '2014-05-08');
        $generatedPeriod = eval('return '.var_export($period, true).';');
        self::assertTrue($generatedPeriod->equals($period));
        self::assertEquals($generatedPeriod, $period);
    }

    public function testConstructor(): void
    {
        $period = new Period('2014-05-01', '2014-05-08');
        self::assertEquals(new DateTimeImmutable('2014-05-01'), $period->getStartDate());
        self::assertEquals(new DateTimeImmutable('2014-05-08'), $period->getEndDate());
    }

    public function testConstructorWithMicroSecondsSucceed(): void
    {
        $period = new Period('2014-05-01 00:00:00', '2014-05-01 00:00:00');
        self::assertEquals(new DateInterval('PT0S'), $period->getDateInterval());
    }

    public function testConstructorThrowException(): void
    {
        self::expectException(Exception::class);
        new Period(
            new DateTime('2014-05-01', new DateTimeZone('Europe/Paris')),
            new DateTime('2014-05-01', new DateTimeZone('Africa/Nairobi'))
        );
    }

    public function testConstructorWithDateTimeInterface(): void
    {
        $start = '2014-05-01';
        $end = new DateTime('2014-05-08');
        $period = new Period($start, $end);
        self::assertSame($start, $period->getStartDate()->format('Y-m-d'));
        self::assertEquals($end, $period->getEndDate());
    }

    /**
     * @dataProvider durationCompareInnerMethodsDataProvider
     */
    public function testdurationCompareInnerMethods(Period $period1, Period $period2, string $method, bool $expected): void
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

    public function testWithDurationAfterStart(): void
    {
        $expected = new Period('2014-03-01', '2014-04-01');
        $period = new Period('2014-03-01', '2014-03-15');
        self::assertEquals($expected, $period->withDurationAfterStart('1 MONTH'));
    }

    public function testWithDurationAfterStartThrowsException(): void
    {
        self::expectException(Exception::class);
        $period = new Period('2014-03-01', '2014-03-15');
        $interval = new DateInterval('P1D');
        $interval->invert = 1;
        $period->withDurationAfterStart($interval);
    }


    public function testWithDurationBeforeEnd(): void
    {
        $expected = new Period('2014-02-01', '2014-03-01');
        $period = new Period('2014-02-15', '2014-03-01');
        self::assertEquals($expected, $period->withDurationBeforeEnd('1 MONTH'));
    }

    public function testWithDurationBeforeEndThrowsException(): void
    {
        self::expectException(Exception::class);
        $period = new Period('2014-02-15', '2014-03-01');
        $interval = new DateInterval('P1D');
        $interval->invert = 1;
        $period->withDurationBeforeEnd($interval);
    }

    public function testMerge(): void
    {
        $period = month(2014, 3);
        $altPeriod = month(2014, 4);
        $expected = interval_after('2014-03-01', '2 MONTHS');
        self::assertEquals($expected, $period->merge($altPeriod));
        self::assertEquals($expected, $altPeriod->merge($period));
        self::assertEquals($expected, $expected->merge($period, $altPeriod));
    }

    public function testMergeThrowsException(): void
    {
        self::expectException(TypeError::class);
        month(2014, 3)->merge();
    }

    public function testMoveEndDate(): void
    {
        $orig = interval_after('2012-01-01', '2 MONTH');
        $period = $orig->moveEndDate('-1 MONTH');
        self::assertSame(1, $orig->durationCompare($period));
        self::assertTrue($orig->durationGreaterThan($period));
        self::assertEquals($orig->getStartDate(), $period->getStartDate());
    }

    public function testMoveEndDateThrowsException(): void
    {
        self::expectException(Exception::class);
        interval_after('2012-01-01', '1 MONTH')->moveEndDate('-3 MONTHS');
    }

    public function testMoveStartDateBackward(): void
    {
        $orig = month(2012, 1);
        $period = $orig->moveStartDate('-1 MONTH');
        self::assertSame(-1, $orig->durationCompare($period));
        self::assertTrue($orig->durationLessThan($period));
        self::assertEquals($orig->getEndDate(), $period->getEndDate());
        self::assertNotEquals($orig->getStartDate(), $period->getStartDate());
    }

    public function testMoveStartDateForward(): void
    {
        $orig = month(2012, 1);
        $period = $orig->moveStartDate('2 WEEKS');
        self::assertSame(1, $orig->durationCompare($period));
        self::assertTrue($orig->durationGreaterThan($period));
        self::assertEquals($orig->getEndDate(), $period->getEndDate());
        self::assertNotEquals($orig->getStartDate(), $period->getStartDate());
    }

    public function testMoveStartDateThrowsException(): void
    {
        self::expectException(Exception::class);
        interval_after('2012-01-01', '1 MONTH')->moveStartDate('3 MONTHS');
    }

    public function testDateIntervalDiff(): void
    {
        $orig = interval_after('2012-01-01', '1 HOUR');
        $alt = interval_after('2012-01-01', '2 HOUR');
        self::assertSame(1, $orig->dateIntervalDiff($alt)->h);
        self::assertSame(0, $orig->dateIntervalDiff($alt)->days);
    }

    public function testTimestampIntervalDiff(): void
    {
        $orig = interval_after('2012-01-01', '1 HOUR');
        $alt = interval_after('2012-01-01', '2 HOUR');
        self::assertEquals(-3600, $orig->timestampIntervalDiff($alt));
    }

    public function testDateIntervalDiffPositionIrrelevant(): void
    {
        $orig = interval_after('2012-01-01', '1 HOUR');
        $alt = interval_after('2012-01-01', '2 HOUR');
        $fromOrig = $orig->dateIntervalDiff($alt);
        $fromOrig->invert = 1;
        self::assertEquals($fromOrig, $alt->dateIntervalDiff($orig));
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
