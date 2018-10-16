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

use ArgumentCountError;
use DateInterval;
use DatePeriod;
use DateTime;
use DateTimeImmutable;
use Exception as PhpException;
use League\Period\Exception;
use PHPUnit\Framework\TestCase;
use TypeError;
use function League\Period\datepoint;
use function League\Period\day;
use function League\Period\duration;
use function League\Period\hour;
use function League\Period\instant;
use function League\Period\interval_after;
use function League\Period\interval_around;
use function League\Period\interval_before;
use function League\Period\interval_from_dateperiod;
use function League\Period\iso_week;
use function League\Period\iso_year;
use function League\Period\minute;
use function League\Period\month;
use function League\Period\quarter;
use function League\Period\second;
use function League\Period\semester;
use function League\Period\year;

class FunctionTest extends TestCase
{
    /**
     * @dataProvider datepointProvider
     */
    public function testDatepoint(DateTimeImmutable $expected, $input)
    {
        $datepoint = datepoint($input);
        self::assertInstanceOf(DateTimeImmutable::class, $datepoint);
        self::assertEquals($expected, $datepoint);
    }

    public function datepointProvider(): array
    {
        $date = '2012-01-05';
        $expected = new DateTimeImmutable($date);
        return [
            'string' => [
                'expected' => $expected,
                'input' => $date,
            ],
            'DateTime' => [
                'expected' => $expected,
                'input' => new DateTime($date),
            ],
            'DateTimeImmutable' => [
                'expected' => $expected,
                'input' => $expected,
            ],
            'int' => [
                'expected' => $expected,
                'input' => $expected->getTimestamp(),
            ],
        ];
    }

    public function testDatepointThrowsTypeError()
    {
        self::expectException(TypeError::class);
        datepoint([]);
    }

    public function testDatepointThrowsException()
    {
        self::expectException(ArgumentCountError::class);
        datepoint(2018, 12);
    }

    /**
     * @dataProvider durationProvider
     */
    public function testDuration(DateInterval $expected, $input)
    {
        $duration = duration($input);
        self::assertInstanceOf(DateInterval::class, $duration);
        self::assertEquals($expected, $duration);
    }

    public function durationProvider(): array
    {
        return [
            'DateInterval' => [
                'expected' => new DateInterval('P1D'),
                'input' => new DateInterval('P1D'),
            ],
            'string' => [
                'expected' => new DateInterval('P1D'),
                'input' => '+1 DAY',
            ],
            'int' => [
                'expected' => new DateInterval('PT30S'),
                'input' => 30,
            ],
        ];
    }

    public function testDurationThrowsTypeError()
    {
        self::expectException(TypeError::class);
        duration([]);
    }

    /**
     * @dataProvider provideIntervalAfterData
     */
    public function testIntervalAfter($startDate, $endDate, $duration)
    {
        $period = interval_after($startDate, $duration);
        self::assertEquals(new DateTimeImmutable($startDate), $period->getStartDate());
        self::assertEquals(new DateTimeImmutable($endDate), $period->getEndDate());
    }

