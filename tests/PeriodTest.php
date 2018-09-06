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
use DateTimeZone;
use Exception as PhpException;
use League\Period\Exception;
use League\Period\Period;
use PHPUnit\Framework\TestCase;
use TypeError;

class PeriodTest extends TestCase
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

    public function testGetDateInterval()
    {
        $interval = new Period(new DateTimeImmutable('2012-02-01'), new DateTimeImmutable('2012-02-02'));
        self::assertInstanceOf(DateInterval::class, $interval->getDateInterval());
    }

    public function testGetTimestampInterval()
    {
        $interval = new Period(new DateTimeImmutable('2012-02-01'), new DateTimeImmutable('2012-02-02'));
        self::assertInternalType('float', $interval->getTimestampInterval());
    }

    /**
     * @dataProvider provideGetDatePeriodData
     */
    public function testGetDatePeriod($interval, $option, $count)
    {
        $period = new Period(new DateTime('2012-01-12'), new DateTime('2012-01-13'));
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
        $orig = new Period(new DateTimeImmutable('2012-01-01'), new DateTimeImmutable('2012-02-01'));
        self::assertTrue($orig->isBefore(new DateTime('2015-01-01')));
        self::assertFalse($orig->isBefore(new DateTime('2010-01-01')));
    }

    public function testIsBeforeInterval()
    {
        $orig = new Period(new DateTimeImmutable('2012-01-01'), new DateTimeImmutable('2012-02-01'));
        $alt  = new Period(new DateTimeImmutable('2012-04-01'), new DateTimeImmutable('2012-06-01'));
        self::assertTrue($orig->isBefore($alt));
        self::assertFalse($alt->isBefore($orig));
    }

    public function testIsBeforeIntervalWithAbutsIntervals()
    {
        $orig = new Period(new DateTimeImmutable('2012-01-01'), new DateTimeImmutable('2012-02-01'));
        $alt =  new Period(new DateTimeImmutable('2012-02-01'), new DateTimeImmutable('2012-02-01 01:00:00'));
        self::assertTrue($orig->isBefore($alt));
    }

    public function testIsAfterDateTimeInterface()
    {
        $orig = new Period(new DateTimeImmutable('2012-01-01'), new DateTimeImmutable('2012-02-01'));
        self::assertFalse($orig->isAfter(new DateTime('2015-01-01')));
        self::assertTrue($orig->isAfter(new DateTime('2010-01-01')));
    }

    public function testIsAfterInterval()
    {
        $orig = new Period(new DateTimeImmutable('2012-01-01'), new DateTimeImmutable('2012-02-01'));
        $alt  = new Period(new DateTimeImmutable('2012-04-01'), new DateTimeImmutable('2012-06-01'));
        self::assertFalse($orig->isAfter($alt));
        self::assertTrue($alt->isAfter($orig));
    }

    public function testIsAfterDateTimeInterfaceAbuts()
    {
        $orig = new Period(new DateTimeImmutable('2012-01-01'), new DateTimeImmutable('2012-02-01'));
        self::assertTrue($orig->isBefore($orig->getEndDate()));
        self::assertFalse($orig->isAfter($orig->getStartDate()));
    }

    /**
     * @dataProvider provideAbutsData
     */
    public function testAbuts(Period $interval, Period $arg, $expected)
    {
        self::assertSame($expected, $interval->abuts($arg));
    }

    public function provideAbutsData()
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
     * @dataProvider provideOverlapsData
     */
    public function testOverlaps(Period $interval, Period $arg, $expected)
    {
        self::assertSame($expected, $interval->overlaps($arg));
    }

    public function provideOverlapsData()
    {
        return [
            'testOverlapsReturnsFalseWithAbutsIntervals' => [
                new Period(new DateTimeImmutable('2014-03-01'), new DateTimeImmutable('2014-04-01')),
                new Period(new DateTimeImmutable('2014-04-01'), new DateTimeImmutable('2014-05-01')),
                false,
            ],
            'testContainsReturnsFalseWithGappedIntervals' => [
                new Period(new DateTimeImmutable('2014-03-01'), new DateTimeImmutable('2014-04-01')),
                new Period(new DateTimeImmutable('2013-04-01'), new DateTimeImmutable('2013-05-01')),
                false,
            ],
            'testOverlapsReturnsTrue' => [
                new Period(new DateTimeImmutable('2014-03-01'), new DateTimeImmutable('2014-04-01')),
                new Period(new DateTimeImmutable('2014-03-15'), new DateTimeImmutable('2014-04-07')),
                true,
            ],
            'testOverlapsReturnsTureWithSameDatepointsIntervals' => [
                new Period(new DateTimeImmutable('2014-03-01'), new DateTimeImmutable('2014-04-01')),
                new Period(new DateTimeImmutable('2014-03-01'), new DateTimeImmutable('2014-04-01')),
                true,
            ],
            'testOverlapsReturnsTrueContainedIntervals' => [
                new Period(new DateTimeImmutable('2014-03-01'), new DateTimeImmutable('2014-04-01')),
                new Period(new DateTimeImmutable('2014-03-13'), new DateTimeImmutable('2014-03-15')),
                true,
            ],
            'testOverlapsReturnsTrueContainedIntervalsBackward' => [
                new Period(new DateTimeImmutable('2014-03-13'), new DateTimeImmutable('2014-03-15')),
                new Period(new DateTimeImmutable('2014-03-01'), new DateTimeImmutable('2014-04-01')),
                true,
            ],
        ];
    }

    /**
     * @dataProvider provideContainsData
     */
    public function testContains(Period $interval, $arg, $expected)
    {
        self::assertSame($expected, $interval->contains($arg));
    }

    public function provideContainsData()
    {
        return [
            'testContainsReturnsTrueWithADateTimeInterfaceObject' => [
                new Period(new DateTimeImmutable('2014-03-10'), new DateTimeImmutable('2014-03-15')),
                new DateTime('2014-03-12'),
                true,
            ],
            'testContainsReturnsTrueWithIntervalObject' => [
                new Period(new DateTimeImmutable('2014-01-01'), new DateTimeImmutable('2014-06-01')),
                new Period(new DateTimeImmutable('2014-01-01'), new DateTimeImmutable('2014-04-01')),
                true,
            ],
            'testContainsReturnsFalseWithADateTimeInterfaceObject' => [
                new Period(new DateTimeImmutable('2014-03-13'), new DateTimeImmutable('2014-03-15')),
                new DateTime('2015-03-12'),
                false,
            ],
            'testContainsReturnsFalseWithADateTimeInterfaceObjectAfterInterval' => [
                new Period(new DateTimeImmutable('2014-03-13'), new DateTimeImmutable('2014-03-15')),
                '2012-03-12',
                false,
            ],
            'testContainsReturnsFalseWithADateTimeInterfaceObjectBeforeInterval' => [
                new Period(new DateTimeImmutable('2014-03-13'), new DateTimeImmutable('2014-03-15')),
                '2014-04-01',
                false,
            ],
            'testContainsReturnsFalseWithAbutsIntervals' => [
                new Period(new DateTimeImmutable('2014-01-01'), new DateTimeImmutable('2014-04-01')),
                new Period(new DateTimeImmutable('2014-01-01'), new DateTimeImmutable('2014-06-01')),
                false,
            ],
            'testContainsReturnsTrueWithIntervalObjectWhichShareTheSameEndDate' => [
                new Period(new DateTimeImmutable('2015-01-01'), new DateTimeImmutable('2016-01-01')),
                new Period(new DateTimeImmutable('2015-12-01'), new DateTimeImmutable('2016-01-01')),
                true,
            ],
            'testContainsReturnsTrueWithAZeroDurationObject' => [
                new Period(new DateTimeImmutable('2012-03-12'), new DateTimeImmutable('2012-03-12')),
                new DateTime('2012-03-12'),
                true,
            ],
        ];
    }

    /**
     * @dataProvider provideCompareDurationData
     */
    public function testCompareDuration(Period $interval1, Period $interval2, int $expected)
    {
        self::assertSame($expected, $interval1->compareDuration($interval2));
    }

    public function provideCompareDurationData()
    {
        return [
            'testDurationLessThan' => [
                new Period(new DateTime('2012-01-01'), new DateTime('2012-01-15')),
                new Period(new DateTime('2013-01-01'), new DateTime('2013-01-16')),
                -1,
            ],
            'testDurationGreaterThanReturnsTrue' => [
                new Period(new DateTime('2012-01-01'), new DateTime('2012-01-15')),
                new Period(new DateTime('2012-01-01'), new DateTime('2012-01-07')),
                1,
            ],
            'testSameDurationAsReturnsTrueWithMicroseconds' => [
                new Period(new DateTime('2012-01-01 00:00:00'), new DateTime('2012-01-03 00:00:00')),
                new Period(new DateTime('2012-02-02 00:00:00'), new DateTime('2012-02-04 00:00:00')),
                0,
            ],
        ];
    }

    /**
     * @dataProvider provideEqualsToData
     */
    public function testEqualsTo(Period  $interval1, Period $interval2, bool $expected)
    {
        self::assertSame($expected, $interval1->equalsTo($interval2));
    }

    public function provideEqualsToData()
    {
        return [
            'testSameValueAsReturnsTrue' => [
                new Period(new DateTime('2012-01-01 00:00:00'), new DateTime('2012-01-03 00:00:00')),
                new Period(new DateTime('2012-01-01 00:00:00'), new DateTime('2012-01-03 00:00:00')),
                true,
            ],
            'testSameValueAsReturnsFalse' => [
                new Period(new DateTime('2012-01-01'), new DateTime('2012-01-15')),
                new Period(new DateTime('2012-01-01'), new DateTime('2012-01-07')),
                false,
            ],
            'testSameValueAsReturnsFalseArgumentOrderIndependent' => [
                new Period(new DateTime('2012-01-01'), new DateTime('2012-01-07')),
                new Period(new DateTime('2012-01-01'), new DateTime('2012-01-15')),
                false,
            ],
        ];
    }

    public function testSplit()
    {
        $period = new Period(new DateTime('2012-01-12'), new DateTime('2012-01-13'));
        $range = $period->split(new DateInterval('PT1H'));
        foreach ($range as $innerPeriod) {
            self::assertInstanceOf(Period::class, $innerPeriod);
        }
    }

    public function testSplitMustRecreateParentObject()
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
        self::assertTrue($total->equalsTo($period));
    }

    public function testSplitWithLargeInterval()
    {
        $period = new Period(new DateTime('2012-01-12'), new DateTime('2012-01-13'));
        $range = $period->split(new DateInterval('P1Y'));
        foreach ($range as $expectedPeriod) {
            self::assertInstanceOf(Period::class, $expectedPeriod);
            self::assertTrue($expectedPeriod->equalsTo($period));
        }
    }

    public function testSplitWithInconsistentInterval()
    {
        $last = null;
        $period = new Period(new DateTime('2012-01-12'), new DateTime('2012-01-13'));

        foreach ($period->split(new DateInterval('PT10H')) as $innerPeriod) {
            $last = $innerPeriod;
        }
        self::assertNotNull($last);
        self::assertSame(14400.0, $last->getTimestampInterval());
    }

    public function testSplitBackwards()
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

    public function testSplitBackwardsWithInconsistentInterval()
    {
        $period = new Period(new DateTime('2010-01-01'), new DateTime('2010-01-02'));
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
        $interval = new Period(new DateTime('2014-01-13'), new DateTime('2014-01-20'));
        $newInterval = $interval->startingOn($expected);
        self::assertTrue($newInterval->getStartDate() == $expected);
        self::assertEquals($interval->getStartDate(), new DateTimeImmutable('2014-01-13'));
        self::assertSame($interval->startingOn($interval->getStartDate()), $interval);
    }

    public function testStartingOnFailedWithWrongStartDate()
    {
        self::expectException(Exception::class);
        $interval = new Period(new DateTime('2014-01-13'), new DateTime('2014-01-20'));
        $interval->startingOn(new DateTime('2015-03-02'));
    }

    public function testEndingOn()
    {
        $expected  = new DateTime('2015-03-02');
        $interval = new Period(new DateTime('2014-01-13'), new DateTime('2014-01-20'));
        $newInterval = $interval->endingOn($expected);
        self::assertTrue($newInterval->getEndDate() == $expected);
        self::assertEquals($interval->getEndDate(), new DateTimeImmutable('2014-01-20'));
        self::assertSame($interval->endingOn($interval->getEndDate()), $interval);
    }

    public function testEndingOnFailedWithWrongEndDate()
    {
        self::expectException(Exception::class);
        $interval = new Period(new DateTime('2014-01-13'), new DateTime('2014-01-20'));
        $interval->endingOn(new DateTime('2012-03-02'));
    }

    public function testExpand()
    {
        $interval = (new Period(new DateTime('2012-02-02'), new DateTime('2012-02-03')))->expand(new DateInterval('P1D'));
        self::assertEquals(new DateTimeImmutable('2012-02-01'), $interval->getStartDate());
        self::assertEquals(new DateTimeImmutable('2012-02-04'), $interval->getEndDate());
    }

    public function testExpandRetunsSameInstance()
    {
        $interval = new Period(new DateTime('2012-02-02'), new DateTime('2012-02-03'));
        self::assertSame($interval->expand(new DateInterval('PT0S')), $interval);
    }

    public function testShrink()
    {
        $dateInterval = new DateInterval('PT12H');
        $dateInterval->invert = 1;
        $interval = (new Period(new DateTime('2012-02-02'), new DateTime('2012-02-03')))->expand($dateInterval);
        self::assertEquals(new DateTimeImmutable('2012-02-02 12:00:00'), $interval->getStartDate());
        self::assertEquals(new DateTimeImmutable('2012-02-02 12:00:00'), $interval->getEndDate());
    }

    public function testExpandThrowsException()
    {
        self::expectException(Exception::class);
        $dateInterval = new DateInterval('P1D');
        $dateInterval->invert = 1;
        $interval = (new Period(new DateTime('2012-02-02'), new DateTime('2012-02-03')))->expand($dateInterval);
    }

    public function testIntersect()
    {
        $orig = new Period(new DateTime('2011-12-01'), new DateTime('2012-04-01'));
        $alt = new Period(new DateTime('2012-01-01'), new DateTime('2012-03-01'));
        self::assertInstanceOf(Period::class, $orig->intersect($alt));
    }

    public function testIntersectThrowsExceptionWithNoOverlappingTimeRange()
    {
        self::expectException(Exception::class);
        $orig = new Period(new DateTime('2013-01-01'), new DateTime('2013-02-01'));
        $alt = new Period(new DateTime('2012-01-01'), new DateTime('2012-03-01'));
        $orig->intersect($alt);
    }

    public function testGap()
    {
        $orig = new Period(new DateTime('2011-12-01'), new DateTime('2012-02-01'));
        $alt = new Period(new DateTime('2012-06-01'), new DateTime('2012-09-01'));
        $res = $orig->gap($alt);

        self::assertInstanceOf(Period::class, $res);
        self::assertEquals($orig->getEndDate(), $res->getStartDate());
        self::assertEquals($alt->getStartDate(), $res->getEndDate());
        self::assertTrue($res->equalsTo($alt->gap($orig)));
    }

    public function testGapThrowsExceptionWithOverlapsInterval()
    {
        self::expectException(Exception::class);
        $orig = new Period(new DateTime('2011-12-01'), new DateTime('2012-02-01'));
        $alt = new Period(new DateTime('2011-12-10'), new DateTime('2011-12-15'));
        $orig->gap($alt);
    }

    public function testGapWithSameStartingInterval()
    {
        self::expectException(Exception::class);
        $orig = new Period(new DateTime('2011-12-01'), new DateTime('2012-02-01'));
        $alt = new Period(new DateTime('2011-12-01'), new DateTime('2011-12-15'));
        $orig->gap($alt);
    }

    public function testGapWithSameEndingInterval()
    {
        self::expectException(Exception::class);
        $orig = new Period(new DateTime('2011-12-01'), new DateTime('2012-02-01'));
        $alt = new Period(new DateTime('2012-01-15'), new DateTime('2012-02-01'));
        $orig->gap($alt);
    }

    public function testGapWithAdjacentInterval()
    {
        $orig = new Period(new DateTime('2011-12-01'), new DateTime('2012-02-01'));
        $alt = new Period(new DateTime('2012-02-01'), new DateTime('2012-02-02'));
        $gap = $orig->gap($alt);
        self::assertInstanceOf(Period::class, $gap);
        self::assertEquals(0, $gap->getTimestampInterval());
    }

    public function testMove()
    {
        $interval = new Period(new DateTime('2016-01-01 15:32:12'), new DateTime('2016-01-15 12:00:01'));
        $moved = $interval->move(new DateInterval('P1D'));
        self::assertFalse($interval->equalsTo($moved));
        self::assertTrue($interval->move(new DateInterval('PT0S'))->equalsTo($interval));
    }

    public function testMoveSupportStringIntervals()
    {
        $interval = new Period(new DateTime('2016-01-01 15:32:12'), new DateTime('2016-01-15 12:00:01'));
        $advanced = $interval->move(DateInterval::createFromDateString('1 DAY'));
        $alt = new Period(new DateTime('2016-01-02 15:32:12'), new DateTime('2016-01-16 12:00:01'));
        self::assertTrue($alt->equalsTo($advanced));
    }

    public function testMoveWithInvertedInterval()
    {
        $orig = new Period(new DateTime('2016-01-01 15:32:12'), new DateTime('2016-01-15 12:00:01'));
        $alt = new Period(new DateTime('2016-01-02 15:32:12'), new DateTime('2016-01-16 12:00:01'));
        $duration = new DateInterval('P1D');
        $duration->invert = 1;
        self::assertTrue($orig->equalsTo($alt->move($duration)));
    }

    public function testMoveWithInvertedStringInterval()
    {
        $orig = new Period(new DateTime('2016-01-01 15:32:12'), new DateTime('2016-01-15 12:00:01'));
        $alt = new Period(new DateTime('2016-01-02 15:32:12'), new DateTime('2016-01-16 12:00:01'));
        self::assertTrue($orig->equalsTo($alt->move(DateInterval::createFromDateString('-1 DAY'))));
    }

    public function testDiffThrowsException()
    {
        $interval1 = new Period(new DateTimeImmutable('2015-01-01'), new DateTimeImmutable('2016-01-01'));
        $interval2 = new Period(new DateTimeImmutable('2013-01-01'), new DateTimeImmutable('2014-01-01'));

        self::expectException(Exception::class);
        $interval1->diff($interval2);
    }

    public function testDiffWithEqualsPeriod()
    {
        $period = new Period(new DateTimeImmutable('2013-01-01'), new DateTimeImmutable('2014-01-01'));
        $alt = new Period(new DateTimeImmutable('2013-01-01'), new DateTimeImmutable('2014-01-01'));
        [$diff1, $diff2] = $alt->diff($period);
        self::assertNull($diff1);
        self::assertNull($diff2);
        self::assertEquals($alt->diff($period), $period->diff($alt));
    }

    public function testDiffWithPeriodSharingStartingDatepoints()
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

    public function testDiffWithPeriodSharingEndingDatepoints()
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

    public function testDiffWithOverlapsPeriod()
    {
        $period = new Period(new DateTimeImmutable('2013-01-01 10:00:00'), new DateTimeImmutable('2013-01-01 13:00:00'));
        $alt = new Period(new DateTimeImmutable('2013-01-01 11:00:00'), new DateTimeImmutable('2013-01-01 14:00:00'));
        [$diff1, $diff2] = $alt->diff($period);
        self::assertSame(3600.0, $diff1->getTimestampInterval());
        self::assertSame(3600.0, $diff2->getTimestampInterval());
        self::assertEquals($alt->diff($period), $period->diff($alt));
    }

    public function testToString()
    {
        date_default_timezone_set('Africa/Nairobi');
        $period = new Period('2014-05-01', '2014-05-08');
        $res = (string) $period;
        self::assertContains('2014-04-30T21:00:00', $res);
        self::assertContains('2014-05-07T21:00:00', $res);
    }

    public function testJsonSerialize()
    {
        $period = Period::createFromMonth(2015, 4);
        $json = json_encode($period);
        self::assertInternalType('string', $json);
        $res = json_decode($json);

        self::assertEquals($period->getStartDate(), new DateTimeImmutable($res->startDate));
        self::assertEquals($period->getEndDate(), new DateTimeImmutable($res->endDate));
    }

    public function testCreateFromDatePeriod()
    {
        $datePeriod = new DatePeriod(
            new DateTime('2016-05-16T00:00:00Z'),
            new DateInterval('P1D'),
            new DateTime('2016-05-20T00:00:00Z')
        );
        $period = Period::createFromDatePeriod($datePeriod);
        self::assertEquals($datePeriod->getStartDate(), $period->getStartDate());
        self::assertEquals($datePeriod->getEndDate(), $period->getEndDate());
    }

    public function testCreateFromDatePeriodThrowsException()
    {
        self::expectException(Exception::class);
        $datePeriod = new DatePeriod('R4/2012-07-01T00:00:00Z/P7D');
        Period::createFromDatePeriod($datePeriod);
    }

    public function testConstructorThrowTypeError()
    {
        self::expectException(TypeError::class);
        new Period(new DateTime(), []);
    }

    public function testSetState()
    {
        $period = new Period('2014-05-01', '2014-05-08');
        $generatedPeriod = eval('return '.var_export($period, true).';');
        self::assertTrue($generatedPeriod->equalsTo($period));
        self::assertEquals($generatedPeriod, $period);
    }

    public function testConstructor()
    {
        $period = new Period('2014-05-01', '2014-05-08');
        $start = $period->getStartDate();
        self::assertEquals(new DateTimeImmutable('2014-05-01'), $start);
        self::assertEquals(new DateTimeImmutable('2014-05-08'), $period->getEndDate());
        self::assertInstanceOf(DateTimeImmutable::class, $start);
    }

    public function testConstructorWithMicroSecondsSucceed()
    {
        $period = new Period('2014-05-01 00:00:00', '2014-05-01 00:00:00');
        self::assertEquals(new DateInterval('PT0S'), $period->getDateInterval());
    }

    public function testConstructorThrowException()
    {
        self::expectException(Exception::class);
        new Period(
            new DateTime('2014-05-01', new DateTimeZone('Europe/Paris')),
            new DateTime('2014-05-01', new DateTimeZone('Africa/Nairobi'))
        );
    }

    public function testConstructorWithDateTimeInterface()
    {
        $period = new Period('2014-05-01', new DateTime('2014-05-08'));
        self::assertInstanceOf(DateTimeImmutable::class, $period->getEndDate());
        self::assertInstanceOf(DateTimeImmutable::class, $period->getStartDate());
    }

    /**
     * @dataProvider provideCreateFromDurationData
     */
    public function testcreateFromDurationAfterStart($startDate, $endDate, $duration)
    {
        $period = Period::createFromDurationAfterStart($startDate, $duration);
        self::assertEquals(new DateTimeImmutable($startDate), $period->getStartDate());
        self::assertEquals(new DateTimeImmutable($endDate), $period->getEndDate());
    }

    public function provideCreateFromDurationData()
    {
        return [
            'usingAString' => [
                '2015-01-01', '2015-01-02', '+1 DAY',
            ],
            'usingAnInt' => [
                '2015-01-01 10:00:00', '2015-01-01 11:00:00', 3600,
            ],
            'usingADateInterval' => [
                '2015-01-01 10:00:00', '2015-01-01 11:00:00', new DateInterval('PT1H'),
            ],
            'usingAFloatWithNoMicroseconds' => [
                '2015-01-01 10:00:00', '2015-01-01 11:00:00', 3600.0,
            ],
            'usingAnInterval' => [
                '2015-01-01 10:00:00', '2015-01-01 11:00:00', Period::createFromHour('2012-01-03 12:00:00'),
            ],
        ];
    }

    public function testCreateFromDurationWithInvalidInteger()
    {
        self::expectException(PhpException::class);
        Period::createFromDurationAfterStart('2014-01-01', -1);
    }

    public function testCreateFromDurationFailedWithOutofRangeInterval()
    {
        self::expectException(Exception::class);
        Period::createFromDurationAfterStart(new DateTime('2012-01-12'), '-1 DAY');
    }

    public function testCreateFromDurationFailedWithInvalidInterval()
    {
        self::expectException(TypeError::class);
        Period::createFromDurationAfterStart(new DateTime('2012-01-12'), []);
    }

    /**
     * @dataProvider provideCreateFromDurationBeforeEndData
     */
    public function testCreateFromDurationBeforeEnd($startDate, $endDate, $duration)
    {
        $period = Period::createFromDurationBeforeEnd($endDate, $duration);
        self::assertEquals(new DateTimeImmutable($startDate), $period->getStartDate());
        self::assertEquals(new DateTimeImmutable($endDate), $period->getEndDate());
    }

    public function provideCreateFromDurationBeforeEndData()
    {
        return [
            'usingAString' => [
                '2015-01-01', '2015-01-02', '+1 DAY',
            ],
            'usingAnInt' => [
                '2015-01-01 10:00:00', '2015-01-01 11:00:00', 3600,
            ],
            'usingADateInterval' => [
                '2015-01-01 10:00:00', '2015-01-01 11:00:00', new DateInterval('PT1H'),
            ],
        ];
    }

    public function testCreateFromDurationBeforeEndFailedWithOutofRangeInterval()
    {
        self::expectException(Exception::class);
        Period::createFromDurationBeforeEnd(new DateTime('2012-01-12'), '-1 DAY');
    }

    public function testcreateFromISOWeek()
    {
        $period = Period::createFromISOWeek(2014, 3);
        self::assertEquals(new DateTimeImmutable('2014-01-13'), $period->getStartDate());
        self::assertEquals(new DateTimeImmutable('2014-01-20'), $period->getEndDate());
    }

    public function testcreateFromISOWeekFailedWithLowInvalidIndex()
    {
        self::expectException(Exception::class);
        Period::createFromISOWeek(2014, 0);
    }

    public function testcreateFromISOWeekFailedWithHighInvalidIndex()
    {
        self::expectException(Exception::class);
        Period::createFromISOWeek(2014, 54);
    }

    public function testcreateFromISOWeekFailedWithInvalidYearIndex()
    {
        self::expectException(TypeError::class);
        Period::createFromISOWeek([], 1);
    }

    public function testcreateFromISOWeekFailedWithMissingSemesterValue()
    {
        self::expectException(Exception::class);
        Period::createFromISOWeek(2014, null);
    }

    public function testCreateFromMonth()
    {
        $period = Period::createFromMonth(2014, 3);
        self::assertEquals(new DateTimeImmutable('2014-03-01'), $period->getStartDate());
        self::assertEquals(new DateTimeImmutable('2014-04-01'), $period->getEndDate());
    }

    public function testCreateFromMonthFailedWithHighInvalidIndex()
    {
        self::expectException(Exception::class);
        Period::createFromMonth(2014, 13);
    }

    public function testCreateFromMonthFailedWithLowInvalidIndex()
    {
        self::expectException(Exception::class);
        Period::createFromMonth(2014, 0);
    }

    public function testCreateFromMonthFailedWithInvalidYearIndex()
    {
        self::expectException(TypeError::class);
        Period::createFromMonth([], 1);
    }

    public function testCreateFromMonthFailedWithMissingSemesterValue()
    {
        self::expectException(Exception::class);
        Period::createFromMonth(2014, null);
    }

    public function testCreateFromQuarter()
    {
        $period = Period::createFromQuarter(2014, 3);
        self::assertEquals(new DateTimeImmutable('2014-07-01'), $period->getStartDate());
        self::assertEquals(new DateTimeImmutable('2014-10-01'), $period->getEndDate());
    }

    public function testCreateFromQuarterFailedWithHighInvalidIndex()
    {
        self::expectException(Exception::class);
        Period::createFromQuarter(2014, 5);
    }

    public function testCreateFromQuarterFailedWithLowInvalidIndex()
    {
        self::expectException(Exception::class);
        Period::createFromQuarter(2014, 0);
    }

    public function testCreateFromQuarterFailedWithInvalidYearIndex()
    {
        self::expectException(TypeError::class);
        Period::createFromQuarter([], 1);
    }

    public function testCreateFromQuarterFailedWithMissingSemesterValue()
    {
        self::expectException(Exception::class);
        Period::createFromQuarter(2014, null);
    }

    public function testCreateFromSemester()
    {
        $period = Period::createFromSemester(2014, 2);
        self::assertEquals(new DateTimeImmutable('2014-07-01'), $period->getStartDate());
        self::assertEquals(new DateTimeImmutable('2015-01-01'), $period->getEndDate());
    }

    public function testCreateFromSemesterFailedWithInvalidYearIndex()
    {
        self::expectException(TypeError::class);
        Period::createFromSemester([], 1);
    }

    public function testCreateFromSemesterFailedWithMissingSemesterValue()
    {
        self::expectException(Exception::class);
        Period::createFromSemester(2014, null);
    }

    public function testCreateFromSemesterFailedWithLowInvalidIndex()
    {
        self::expectException(Exception::class);
        Period::createFromSemester(2014, 0);
    }

    public function testCreateFromSemesterFailedWithHighInvalidIndex()
    {
        self::expectException(Exception::class);
        Period::createFromSemester(2014, 3);
    }

    public function testCreateFromYear()
    {
        $period = Period::createFromYear(2014);
        self::assertEquals(new DateTimeImmutable('2014-01-01'), $period->getStartDate());
        self::assertEquals(new DateTimeImmutable('2015-01-01'), $period->getEndDate());
    }

    public function testCreateFromISOYear()
    {
        $period = Period::createFromISOYear(2014);
        $interval = Period::createFromISOYear('2014-06-25');
        self::assertEquals(new DateTimeImmutable('2013-12-30'), $period->getStartDate());
        self::assertEquals(new DateTimeImmutable('2014-12-29'), $period->getEndDate());
        self::assertTrue($period->equalsTo($interval));
    }

    public function testCreateFromDay()
    {
        $period = Period::createFromDay(new ExtendedDate('2008-07-01T22:35:17.123456+08:00'));
        self::assertEquals(new DateTimeImmutable('2008-07-01T00:00:00+08:00'), $period->getStartDate());
        self::assertEquals(new DateTimeImmutable('2008-07-02T00:00:00+08:00'), $period->getEndDate());
        self::assertEquals('+08:00', $period->getStartDate()->format('P'));
        self::assertEquals('+08:00', $period->getEndDate()->format('P'));
        self::assertInstanceOf(ExtendedDate::class, $period->getStartDate());
        self::assertInstanceOf(ExtendedDate::class, $period->getEndDate());
    }

    public function testCreateFromHour()
    {
        $today = new ExtendedDate('2008-07-01T22:35:17.123456+08:00');
        $period = Period::createFromHour($today);
        self::assertEquals(new DateTimeImmutable('2008-07-01T22:00:00+08:00'), $period->getStartDate());
        self::assertEquals(new DateTimeImmutable('2008-07-01T23:00:00+08:00'), $period->getEndDate());
        self::assertEquals('+08:00', $period->getStartDate()->format('P'));
        self::assertEquals('+08:00', $period->getEndDate()->format('P'));
        self::assertInstanceOf(ExtendedDate::class, $period->getStartDate());
        self::assertInstanceOf(ExtendedDate::class, $period->getEndDate());
    }

    public function testCreateFromMinute()
    {
        $today = new ExtendedDate('2008-07-01T22:35:17.123456+08:00');
        $period = Period::createFromMinute($today);
        self::assertEquals(new DateTimeImmutable('2008-07-01T22:35:00+08:00'), $period->getStartDate());
        self::assertEquals(new DateTimeImmutable('2008-07-01T22:36:00+08:00'), $period->getEndDate());
        self::assertEquals('+08:00', $period->getStartDate()->format('P'));
        self::assertEquals('+08:00', $period->getEndDate()->format('P'));
        self::assertInstanceOf(ExtendedDate::class, $period->getStartDate());
        self::assertInstanceOf(ExtendedDate::class, $period->getEndDate());
    }

    public function testCreateFromSecond()
    {
        $today = new ExtendedDate('2008-07-01T22:35:17.123456+08:00');
        $period = Period::createFromSecond($today);
        self::assertEquals(new DateTimeImmutable('2008-07-01T22:35:17+08:00'), $period->getStartDate());
        self::assertEquals(new DateTimeImmutable('2008-07-01T22:35:18+08:00'), $period->getEndDate());
        self::assertEquals('+08:00', $period->getStartDate()->format('P'));
        self::assertEquals('+08:00', $period->getEndDate()->format('P'));
        self::assertInstanceOf(ExtendedDate::class, $period->getStartDate());
        self::assertInstanceOf(ExtendedDate::class, $period->getEndDate());
    }


    public function testcreateFromDatepoint()
    {
        $today = new ExtendedDate('2008-07-01T22:35:17.123456+08:00');
        $period = Period::createFromDatepoint($today);
        self::assertEquals($today, $period->getStartDate());
        self::assertEquals($today, $period->getEndDate());
        self::assertEquals('+08:00', $period->getStartDate()->format('P'));
        self::assertEquals('+08:00', $period->getEndDate()->format('P'));
        self::assertInstanceOf(ExtendedDate::class, $period->getStartDate());
        self::assertInstanceOf(ExtendedDate::class, $period->getEndDate());
        self::assertEquals(new DateInterval('P0D'), $period->getDateInterval());
    }

    public function testCreateFromWithDateTimeInterface()
    {
        self::assertTrue(Period::createFromISOWeek('2008W27')->equalsTo(Period::createFromISOWeek(2008, 27)));
        self::assertTrue(Period::createFromMonth('2008-07')->equalsTo(Period::createFromMonth(2008, 7)));
        self::assertTrue(Period::createFromQuarter('2008-02')->equalsTo(Period::createFromQuarter(2008, 1)));
        self::assertTrue(Period::createFromSemester('2008-10')->equalsTo(Period::createFromSemester(2008, 2)));
        self::assertTrue(Period::createFromYear('2008-01')->equalsTo(Period::createFromYear(2008)));
    }

    public function testCreateFromMonthWithDateTimeInterface()
    {
        $today = new ExtendedDate('2008-07-01T22:35:17.123456+08:00');
        $period = Period::createFromMonth($today);
        self::assertEquals(new DateTimeImmutable('2008-07-01T00:00:00+08:00'), $period->getStartDate());
        self::assertEquals(new DateTimeImmutable('2008-08-01T00:00:00+08:00'), $period->getEndDate());
        self::assertEquals('+08:00', $period->getStartDate()->format('P'));
        self::assertEquals('+08:00', $period->getEndDate()->format('P'));
        self::assertInstanceOf(ExtendedDate::class, $period->getStartDate());
        self::assertInstanceOf(ExtendedDate::class, $period->getEndDate());
    }

    public function testCreateFromYearWithDateTimeInterface()
    {
        $today = new ExtendedDate('2008-07-01T22:35:17.123456+08:00');
        $period = Period::createFromYear($today);
        self::assertEquals(new DateTimeImmutable('2008-01-01T00:00:00+08:00'), $period->getStartDate());
        self::assertEquals(new DateTimeImmutable('2009-01-01T00:00:00+08:00'), $period->getEndDate());
        self::assertEquals('+08:00', $period->getStartDate()->format('P'));
        self::assertEquals('+08:00', $period->getEndDate()->format('P'));
        self::assertInstanceOf(ExtendedDate::class, $period->getStartDate());
        self::assertInstanceOf(ExtendedDate::class, $period->getEndDate());
    }

    /**
     * @dataProvider provideCompareDurationInnerMethodsData
     */
    public function testCompareDurationInnerMethods(Period $period1, Period $period2, $method, $expected)
    {
        self::assertSame($expected, $period1->$method($period2));
    }

    public function provideCompareDurationInnerMethodsData()
    {
        return [
            'testDurationLessThan' => [
                Period::createFromDurationAfterStart('2012-01-01', '1 WEEK'),
                Period::createFromDurationAfterStart('2013-01-01', '1 MONTH'),
                'durationLessThan',
                true,
            ],
            'testDurationGreaterThanReturnsTrue' => [
                Period::createFromDurationAfterStart('2012-01-01', '1 MONTH'),
                Period::createFromDurationAfterStart('2012-01-01', '1 WEEK'),
                'durationGreaterThan',
                true,
            ],
            'testSameDurationAsReturnsTrueWithMicroseconds' => [
                new Period('2012-01-01 00:00:00', '2012-01-03 00:00:00'),
                new Period('2012-02-02 00:00:00', '2012-02-04 00:00:00'),
                'sameDurationAs',
                true,
            ],
        ];
    }

    public function testwithDurationAfterStart()
    {
        $expected = Period::createFromMonth(2014, 3);
        $period = Period::createFromDurationAfterStart('2014-03-01', '2 WEEKS');
        self::assertEquals($expected, $period->withDurationAfterStart('1 MONTH'));
    }

    public function testWithDurationThrowsException()
    {
        self::expectException(Exception::class);
        $period = Period::createFromDurationAfterStart('2014-03-01', '2 WEEKS');
        $interval = new DateInterval('P1D');
        $interval->invert = 1;
        $period->withDurationAfterStart($interval);
    }


    public function testWithDurationBeforeEnd()
    {
        $expected = Period::createFromMonth(2014, 2);
        $period = Period::createFromDurationBeforeEnd('2014-03-01', '2 WEEKS');
        self::assertEquals($expected, $period->withDurationBeforeEnd('1 MONTH'));
    }

    public function testWithDurationBeforeEndThrowsException()
    {
        self::expectException(Exception::class);
        $period = Period::createFromDurationBeforeEnd('2014-03-01', '2 WEEKS');
        $interval = new DateInterval('P1D');
        $interval->invert = 1;
        $period->withDurationBeforeEnd($interval);
    }

    public function testMerge()
    {
        $period = Period::createFromMonth(2014, 3);
        $altPeriod = Period::createFromMonth(2014, 4);
        $expected = Period::createFromDurationAfterStart('2014-03-01', '2 MONTHS');
        self::assertEquals($expected, $period->merge($altPeriod));
        self::assertEquals($expected, $altPeriod->merge($period));
        self::assertEquals($expected, $expected->merge($period, $altPeriod));
    }

    public function testMergeThrowsException()
    {
        self::expectException(TypeError::class);
        Period::createFromMonth(2014, 3)->merge();
    }

    public function testAdd()
    {
        $orig = Period::createFromDurationAfterStart('2012-01-01', '2 MONTH');
        $period = $orig->moveEndDate('-1 MONTH');
        self::assertSame(1, $orig->compareDuration($period));
        self::assertTrue($orig->durationGreaterThan($period));
        self::assertEquals($orig->getStartDate(), $period->getStartDate());
    }

    public function testAddThrowsException()
    {
        self::expectException(Exception::class);
        Period::createFromDurationAfterStart('2012-01-01', '1 MONTH')->moveEndDate('-3 MONTHS');
    }

    public function testMoveStartDateBackward()
    {
        $orig = Period::createFromMonth(2012, 1);
        $period = $orig->moveStartDate('-1 MONTH');
        self::assertSame(-1, $orig->compareDuration($period));
        self::assertTrue($orig->durationLessThan($period));
        self::assertEquals($orig->getEndDate(), $period->getEndDate());
        self::assertNotEquals($orig->getStartDate(), $period->getStartDate());
    }

    public function testMoveStartDateForward()
    {
        $orig = Period::createFromMonth(2012, 1);
        $period = $orig->moveStartDate('2 WEEKS');
        self::assertSame(1, $orig->compareDuration($period));
        self::assertTrue($orig->durationGreaterThan($period));
        self::assertEquals($orig->getEndDate(), $period->getEndDate());
        self::assertNotEquals($orig->getStartDate(), $period->getStartDate());
    }

    public function testMoveStartDateThrowsException()
    {
        self::expectException(Exception::class);
        Period::createFromDurationAfterStart('2012-01-01', '1 MONTH')->moveStartDate('3 MONTHS');
    }

    public function testSubThrowsException()
    {
        self::expectException(Exception::class);
        Period::createFromDurationAfterStart('2012-01-01', '1 MONTH')->moveEndDate('-3 MONTHS');
    }

    public function testDateIntervalDiff()
    {
        $orig = Period::createFromDurationAfterStart('2012-01-01', '1 HOUR');
        $alt = Period::createFromDurationAfterStart('2012-01-01', '2 HOUR');
        self::assertInstanceOf(DateInterval::class, $orig->dateIntervalDiff($alt));
    }

    public function testTimeIntervalDiff()
    {
        $orig = Period::createFromDurationAfterStart('2012-01-01', '1 HOUR');
        $alt = Period::createFromDurationAfterStart('2012-01-01', '2 HOUR');
        self::assertEquals(-3600, $orig->timestampIntervalDiff($alt));
    }

    public function testDateIntervalDiffPositionIrrelevant()
    {
        $orig = Period::createFromDurationAfterStart('2012-01-01', '1 HOUR');
        $alt = Period::createFromDurationAfterStart('2012-01-01', '2 HOUR');
        $fromOrig = $orig->dateIntervalDiff($alt);
        $fromOrig->invert = 1;
        self::assertEquals($fromOrig, $alt->dateIntervalDiff($orig));
    }
}
