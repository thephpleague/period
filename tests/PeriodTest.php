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
use DateTimeZone;
use Exception as PhpException;
use League\Period\Exception;
use League\Period\Interval;
use League\Period\Period;
use TypeError;

class PeriodTest extends IntervalTest
{
    protected function createInterval(DateTimeInterface $startDate, DateTimeInterface $endDate): Interval
    {
        return new Period($startDate, $endDate);
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


    public function testCreateFromInstant()
    {
        $today = new ExtendedDate('2008-07-01T22:35:17.123456+08:00');
        $period = Period::createFromInstant($today);
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
