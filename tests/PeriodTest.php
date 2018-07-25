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

namespace League\Period\Test;

use DateInterval;
use DatePeriod;
use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use League\Period\Exception;
use League\Period\Period;
use PHPUnit\Framework\TestCase as TestCase;
use TypeError;

class PeriodTest extends TestCase
{
    /**
     * @var string
     */
    private $timezone;

    public function setUp()
    {
        $this->timezone = date_default_timezone_get();
    }

    public function tearDown()
    {
        date_default_timezone_set($this->timezone);
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

    /**
     * @dataProvider provideGetDatePeriodData
     */
    public function testGetDatePeriod($interval, $option, $count)
    {
        $period = Period::createFromDuration(new DateTime('2012-01-12'), '1 DAY');
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

    public function testGetDateInterval()
    {
        $period = Period::createFromMonth(2014, 3);
        self::assertInstanceOf(DateInterval::class, $period->getDateInterval());
    }

    public function testGetTimestampInterval()
    {
        $period = Period::createFromMonth(2014, 3);
        self::assertInternalType('float', $period->getTimestampInterval());
    }

    public function testSplit()
    {
        $period = Period::createFromDuration(new DateTime('2012-01-12'), '1 DAY');
        $range = $period->split(3600);
        foreach ($range as $innerPeriod) {
            self::assertInstanceOf(Period::class, $innerPeriod);
        }
    }

    public function testSplitMustRecreateParentObject()
    {
        $period = Period::createFromDuration(new DateTime('2012-01-12'), '1 DAY');
        $range  = $period->split(3600);
        $total = null;
        foreach ($range as $part) {
            if (is_null($total)) {
                $total = $part;
                continue;
            }
            $total = $total->merge($part);
        }
        self::assertEquals($period, $total);
    }


    public function testSplitWithLargeInterval()
    {
        $period = Period::createFromDuration(new DateTime('2012-01-12'), '1 DAY');
        $range  = $period->split('2 DAY');
        foreach ($range as $expectedPeriod) {
            self::assertEquals($period, $expectedPeriod);
        }
    }

    public function testSplitWithInconsistentInterval()
    {
        $last = null;
        foreach (Period::createFromDuration(new DateTime('2012-01-12'), '1 DAY')->split('10 HOURS') as $innerPeriod) {
            $last = $innerPeriod;
        }
        self::assertNotNull($last);
        self::assertEquals(14400, $last->getTimestampInterval());
    }

    public function testSplitDataBackwards()
    {
        $period = Period::createFromDuration(new DateTime('2015-01-01'), '3 days');
        $range = $period->splitBackwards('1 day');
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
        $period = Period::createFromDuration('2010-01-01', '1 DAY');
        $last = null;
        foreach ($period->splitBackwards('10 HOURS') as $innerPeriod) {
            $last = $innerPeriod;
        }

        self::assertNotNull($last);
        self::assertEquals(14400, $last->getTimestampInterval());
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
    public function testCreateFromDuration($startDate, $endDate, $duration)
    {
        $period = Period::createFromDuration($startDate, $duration);
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
        ];
    }

    public function testCreateFromDurationWithInvalidInteger()
    {
        self::expectException(\Exception::class);
        Period::createFromDuration('2014-01-01', -1);
    }

    public function testCreateFromDurationFailedWithOutofRangeInterval()
    {
        self::expectException(Exception::class);
        Period::createFromDuration(new DateTime('2012-01-12'), '-1 DAY');
    }

    public function testCreateFromDurationFailedWithInvalidInterval()
    {
        self::expectException(TypeError::class);
        Period::createFromDuration(new DateTime('2012-01-12'), []);
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

    public function testCreateFromWeek()
    {
        $period = Period::createFromWeek(2014, 3);
        self::assertEquals(new DateTimeImmutable('2014-01-13'), $period->getStartDate());
        self::assertEquals(new DateTimeImmutable('2014-01-20'), $period->getEndDate());
    }

    public function testCreateFromWeekFailedWithLowInvalidIndex()
    {
        self::expectException(Exception::class);
        Period::createFromWeek(2014, 0);
    }

    public function testCreateFromWeekFailedWithHighInvalidIndex()
    {
        self::expectException(Exception::class);
        Period::createFromWeek(2014, 54);
    }

    public function testCreateFromWeekFailedWithInvalidYearIndex()
    {
        self::expectException(TypeError::class);
        Period::createFromWeek([], 1);
    }

    public function testCreateFromWeekFailedWithMissingSemesterValue()
    {
        self::expectException(Exception::class);
        Period::createFromWeek(2014, null);
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

    public function testCreateFromWithDateTimeInterface()
    {
        self::assertTrue(Period::createFromWeek('2008W27')->equalsTo(Period::createFromWeek(2008, 27)));
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

    public function testIsBeforeDatetime()
    {
        $orig = Period::createFromDuration('2012-01-01', '1 MONTH');
        self::assertTrue($orig->isBefore(new DateTime('2015-01-01')));
        self::assertFalse($orig->isBefore(new DateTime('2010-01-01')));
    }

    public function testIsBeforePeriod()
    {
        $orig = Period::createFromDuration('2012-01-01', '1 MONTH');
        $alt  = Period::createFromDuration('2012-04-01', '2 MONTH');
        self::assertTrue($orig->isBefore($alt));
        self::assertFalse($alt->isBefore($orig));
    }

    public function testIsBeforePeriodWithAbutsPeriods()
    {
        $orig = Period::createFromDuration('2012-01-01', '1 MONTH');
        self::assertTrue($orig->isBefore(Period::createFromDuration('2012-02-01', new DateInterval('PT1H'))));
    }

    public function testIsAfterDatetime()
    {
        $orig = Period::createFromDuration('2012-01-01', '1 MONTH');
        self::assertFalse($orig->isAfter(new DateTime('2015-01-01')));
        self::assertTrue($orig->isAfter(new DateTime('2010-01-01')));
    }

    public function testIsAfterPeriod()
    {
        $orig = Period::createFromDuration('2012-01-01', '1 MONTH');
        $alt  = Period::createFromDuration('2012-04-01', '2 MONTH');
        self::assertFalse($orig->isAfter($alt));
        self::assertTrue($alt->isAfter($orig));
    }

    public function testIsAfterDatetimeAbuts()
    {
        $orig = Period::createFromDuration('2012-01-01', '1 MONTH');
        self::assertTrue($orig->isBefore($orig->getEndDate()));
        self::assertFalse($orig->isAfter($orig->getStartDate()));
    }

    /**
     * @dataProvider provideAbutsData
     */
    public function testAbuts(Period $period, Period $arg, $expected)
    {
        self::assertSame($expected, $period->abuts($arg));
    }

    public function provideAbutsData()
    {
        return [
            'testAbutsReturnsTrueWithEqualDatePoints' => [
                Period::createFromDuration('2012-01-01', '1 MONTH'),
                Period::createFromDuration('2012-02-01', '2 MONTH'),
                true,
            ],
            'testAbutsReturnsFalseWithoutEqualDatePoints' => [
                Period::createFromDuration('2012-01-01', '1 MONTH'),
                Period::createFromDuration('2012-01-01', '2 MONTH'),
                false,
            ],
        ];
    }

    /**
     * @dataProvider provideOverlapsData
     */
    public function testOverlaps(Period $period, Period $arg, $expected)
    {
        self::assertSame($expected, $period->overlaps($arg));
    }

    public function provideOverlapsData()
    {
        return [
            'testOverlapsReturnsFalseWithAbutsPeriods' => [
                Period::createFromMonth(2014, 3),
                Period::createFromMonth(2014, 4),
                false,
            ],
            'testContainsReturnsFalseWithGappedPeriods' => [
                Period::createFromMonth(2014, 3),
                Period::createFromMonth(2013, 4),
                false,
            ],
            'testOverlapsReturnsTrue' => [
                Period::createFromMonth(2014, 3),
                Period::createFromDuration('2014-03-15', '3 WEEKS'),
                true,
            ],
            'testOverlapsReturnsTureWithSameDatepointsPeriods' => [
                Period::createFromMonth(2014, 3),
                new Period('2014-03-01', '2014-04-01'),
                true,
            ],
            'testOverlapsReturnsTrueContainedPeriods' => [
                Period::createFromMonth(2014, 3),
                Period::createFromDuration('2014-03-13', '2014-03-15'),
                true,
            ],
            'testOverlapsReturnsTrueContainedPeriodsBackward' => [
                Period::createFromDuration('2014-03-13', '2014-03-15'),
                Period::createFromMonth(2014, 3),
                true,
            ],
        ];
    }

    /**
     * @dataProvider provideContainsData
     */
    public function testContains(Period $period, $arg, $expected)
    {
        self::assertSame($expected, $period->contains($arg));
    }

    public function provideContainsData()
    {
        return [
            'testContainsReturnsTrueWithADateTimeInterfaceObject' => [
                Period::createFromMonth(2014, 3),
                new DateTime('2014-03-12'),
                true,
            ],
            'testContainsReturnsTrueWithPeriodObject' => [
                Period::createFromSemester(2014, 1),
                Period::createFromQuarter(2014, 1),
                true,
            ],
            'testContainsReturnsFalseWithADateTimeInterfaceObject' => [
                Period::createFromMonth(2014, 3),
                new DateTime('2015-03-12'),
                false,
            ],
            'testContainsReturnsFalseWithADateTimeInterfaceObjectAfterPeriod' => [
                Period::createFromMonth(2014, 3),
                '2012-03-12',
                false,
            ],
            'testContainsReturnsFalseWithADateTimeInterfaceObjectBeforePeriod' => [
                Period::createFromMonth(2014, 3),
                '2014-04-01',
                false,
            ],
            'testContainsReturnsFalseWithAbutsPeriods' => [
                Period::createFromQuarter(2014, 1),
                Period::createFromSemester(2014, 1),
                false,
            ],
            'testContainsReturnsTrueWithPeriodObjectWhichShareTheSameEndDate' => [
                Period::createFromYear(2015),
                Period::createFromMonth(2015, 12),
                true,
            ],
            'testContainsReturnsTrueWithAZeroDurationObject' => [
                new Period('2012-03-12', '2012-03-12'),
                '2012-03-12',
                true,
            ],
        ];
    }

    /**
     * @dataProvider provideCompareDurationData
     */
    public function testCompareDuration(Period $period1, Period $period2, $method, $expected)
    {
        self::assertSame($expected, $period1->$method($period2));
    }

    public function provideCompareDurationData()
    {
        return [
            'testDurationLessThan' => [
                Period::createFromDuration('2012-01-01', '1 WEEK'),
                Period::createFromDuration('2013-01-01', '1 MONTH'),
                'durationLessThan',
                true,
            ],
            'testDurationGreaterThanReturnsTrue' => [
                Period::createFromDuration('2012-01-01', '1 MONTH'),
                Period::createFromDuration('2012-01-01', '1 WEEK'),
                'durationGreaterThan',
                true,
            ],
            'testSameDurationAsReturnsTrueWithMicroseconds' => [
                new Period('2012-01-01 00:00:00', '2012-01-03 00:00:00'),
                new Period('2012-02-02 00:00:00', '2012-02-04 00:00:00'),
                'sameDurationAs',
                true,
            ],
            'testSameValueAsReturnsTrue' => [
                Period::createFromDuration('2012-01-01', '1 MONTH'),
                Period::createFromMonth(2012, 1),
                'equalsTo',
                true,
            ],
            'testSameValueAsReturnsFalse' => [
                Period::createFromDuration('2012-01-01', '1 MONTH'),
                Period::createFromDuration('2012-01-01', '1 WEEK'),
                'equalsTo',
                false,
            ],
            'testSameValueAsReturnsFalseArgumentOrderIndependent' => [
                Period::createFromDurationBeforeEnd('2012-01-01', '1 WEEK'),
                Period::createFromDurationBeforeEnd('2012-01-01', '1 MONTH'),
                'equalsTo',
                false,
            ],
        ];
    }

    public function testStartingOn()
    {
        $expected  = new DateTime('2012-03-02');
        $period = Period::createFromWeek(2014, 3);
        $newPeriod = $period->startingOn($expected);
        self::assertTrue($newPeriod->getStartDate() == $expected);
        self::assertEquals($period->getStartDate(), new DateTimeImmutable('2014-01-13'));
        self::assertSame($period->startingOn($period->getStartDate()), $period);
    }

    public function testStartingOnFailedWithWrongStartDate()
    {
        self::expectException(Exception::class);
        $period = Period::createFromWeek(2014, 3);
        $period->startingOn(new DateTime('2015-03-02'));
    }

    public function testEndingOn()
    {
        $expected  = new DateTime('2015-03-02');
        $period = Period::createFromWeek(2014, 3);
        $newPeriod = $period->endingOn($expected);
        self::assertTrue($newPeriod->getEndDate() == $expected);
        self::assertEquals($period->getEndDate(), new DateTimeImmutable('2014-01-20'));
        self::assertSame($period->endingOn($period->getEndDate()), $period);
    }

    public function testEndingOnFailedWithWrongEndDate()
    {
        self::expectException(Exception::class);
        $period = Period::createFromWeek(2014, 3);
        $period->endingOn(new DateTime('2012-03-02'));
    }

    public function testWithDuration()
    {
        $expected = Period::createFromMonth(2014, 3);
        $period = Period::createFromDuration('2014-03-01', '2 WEEKS');
        self::assertEquals($expected, $period->withDuration('1 MONTH'));
    }

    public function testWithDurationThrowsException()
    {
        self::expectException(Exception::class);
        $period = Period::createFromDuration('2014-03-01', '2 WEEKS');
        $interval = new DateInterval('P1D');
        $interval->invert = 1;
        $period->withDuration($interval);
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
        $expected = Period::createFromDuration('2014-03-01', '2 MONTHS');
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
        $orig = Period::createFromDuration('2012-01-01', '2 MONTH');
        $period = $orig->moveEndDate('-1 MONTH');
        self::assertSame(1, $orig->compareDuration($period));
        self::assertTrue($orig->durationGreaterThan($period));
        self::assertEquals($orig->getStartDate(), $period->getStartDate());
    }

    public function testAddThrowsException()
    {
        self::expectException(Exception::class);
        Period::createFromDuration('2012-01-01', '1 MONTH')->moveEndDate('-3 MONTHS');
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
        Period::createFromDuration('2012-01-01', '1 MONTH')->moveStartDate('3 MONTHS');
    }

    public function testSubThrowsException()
    {
        self::expectException(Exception::class);
        Period::createFromDuration('2012-01-01', '1 MONTH')->moveEndDate('-3 MONTHS');
    }

    public function testExpand()
    {
        $period = Period::createFromDay('2012-02-02')->expand(new DateInterval('P1D'));
        self::assertEquals(new DateTimeImmutable('2012-02-01'), $period->getStartDate());
        self::assertEquals(new DateTimeImmutable('2012-02-04'), $period->getEndDate());
    }

    public function testExpandRetunsSameInstance()
    {
        $period = Period::createFromDay('2012-02-02');
        self::assertSame($period->expand(new DateInterval('PT0S')), $period);
    }

    public function testShrink()
    {
        $dateInterval = new DateInterval('PT12H');
        $dateInterval->invert = 1;
        $period = Period::createFromDay('2012-02-02')->expand($dateInterval);
        self::assertEquals(new DateTimeImmutable('2012-02-02 12:00:00'), $period->getStartDate());
        self::assertEquals(new DateTimeImmutable('2012-02-02 12:00:00'), $period->getEndDate());
    }

    public function testExpandThrowsException()
    {
        self::expectException(Exception::class);
        $dateInterval = new DateInterval('P1D');
        $dateInterval->invert = 1;
        $period = Period::createFromDay('2012-02-02')->expand($dateInterval);
    }

    public function testDateIntervalDiff()
    {
        $orig = Period::createFromDuration('2012-01-01', '1 HOUR');
        $alt = Period::createFromDuration('2012-01-01', '2 HOUR');
        self::assertInstanceOf(DateInterval::class, $orig->dateIntervalDiff($alt));
    }

    public function testTimeIntervalDiff()
    {
        $orig = Period::createFromDuration('2012-01-01', '1 HOUR');
        $alt = Period::createFromDuration('2012-01-01', '2 HOUR');
        self::assertEquals(-3600, $orig->timestampIntervalDiff($alt));
    }

    public function testDateIntervalDiffPositionIrrelevant()
    {
        $orig = Period::createFromDuration('2012-01-01', '1 HOUR');
        $alt = Period::createFromDuration('2012-01-01', '2 HOUR');
        $fromOrig = $orig->dateIntervalDiff($alt);
        $fromOrig->invert = 1;
        self::assertEquals($fromOrig, $alt->dateIntervalDiff($orig));
    }

    public function testIntersect()
    {
        $orig = Period::createFromDuration('2011-12-01', '5 MONTH');
        $alt = Period::createFromDuration('2012-01-01', '2 MONTH');

        self::assertInstanceOf(Period::class, $orig->intersect($alt));
    }

    public function testIntersectThrowsExceptionWithNoOverlappingTimeRange()
    {
        self::expectException(Exception::class);
        $orig = Period::createFromDuration('2013-01-01', '1 MONTH');
        $orig->intersect(Period::createFromDuration('2012-01-01', '2 MONTH'));
    }

    public function testGap()
    {
        $orig = Period::createFromDuration('2011-12-01', '2 MONTHS');
        $alt = Period::createFromDuration('2012-06-15', '3 MONTHS');
        $res = $orig->gap($alt);
        self::assertInstanceOf(Period::class, $res);
        self::assertEquals($orig->getEndDate(), $res->getStartDate());
        self::assertEquals($alt->getStartDate(), $res->getEndDate());
        self::assertTrue($res->equalsTo($alt->gap($orig)));
    }

    public function testGapThrowsExceptionWithOverlapsPeriod()
    {
        self::expectException(Exception::class);
        $orig = Period::createFromDuration('2011-12-01', '5 MONTH');
        $orig->gap(Period::createFromDuration('2012-01-01', '2 MONTH'));
    }

    public function testGapWithSameStartingPeriod()
    {
        self::expectException(Exception::class);
        $orig = Period::createFromDuration('2012-12-01', '5 MONTH');
        $orig->gap(Period::createFromDuration('2012-12-01', '2 MONTH'));
    }

    public function testGapWithSameEndingPeriod()
    {
        self::expectException(Exception::class);
        $orig = Period::createFromDurationBeforeEnd('2012-12-01', '5 MONTH');
        $orig->gap(Period::createFromDurationBeforeEnd('2012-12-01', '2 MONTH'));
    }

    public function testGapWithAdjacentPeriod()
    {
        $orig = Period::createFromDurationBeforeEnd('2012-12-01', '5 MONTH');
        $alt  = Period::createFromDuration($orig->getEndDate(), '1 MINUTE');
        $gap  = $orig->gap($alt);
        self::assertInstanceOf(Period::class, $gap);
        self::assertEquals(0, $gap->getTimestampInterval());
    }

    public function testDiffThrowsException()
    {
        self::expectException(Exception::class);
        Period::createFromYear(2015)->diff(Period::createFromYear(2013));
    }

    public function testDiffWithEqualsPeriod()
    {
        $period = Period::createFromYear(2013);
        $alt = Period::createFromDuration('2013-01-01', '1 YEAR');
        $res = $alt->diff($period);
        self::assertCount(0, $res);
        self::assertEquals($res, $period->diff($alt));
    }

    public function testDiffWithPeriodSharingStartingDatepoints()
    {
        $period = Period::createFromYear(2013);
        $alt = Period::createFromDuration('2013-01-01', '3 MONTHS');
        $res = $alt->diff($period);
        self::assertCount(1, $res);
        self::assertInstanceOf(Period::class, $res[0]);
        self::assertEquals(new DateTimeImmutable('2013-04-01'), $res[0]->getStartDate());
        self::assertEquals(new DateTimeImmutable('2014-01-01'), $res[0]->getEndDate());
        self::assertEquals($res, $period->diff($alt));
    }

    public function testDiffWithPeriodSharingEndingDatepoints()
    {
        $period = Period::createFromYear(2013);
        $alt = Period::createFromDurationBeforeEnd('2014-01-01', '3 MONTHS');
        $res = $alt->diff($period);
        self::assertCount(1, $res);
        self::assertInstanceOf(Period::class, $res[0]);
        self::assertEquals(new DateTimeImmutable('2013-01-01'), $res[0]->getStartDate());
        self::assertEquals(new DateTimeImmutable('2013-10-01'), $res[0]->getEndDate());
        self::assertEquals($res, $period->diff($alt));
    }

    public function testDiffWithOverlapsPeriod()
    {
        $period = Period::createFromDuration('2013-01-01 10:00:00', '3 HOURS');
        $alt = Period::createFromDuration('2013-01-01 11:00:00', '3 HOURS');
        $res = $alt->diff($period);
        self::assertCount(2, $res);
        foreach ($res as $periodDiff) {
            self::assertEquals(3600, $periodDiff->getTimestampInterval());
        }
        self::assertEquals($res, $period->diff($alt));
    }

    public function testMove()
    {
        $period = new Period('2016-01-01 15:32:12', '2016-01-15 12:00:01');
        $moved = $period->move(new DateInterval('P1D'));
        self::assertEquals(new Period('2016-01-02 15:32:12', '2016-01-16 12:00:01'), $moved);
        self::assertSame($period->move(new DateInterval('PT0S')), $period);
    }

    public function testMoveSupportStringIntervals()
    {
        $period = new Period('2016-01-01 15:32:12', '2016-01-15 12:00:01');
        $advanced = $period->move('1 DAY');
        self::assertEquals(new Period('2016-01-02 15:32:12', '2016-01-16 12:00:01'), $advanced);
    }

    public function testMoveWithInvertedInterval()
    {
        $period = new Period('2016-01-02 15:32:12', '2016-01-16 12:00:01');
        $lessOneDay = new DateInterval('P1D');
        $lessOneDay->invert = 1;
        $moved = $period->move($lessOneDay);
        self::assertEquals(new Period('2016-01-01 15:32:12', '2016-01-15 12:00:01'), $moved);
    }

    public function testMoveWithInvertedStringInterval()
    {
        $period = new Period('2016-01-02 15:32:12', '2016-01-16 12:00:01');
        $moved = $period->move('- 1 day');
        self::assertEquals(new Period('2016-01-01 15:32:12', '2016-01-15 12:00:01'), $moved);
    }
}
