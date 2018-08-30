<?php

/**
 * League.Period (https://period.thephpleague.com).
 *
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @license https://github.com/thephpleague/period/blob/master/LICENSE (MIT License)
 * @version 4.0.0
 * @link    https://github.com/thephpleague/period
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
use League\Period\Exception;
use League\Period\Interval;
use PHPUnit\Framework\TestCase as TestCase;

abstract class IntervalTest extends TestCase
{
    /**
     * @var string
     */
    protected $timezone;

    public function setUp()
    {
        $this->timezone = date_default_timezone_get();
    }

    public function tearDown()
    {
        date_default_timezone_set($this->timezone);
    }

    abstract protected function createInterval(DateTimeInterface $startDate, DateTimeInterface $endDate): Interval;

    public function testGetDateInterval()
    {
        $interval = $this->createInterval(new DateTimeImmutable('2012-02-01'), new DateTimeImmutable('2012-02-02'));
        self::assertInstanceOf(DateInterval::class, $interval->getDateInterval());
    }

    public function testGetTimestampInterval()
    {
        $interval = $this->createInterval(new DateTimeImmutable('2012-02-01'), new DateTimeImmutable('2012-02-02'));
        self::assertInternalType('float', $interval->getTimestampInterval());
    }

    /**
     * @dataProvider provideGetDatePeriodData
     */
    public function testGetDatePeriod($interval, $option, $count)
    {
        $period = $this->createInterval(new DateTime('2012-01-12'), new DateTime('2012-01-13'));
        $range = $period->getDatePeriod($interval, $option);
        self::assertInstanceOf(DatePeriod::class, $range);
        self::assertCount($count, iterator_to_array($range));
    }

    public function provideGetDatePeriodData()
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

    public function testIsBeforeDateTimeInterface()
    {
        $orig = $this->createInterval(new DateTimeImmutable('2012-01-01'), new DateTimeImmutable('2012-02-01'));
        self::assertTrue($orig->isBefore(new DateTime('2015-01-01')));
        self::assertFalse($orig->isBefore(new DateTime('2010-01-01')));
    }

    public function testIsBeforeInterval()
    {
        $orig = $this->createInterval(new DateTimeImmutable('2012-01-01'), new DateTimeImmutable('2012-02-01'));
        $alt  = $this->createInterval(new DateTimeImmutable('2012-04-01'), new DateTimeImmutable('2012-06-01'));
        self::assertTrue($orig->isBefore($alt));
        self::assertFalse($alt->isBefore($orig));
    }

    public function testIsBeforeIntervalWithAbutsIntervals()
    {
        $orig = $this->createInterval(new DateTimeImmutable('2012-01-01'), new DateTimeImmutable('2012-02-01'));
        $alt =  $this->createInterval(new DateTimeImmutable('2012-02-01'), new DateTimeImmutable('2012-02-01 01:00:00'));
        self::assertTrue($orig->isBefore($alt));
    }

    public function testIsAfterDateTimeInterface()
    {
        $orig = $this->createInterval(new DateTimeImmutable('2012-01-01'), new DateTimeImmutable('2012-02-01'));
        self::assertFalse($orig->isAfter(new DateTime('2015-01-01')));
        self::assertTrue($orig->isAfter(new DateTime('2010-01-01')));
    }

    public function testIsAfterInterval()
    {
        $orig = $this->createInterval(new DateTimeImmutable('2012-01-01'), new DateTimeImmutable('2012-02-01'));
        $alt  = $this->createInterval(new DateTimeImmutable('2012-04-01'), new DateTimeImmutable('2012-06-01'));
        self::assertFalse($orig->isAfter($alt));
        self::assertTrue($alt->isAfter($orig));
    }

    public function testIsAfterDateTimeInterfaceAbuts()
    {
        $orig = $this->createInterval(new DateTimeImmutable('2012-01-01'), new DateTimeImmutable('2012-02-01'));
        self::assertTrue($orig->isBefore($orig->getEndDate()));
        self::assertFalse($orig->isAfter($orig->getStartDate()));
    }

    /**
     * @dataProvider provideAbutsData
     */
    public function testAbuts(Interval $interval, Interval $arg, $expected)
    {
        self::assertSame($expected, $interval->abuts($arg));
    }

    public function provideAbutsData()
    {
        return [
            'testAbutsReturnsTrueWithEqualDatePoints' => [
                $this->createInterval(new DateTimeImmutable('2012-01-01'), new DateTimeImmutable('2012-02-01')),
                $this->createInterval(new DateTimeImmutable('2012-02-01'), new DateTimeImmutable('2012-05-01')),
                true,
            ],
            'testAbutsReturnsFalseWithoutEqualDatePoints' => [
                $this->createInterval(new DateTimeImmutable('2012-01-01'), new DateTimeImmutable('2012-02-01')),
                $this->createInterval(new DateTimeImmutable('2012-01-01'), new DateTimeImmutable('2012-03-01')),
                false,
            ],
        ];
    }

    /**
     * @dataProvider provideOverlapsData
     */
    public function testOverlaps(Interval $interval, Interval $arg, $expected)
    {
        self::assertSame($expected, $interval->overlaps($arg));
    }

    public function provideOverlapsData()
    {
        return [
            'testOverlapsReturnsFalseWithAbutsIntervals' => [
                $this->createInterval(new DateTimeImmutable('2014-03-01'), new DateTimeImmutable('2014-04-01')),
                $this->createInterval(new DateTimeImmutable('2014-04-01'), new DateTimeImmutable('2014-05-01')),
                false,
            ],
            'testContainsReturnsFalseWithGappedIntervals' => [
                $this->createInterval(new DateTimeImmutable('2014-03-01'), new DateTimeImmutable('2014-04-01')),
                $this->createInterval(new DateTimeImmutable('2013-04-01'), new DateTimeImmutable('2013-05-01')),
                false,
            ],
            'testOverlapsReturnsTrue' => [
                $this->createInterval(new DateTimeImmutable('2014-03-01'), new DateTimeImmutable('2014-04-01')),
                $this->createInterval(new DateTimeImmutable('2014-03-15'), new DateTimeImmutable('2014-04-07')),
                true,
            ],
            'testOverlapsReturnsTureWithSameDatepointsIntervals' => [
                $this->createInterval(new DateTimeImmutable('2014-03-01'), new DateTimeImmutable('2014-04-01')),
                $this->createInterval(new DateTimeImmutable('2014-03-01'), new DateTimeImmutable('2014-04-01')),
                true,
            ],
            'testOverlapsReturnsTrueContainedIntervals' => [
                $this->createInterval(new DateTimeImmutable('2014-03-01'), new DateTimeImmutable('2014-04-01')),
                $this->createInterval(new DateTimeImmutable('2014-03-13'), new DateTimeImmutable('2014-03-15')),
                true,
            ],
            'testOverlapsReturnsTrueContainedIntervalsBackward' => [
                $this->createInterval(new DateTimeImmutable('2014-03-13'), new DateTimeImmutable('2014-03-15')),
                $this->createInterval(new DateTimeImmutable('2014-03-01'), new DateTimeImmutable('2014-04-01')),
                true,
            ],
        ];
    }

    /**
     * @dataProvider provideContainsData
     */
    public function testContains(Interval $interval, $arg, $expected)
    {
        self::assertSame($expected, $interval->contains($arg));
    }

    public function provideContainsData()
    {
        return [
            'testContainsReturnsTrueWithADateTimeInterfaceObject' => [
                $this->createInterval(new DateTimeImmutable('2014-03-10'), new DateTimeImmutable('2014-03-15')),
                new DateTime('2014-03-12'),
                true,
            ],
            'testContainsReturnsTrueWithIntervalObject' => [
                $this->createInterval(new DateTimeImmutable('2014-01-01'), new DateTimeImmutable('2014-06-01')),
                $this->createInterval(new DateTimeImmutable('2014-01-01'), new DateTimeImmutable('2014-04-01')),
                true,
            ],
            'testContainsReturnsFalseWithADateTimeInterfaceObject' => [
                $this->createInterval(new DateTimeImmutable('2014-03-13'), new DateTimeImmutable('2014-03-15')),
                new DateTime('2015-03-12'),
                false,
            ],
            'testContainsReturnsFalseWithADateTimeInterfaceObjectAfterInterval' => [
                $this->createInterval(new DateTimeImmutable('2014-03-13'), new DateTimeImmutable('2014-03-15')),
                '2012-03-12',
                false,
            ],
            'testContainsReturnsFalseWithADateTimeInterfaceObjectBeforeInterval' => [
                $this->createInterval(new DateTimeImmutable('2014-03-13'), new DateTimeImmutable('2014-03-15')),
                '2014-04-01',
                false,
            ],
            'testContainsReturnsFalseWithAbutsIntervals' => [
                $this->createInterval(new DateTimeImmutable('2014-01-01'), new DateTimeImmutable('2014-04-01')),
                $this->createInterval(new DateTimeImmutable('2014-01-01'), new DateTimeImmutable('2014-06-01')),
                false,
            ],
            'testContainsReturnsTrueWithIntervalObjectWhichShareTheSameEndDate' => [
                $this->createInterval(new DateTimeImmutable('2015-01-01'), new DateTimeImmutable('2016-01-01')),
                $this->createInterval(new DateTimeImmutable('2015-12-01'), new DateTimeImmutable('2016-01-01')),
                true,
            ],
            'testContainsReturnsTrueWithAZeroDurationObject' => [
                $this->createInterval(new DateTimeImmutable('2012-03-12'), new DateTimeImmutable('2012-03-12')),
                new DateTime('2012-03-12'),
                true,
            ],
        ];
    }

    /**
     * @dataProvider provideCompareDurationData
     */
    public function testCompareDuration(Interval $interval1, Interval $interval2, int $expected)
    {
        self::assertSame($expected, $interval1->compareDuration($interval2));
    }

    public function provideCompareDurationData()
    {
        return [
            'testDurationLessThan' => [
                $this->createInterval(new DateTime('2012-01-01'), new DateTime('2012-01-15')),
                $this->createInterval(new DateTime('2013-01-01'), new DateTime('2013-01-16')),
                -1,
            ],
            'testDurationGreaterThanReturnsTrue' => [
                $this->createInterval(new DateTime('2012-01-01'), new DateTime('2012-01-15')),
                $this->createInterval(new DateTime('2012-01-01'), new DateTime('2012-01-07')),
                1,
            ],
            'testSameDurationAsReturnsTrueWithMicroseconds' => [
                $this->createInterval(new DateTime('2012-01-01 00:00:00'), new DateTime('2012-01-03 00:00:00')),
                $this->createInterval(new DateTime('2012-02-02 00:00:00'), new DateTime('2012-02-04 00:00:00')),
                0,
            ],
        ];
    }

    /**
     * @dataProvider provideEqualsToData
     */
    public function testEqualsTo(Interval $interval1, Interval $interval2, bool $expected)
    {
        self::assertSame($expected, $interval1->equalsTo($interval2));
    }

    public function provideEqualsToData()
    {
        return [
            'testSameValueAsReturnsTrue' => [
                $this->createInterval(new DateTime('2012-01-01 00:00:00'), new DateTime('2012-01-03 00:00:00')),
                $this->createInterval(new DateTime('2012-01-01 00:00:00'), new DateTime('2012-01-03 00:00:00')),
                true,
            ],
            'testSameValueAsReturnsFalse' => [
                $this->createInterval(new DateTime('2012-01-01'), new DateTime('2012-01-15')),
                $this->createInterval(new DateTime('2012-01-01'), new DateTime('2012-01-07')),
                false,
            ],
            'testSameValueAsReturnsFalseArgumentOrderIndependent' => [
                $this->createInterval(new DateTime('2012-01-01'), new DateTime('2012-01-07')),
                $this->createInterval(new DateTime('2012-01-01'), new DateTime('2012-01-15')),
                false,
            ],
        ];
    }

    public function testSplit()
    {
        $period = $this->createInterval(new DateTime('2012-01-12'), new DateTime('2012-01-13'));
        $range = $period->split(new DateInterval('PT1H'));
        foreach ($range as $innerPeriod) {
            self::assertInstanceOf(Interval::class, $innerPeriod);
        }
    }

    public function testSplitMustRecreateParentObject()
    {
        $period = $this->createInterval(new DateTime('2012-01-12'), new DateTime('2012-01-13'));
        $range = $period->split(new DateInterval('PT1H'));
        $total = null;
        foreach ($range as $part) {
            if (null === $total) {
                $total = $part;
                continue;
            }
            $total = $total->endingOn($part->getEndDate());
        }
        self::assertInstanceOf(Interval::class, $total);
        self::assertTrue($total->equalsTo($period));
    }

    public function testSplitWithLargeInterval()
    {
        $period = $this->createInterval(new DateTime('2012-01-12'), new DateTime('2012-01-13'));
        $range = $period->split(new DateInterval('P1Y'));
        foreach ($range as $expectedPeriod) {
            self::assertInstanceOf(Interval::class, $expectedPeriod);
            self::assertTrue($expectedPeriod->equalsTo($period));
        }
    }

    public function testSplitWithInconsistentInterval()
    {
        $last = null;
        $period = $this->createInterval(new DateTime('2012-01-12'), new DateTime('2012-01-13'));

        foreach ($period->split(new DateInterval('PT10H')) as $innerPeriod) {
            $last = $innerPeriod;
        }
        self::assertNotNull($last);
        self::assertSame(14400.0, $last->getTimestampInterval());
    }

    public function testSplitBackwards()
    {
        $period = $this->createInterval(new DateTime('2015-01-01'), new DateTime('2015-01-04'));
        $range = $period->splitBackwards(new DateInterval('P1D'));
        $list = [];
        foreach ($range as $innerPeriod) {
            $list[] = $innerPeriod;
        }

        $result = array_map(function (Interval $range) {
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

    public function testSplitBackwardsWithInconsistentInterval()
    {
        $period = $this->createInterval(new DateTime('2010-01-01'), new DateTime('2010-01-02'));
        $last = null;
        foreach ($period->splitBackwards(new DateInterval('PT10H')) as $innerPeriod) {
            $last = $innerPeriod;
        }

        self::assertNotNull($last);
        self::assertEquals(14400.0, $last->getTimestampInterval());
    }

    public function testStartingOn()
    {
        $expected  = new DateTime('2012-03-02');
        $interval = $this->createInterval(new DateTime('2014-01-13'), new DateTime('2014-01-20'));
        $newInterval = $interval->startingOn($expected);
        self::assertTrue($newInterval->getStartDate() == $expected);
        self::assertEquals($interval->getStartDate(), new DateTimeImmutable('2014-01-13'));
        self::assertSame($interval->startingOn($interval->getStartDate()), $interval);
    }

    public function testStartingOnFailedWithWrongStartDate()
    {
        self::expectException(Exception::class);
        $interval = $this->createInterval(new DateTime('2014-01-13'), new DateTime('2014-01-20'));
        $interval->startingOn(new DateTime('2015-03-02'));
    }

    public function testEndingOn()
    {
        $expected  = new DateTime('2015-03-02');
        $interval = $this->createInterval(new DateTime('2014-01-13'), new DateTime('2014-01-20'));
        $newInterval = $interval->endingOn($expected);
        self::assertTrue($newInterval->getEndDate() == $expected);
        self::assertEquals($interval->getEndDate(), new DateTimeImmutable('2014-01-20'));
        self::assertSame($interval->endingOn($interval->getEndDate()), $interval);
    }

    public function testEndingOnFailedWithWrongEndDate()
    {
        self::expectException(Exception::class);
        $interval = $this->createInterval(new DateTime('2014-01-13'), new DateTime('2014-01-20'));
        $interval->endingOn(new DateTime('2012-03-02'));
    }

    public function testExpand()
    {
        $interval = $this->createInterval(new DateTime('2012-02-02'), new DateTime('2012-02-03'))->expand(new DateInterval('P1D'));
        self::assertEquals(new DateTimeImmutable('2012-02-01'), $interval->getStartDate());
        self::assertEquals(new DateTimeImmutable('2012-02-04'), $interval->getEndDate());
    }

    public function testExpandRetunsSameInstance()
    {
        $interval = $this->createInterval(new DateTime('2012-02-02'), new DateTime('2012-02-03'));
        self::assertSame($interval->expand(new DateInterval('PT0S')), $interval);
    }

    public function testShrink()
    {
        $dateInterval = new DateInterval('PT12H');
        $dateInterval->invert = 1;
        $interval = $this->createInterval(new DateTime('2012-02-02'), new DateTime('2012-02-03'))->expand($dateInterval);
        self::assertEquals(new DateTimeImmutable('2012-02-02 12:00:00'), $interval->getStartDate());
        self::assertEquals(new DateTimeImmutable('2012-02-02 12:00:00'), $interval->getEndDate());
    }

    public function testExpandThrowsException()
    {
        self::expectException(Exception::class);
        $dateInterval = new DateInterval('P1D');
        $dateInterval->invert = 1;
        $interval = $this->createInterval(new DateTime('2012-02-02'), new DateTime('2012-02-03'))->expand($dateInterval);
    }

    public function testIntersect()
    {
        $orig = $this->createInterval(new DateTime('2011-12-01'), new DateTime('2012-04-01'));
        $alt = $this->createInterval(new DateTime('2012-01-01'), new DateTime('2012-03-01'));
        self::assertInstanceOf(Interval::class, $orig->intersect($alt));
    }

    public function testIntersectThrowsExceptionWithNoOverlappingTimeRange()
    {
        self::expectException(Exception::class);
        $orig = $this->createInterval(new DateTime('2013-01-01'), new DateTime('2013-02-01'));
        $alt = $this->createInterval(new DateTime('2012-01-01'), new DateTime('2012-03-01'));
        $orig->intersect($alt);
    }

    public function testGap()
    {
        $orig = $this->createInterval(new DateTime('2011-12-01'), new DateTime('2012-02-01'));
        $alt = $this->createInterval(new DateTime('2012-06-01'), new DateTime('2012-09-01'));
        $res = $orig->gap($alt);

        self::assertInstanceOf(Interval::class, $res);
        self::assertEquals($orig->getEndDate(), $res->getStartDate());
        self::assertEquals($alt->getStartDate(), $res->getEndDate());
        self::assertTrue($res->equalsTo($alt->gap($orig)));
    }

    public function testGapThrowsExceptionWithOverlapsInterval()
    {
        self::expectException(Exception::class);
        $orig = $this->createInterval(new DateTime('2011-12-01'), new DateTime('2012-02-01'));
        $alt = $this->createInterval(new DateTime('2011-12-10'), new DateTime('2011-12-15'));
        $orig->gap($alt);
    }

    public function testGapWithSameStartingInterval()
    {
        self::expectException(Exception::class);
        $orig = $this->createInterval(new DateTime('2011-12-01'), new DateTime('2012-02-01'));
        $alt = $this->createInterval(new DateTime('2011-12-01'), new DateTime('2011-12-15'));
        $orig->gap($alt);
    }

    public function testGapWithSameEndingInterval()
    {
        self::expectException(Exception::class);
        $orig = $this->createInterval(new DateTime('2011-12-01'), new DateTime('2012-02-01'));
        $alt = $this->createInterval(new DateTime('2012-01-15'), new DateTime('2012-02-01'));
        $orig->gap($alt);
    }

    public function testGapWithAdjacentInterval()
    {
        $orig = $this->createInterval(new DateTime('2011-12-01'), new DateTime('2012-02-01'));
        $alt = $this->createInterval(new DateTime('2012-02-01'), new DateTime('2012-02-02'));
        $gap = $orig->gap($alt);
        self::assertInstanceOf(Interval::class, $gap);
        self::assertEquals(0, $gap->getTimestampInterval());
    }

    public function testMove()
    {
        $interval = $this->createInterval(new DateTime('2016-01-01 15:32:12'), new DateTime('2016-01-15 12:00:01'));
        $moved = $interval->move(new DateInterval('P1D'));
        self::assertFalse($interval->equalsTo($moved));
        self::assertTrue($interval->move(new DateInterval('PT0S'))->equalsTo($interval));
    }

    public function testMoveSupportStringIntervals()
    {
        $interval = $this->createInterval(new DateTime('2016-01-01 15:32:12'), new DateTime('2016-01-15 12:00:01'));
        $advanced = $interval->move(DateInterval::createFromDateString('1 DAY'));
        $alt = $this->createInterval(new DateTime('2016-01-02 15:32:12'), new DateTime('2016-01-16 12:00:01'));
        self::assertTrue($alt->equalsTo($advanced));
    }

    public function testMoveWithInvertedInterval()
    {
        $orig = $this->createInterval(new DateTime('2016-01-01 15:32:12'), new DateTime('2016-01-15 12:00:01'));
        $alt = $this->createInterval(new DateTime('2016-01-02 15:32:12'), new DateTime('2016-01-16 12:00:01'));
        $duration = new DateInterval('P1D');
        $duration->invert = 1;
        self::assertTrue($orig->equalsTo($alt->move($duration)));
    }

    public function testMoveWithInvertedStringInterval()
    {
        $orig = $this->createInterval(new DateTime('2016-01-01 15:32:12'), new DateTime('2016-01-15 12:00:01'));
        $alt = $this->createInterval(new DateTime('2016-01-02 15:32:12'), new DateTime('2016-01-16 12:00:01'));
        self::assertTrue($orig->equalsTo($alt->move(DateInterval::createFromDateString('-1 DAY'))));
    }

    public function testDiffThrowsException()
    {
        $interval1 = $this->createInterval(new DateTimeImmutable('2015-01-01'), new DateTimeImmutable('2016-01-01'));
        $interval2 = $this->createInterval(new DateTimeImmutable('2013-01-01'), new DateTimeImmutable('2014-01-01'));

        self::expectException(Exception::class);
        $interval1->diff($interval2);
    }

    public function testDiffWithEqualsPeriod()
    {
        $period = $this->createInterval(new DateTimeImmutable('2013-01-01'), new DateTimeImmutable('2014-01-01'));
        $alt = $this->createInterval(new DateTimeImmutable('2013-01-01'), new DateTimeImmutable('2014-01-01'));
        [$diff1, $diff2] = $alt->diff($period);
        self::assertNull($diff1);
        self::assertNull($diff2);
        self::assertEquals($alt->diff($period), $period->diff($alt));
    }

    public function testDiffWithPeriodSharingStartingDatepoints()
    {
        $period = $this->createInterval(new DateTimeImmutable('2013-01-01'), new DateTimeImmutable('2014-01-01'));
        $alt = $this->createInterval(new DateTimeImmutable('2013-01-01'), new DateTimeImmutable('2013-04-01'));
        [$diff1, $diff2] = $alt->diff($period);
        self::assertInstanceOf(Interval::class, $diff1);
        self::assertNull($diff2);
        self::assertEquals(new DateTimeImmutable('2013-04-01'), $diff1->getStartDate());
        self::assertEquals(new DateTimeImmutable('2014-01-01'), $diff1->getEndDate());
        self::assertEquals($alt->diff($period), $period->diff($alt));
    }

    public function testDiffWithPeriodSharingEndingDatepoints()
    {
        $period = $this->createInterval(new DateTimeImmutable('2013-01-01'), new DateTimeImmutable('2014-01-01'));
        $alt = $this->createInterval(new DateTimeImmutable('2013-10-01'), new DateTimeImmutable('2014-01-01'));
        [$diff1, $diff2] = $alt->diff($period);
        self::assertInstanceOf(Interval::class, $diff1);
        self::assertNull($diff2);
        self::assertEquals(new DateTimeImmutable('2013-01-01'), $diff1->getStartDate());
        self::assertEquals(new DateTimeImmutable('2013-10-01'), $diff1->getEndDate());
        self::assertEquals($alt->diff($period), $period->diff($alt));
    }

    public function testDiffWithOverlapsPeriod()
    {
        $period = $this->createInterval(new DateTimeImmutable('2013-01-01 10:00:00'), new DateTimeImmutable('2013-01-01 13:00:00'));
        $alt = $this->createInterval(new DateTimeImmutable('2013-01-01 11:00:00'), new DateTimeImmutable('2013-01-01 14:00:00'));
        [$diff1, $diff2] = $alt->diff($period);
        self::assertSame(3600.0, $diff1->getTimestampInterval());
        self::assertSame(3600.0, $diff2->getTimestampInterval());
        self::assertEquals($alt->diff($period), $period->diff($alt));
    }
}