    public function provideIntervalAfterData()
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
                '2015-01-01 10:00:00', '2015-01-01 11:00:00', hour('2012-01-03 12:00:00'),
            ],
        ];
    }

    public function testIntervalAfterWithInvalidInteger()
    {
        self::expectException(PhpException::class);
        interval_after('2014-01-01', -1);
    }

    public function testIntervalAfterFailedWithOutofRangeInterval()
    {
        self::expectException(Exception::class);
        interval_after(new DateTime('2012-01-12'), '-1 DAY');
    }

    public function testIntervalAfterFailedWithInvalidInterval()
    {
        self::expectException(TypeError::class);
        interval_after(new DateTime('2012-01-12'), []);
    }

    /**
     * @dataProvider intervalBeforeProviderData
     */
    public function testIntervalBefore($startDate, $endDate, $duration)
    {
        $period = interval_before($endDate, $duration);
        self::assertEquals(new DateTimeImmutable($startDate), $period->getStartDate());
        self::assertEquals(new DateTimeImmutable($endDate), $period->getEndDate());
    }

    public function intervalBeforeProviderData()
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

    public function testIntervalBeforeFailedWithOutofRangeInterval()
    {
        self::expectException(Exception::class);
        interval_before(new DateTime('2012-01-12'), '-1 DAY');
    }

    public function testIntervalAround()
    {
        $date = '2012-06-05';
        $duration = '1 WEEK';

        $period = interval_around($date, $duration);
        self::assertTrue($period->contains($date));
        self::assertEquals(datepoint($date)->sub(duration($duration)), $period->getStartDate());
        self::assertEquals(datepoint($date)->add(duration($duration)), $period->getEndDate());
    }

    public function testIntervalAroundThrowsException()
    {
        self::expectException(Exception::class);
        interval_around(new DateTime('2012-06-05'), '-1 DAY');
    }

    public function testIntervalFromDatePeriod()
    {
        $datePeriod = new DatePeriod(
            new DateTime('2016-05-16T00:00:00Z'),
            new DateInterval('P1D'),
            new DateTime('2016-05-20T00:00:00Z')
        );
        $period = interval_from_dateperiod($datePeriod);
        self::assertEquals($datePeriod->getStartDate(), $period->getStartDate());
        self::assertEquals($datePeriod->getEndDate(), $period->getEndDate());
    }

    public function testIntervalFromDatePeriodThrowsException()
    {
        self::expectException(Exception::class);
        interval_from_dateperiod(new DatePeriod('R4/2012-07-01T00:00:00Z/P7D'));
    }

    public function testIsoWeek()
    {
        $period = iso_week(2014, 3);
        self::assertEquals(new DateTimeImmutable('2014-01-13'), $period->getStartDate());
        self::assertEquals(new DateTimeImmutable('2014-01-20'), $period->getEndDate());
    }

    public function testIsoWeekFailedWithInvalidYearIndex()
    {
        self::expectException(TypeError::class);
        iso_week([], 1);
    }

    public function testIsoWeekWithMissingWeekValue()
    {
        self::assertTrue(iso_week(2014)->equals(iso_week(2014, 1)));
    }

    public function testMonth()
    {
        $period = month(2014, 3);
        self::assertEquals(new DateTimeImmutable('2014-03-01'), $period->getStartDate());
        self::assertEquals(new DateTimeImmutable('2014-04-01'), $period->getEndDate());
    }

    public function testMonthFailedWithInvalidYearIndex()
    {
        self::expectException(TypeError::class);
        month([], 1);
    }

    public function testMonthWithMissingValue()
    {
        self::assertTrue(month(2014)->equals(month(2014, 1)));
    }

    public function testQuarter()
    {
        $period = quarter(2014, 3);
        self::assertEquals(new DateTimeImmutable('2014-07-01'), $period->getStartDate());
        self::assertEquals(new DateTimeImmutable('2014-10-01'), $period->getEndDate());
    }

    public function testQuarterFailedWithInvalidYearIndex()
    {
        self::expectException(TypeError::class);
        quarter([], 1);
    }

    public function testQuarterWithMissingValue()
    {
        self::assertEquals(quarter(2014), quarter(2014, 1));
    }

    public function testSemester()
    {
        $period = semester(2014, 2);
        self::assertEquals(new DateTimeImmutable('2014-07-01'), $period->getStartDate());
        self::assertEquals(new DateTimeImmutable('2015-01-01'), $period->getEndDate());
    }

    public function testSemesterFailedWithInvalidYearIndex()
    {
        self::expectException(TypeError::class);
        semester([], 1);
    }

    public function testSemesterFailedWithMissingValue()
    {
        self::assertEquals(semester(2014), semester(2014, 1));
    }

    public function testYear()
    {
        $period = year(2014);
        self::assertEquals(new DateTimeImmutable('2014-01-01'), $period->getStartDate());
        self::assertEquals(new DateTimeImmutable('2015-01-01'), $period->getEndDate());
    }

    public function testISOYear()
    {
        $period = iso_year(2014);
        $interval = iso_year('2014-06-25');
        self::assertEquals(new DateTimeImmutable('2013-12-30'), $period->getStartDate());
        self::assertEquals(new DateTimeImmutable('2014-12-29'), $period->getEndDate());
        self::assertTrue($period->equals($interval));
    }

    public function testDay()
    {
        $period = day(new ExtendedDate('2008-07-01T22:35:17.123456+08:00'));
        self::assertEquals(new DateTimeImmutable('2008-07-01T00:00:00+08:00'), $period->getStartDate());
        self::assertEquals(new DateTimeImmutable('2008-07-02T00:00:00+08:00'), $period->getEndDate());
        self::assertEquals('+08:00', $period->getStartDate()->format('P'));
        self::assertEquals('+08:00', $period->getEndDate()->format('P'));
        self::assertInstanceOf(ExtendedDate::class, $period->getStartDate());
        self::assertInstanceOf(ExtendedDate::class, $period->getEndDate());
    }

    public function testAlternateDay()
    {
        $period = day('2008-07-01');
        $alt_period = day(2008, 7, 1);
        self::assertEquals($period, $alt_period);
    }

    public function testHour()
    {
        $today = new ExtendedDate('2008-07-01T22:35:17.123456+08:00');
        $period = hour($today);
        self::assertEquals(new DateTimeImmutable('2008-07-01T22:00:00+08:00'), $period->getStartDate());
        self::assertEquals(new DateTimeImmutable('2008-07-01T23:00:00+08:00'), $period->getEndDate());
        self::assertEquals('+08:00', $period->getStartDate()->format('P'));
        self::assertEquals('+08:00', $period->getEndDate()->format('P'));
        self::assertInstanceOf(ExtendedDate::class, $period->getStartDate());
        self::assertInstanceOf(ExtendedDate::class, $period->getEndDate());
    }

    public function testAlternateHour()
    {
        $period = hour('2008-07-01 12:03:04');
        $alt_period = hour(2008, 7, 1, 12);
        self::assertEquals($period, $alt_period);
    }

    public function testMinute()
    {
        $today = new ExtendedDate('2008-07-01T22:35:17.123456+08:00');
        $period = minute($today);
        self::assertEquals(new DateTimeImmutable('2008-07-01T22:35:00+08:00'), $period->getStartDate());
        self::assertEquals(new DateTimeImmutable('2008-07-01T22:36:00+08:00'), $period->getEndDate());
        self::assertEquals('+08:00', $period->getStartDate()->format('P'));
        self::assertEquals('+08:00', $period->getEndDate()->format('P'));
        self::assertInstanceOf(ExtendedDate::class, $period->getStartDate());
        self::assertInstanceOf(ExtendedDate::class, $period->getEndDate());
    }

    public function testAlternateMinute()
    {
        $period = minute('2008-07-01 12:03:04');
        $alt_period = minute(2008, 7, 1, 12, 3);
        self::assertEquals($period, $alt_period);
    }

    public function testSecond()
    {
        $today = new ExtendedDate('2008-07-01T22:35:17.123456+08:00');
        $period = second($today);
        self::assertTrue($period->contains($today));
        self::assertTrue($today >= $period->getStartDate());
        self::assertEquals(new DateTimeImmutable('2008-07-01T22:35:17+08:00'), $period->getStartDate());
        self::assertEquals(new DateTimeImmutable('2008-07-01T22:35:18+08:00'), $period->getEndDate());
        self::assertEquals('+08:00', $period->getStartDate()->format('P'));
        self::assertEquals('+08:00', $period->getEndDate()->format('P'));
        self::assertInstanceOf(ExtendedDate::class, $period->getStartDate());
        self::assertInstanceOf(ExtendedDate::class, $period->getEndDate());
    }

    public function testAlternateSecond()
    {
        $period = second('2008-07-01 12:03:04');
        $alt_period = second(2008, 7, 1, 12, 3, 4);
        self::assertEquals($period, $alt_period);
    }

    public function testInstant()
    {
        $today = new ExtendedDate('2008-07-01T22:35:17.123456+08:00');
        $period = instant($today);
        self::assertEquals($today, $period->getStartDate());
        self::assertEquals($today, $period->getEndDate());
        self::assertEquals('+08:00', $period->getStartDate()->format('P'));
        self::assertEquals('+08:00', $period->getEndDate()->format('P'));
        self::assertInstanceOf(ExtendedDate::class, $period->getStartDate());
        self::assertInstanceOf(ExtendedDate::class, $period->getEndDate());
        self::assertEquals(new DateInterval('P0D'), $period->getDateInterval());
    }

    public function testAlternateInstant()
    {
        $period = instant('2008-07-01 12:03:04');
        $alt_period = instant(2008, 7, 1, 12, 3, 4);
        self::assertEquals($period, $alt_period);
    }

    public function testCreateFromWithDateTimeInterface()
    {
        self::assertTrue(iso_week('2008W27')->equals(iso_week(2008, 27)));
        self::assertTrue(month('2008-07')->equals(month(2008, 7)));
        self::assertTrue(quarter('2008-02')->equals(quarter(2008, 1)));
        self::assertTrue(semester('2008-10')->equals(semester(2008, 2)));
        self::assertTrue(year('2008-01')->equals(year(2008)));
    }

    public function testMonthWithDateTimeInterface()
    {
        $today = new ExtendedDate('2008-07-01T22:35:17.123456+08:00');
        $period = month($today);
        self::assertEquals(new DateTimeImmutable('2008-07-01T00:00:00+08:00'), $period->getStartDate());
        self::assertEquals(new DateTimeImmutable('2008-08-01T00:00:00+08:00'), $period->getEndDate());
        self::assertEquals('+08:00', $period->getStartDate()->format('P'));
        self::assertEquals('+08:00', $period->getEndDate()->format('P'));
        self::assertInstanceOf(ExtendedDate::class, $period->getStartDate());
        self::assertInstanceOf(ExtendedDate::class, $period->getEndDate());
    }

    public function testYearWithDateTimeInterface()
    {
        $today = new ExtendedDate('2008-07-01T22:35:17.123456+08:00');
        $period = year($today);
        self::assertEquals(new DateTimeImmutable('2008-01-01T00:00:00+08:00'), $period->getStartDate());
        self::assertEquals(new DateTimeImmutable('2009-01-01T00:00:00+08:00'), $period->getEndDate());
        self::assertEquals('+08:00', $period->getStartDate()->format('P'));
        self::assertEquals('+08:00', $period->getEndDate()->format('P'));
        self::assertInstanceOf(ExtendedDate::class, $period->getStartDate());
        self::assertInstanceOf(ExtendedDate::class, $period->getEndDate());
    }
}
