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
use function League\Period\interval_before;
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

    public function testISOWeek()
    {
        $period = iso_week(2014, 3);
        self::assertEquals(new DateTimeImmutable('2014-01-13'), $period->getStartDate());
        self::assertEquals(new DateTimeImmutable('2014-01-20'), $period->getEndDate());
    }

    public function testISOWeekFailedWithLowInvalidIndex()
    {
        self::expectException(Exception::class);
        iso_week(2014, 0);
    }

    public function testISOWeekFailedWithHighInvalidIndex()
    {
        self::expectException(Exception::class);
        iso_week(2014, 54);
    }

    public function testISOWeekFailedWithInvalidYearIndex()
    {
        self::expectException(TypeError::class);
        iso_week([], 1);
    }

    public function testISOWeekFailedWithMissingSemesterValue()
    {
        self::expectException(Exception::class);
        iso_week(2014, null);
    }

    public function testMonth()
    {
        $period = month(2014, 3);
        self::assertEquals(new DateTimeImmutable('2014-03-01'), $period->getStartDate());
        self::assertEquals(new DateTimeImmutable('2014-04-01'), $period->getEndDate());
    }

    public function testMonthFailedWithHighInvalidIndex()
    {
        self::expectException(Exception::class);
        month(2014, 13);
    }

    public function testMonthFailedWithLowInvalidIndex()
    {
        self::expectException(Exception::class);
        month(2014, 0);
    }

    public function testMonthFailedWithInvalidYearIndex()
    {
        self::expectException(TypeError::class);
        month([], 1);
    }

    public function testMonthFailedWithMissingSemesterValue()
    {
        self::expectException(Exception::class);
        month(2014, null);
    }

    public function testQuarter()
    {
        $period = quarter(2014, 3);
        self::assertEquals(new DateTimeImmutable('2014-07-01'), $period->getStartDate());
        self::assertEquals(new DateTimeImmutable('2014-10-01'), $period->getEndDate());
    }

    public function testQuarterFailedWithHighInvalidIndex()
    {
        self::expectException(Exception::class);
        quarter(2014, 5);
    }

    public function testQuarterFailedWithLowInvalidIndex()
    {
        self::expectException(Exception::class);
        quarter(2014, 0);
    }

    public function testQuarterFailedWithInvalidYearIndex()
    {
        self::expectException(TypeError::class);
        quarter([], 1);
    }

    public function testQuarterFailedWithMissingSemesterValue()
    {
        self::expectException(Exception::class);
        quarter(2014, null);
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

    public function testSemesterFailedWithMissingSemesterValue()
    {
        self::expectException(Exception::class);
        semester(2014, null);
    }

    public function testSemesterFailedWithLowInvalidIndex()
    {
        self::expectException(Exception::class);
        semester(2014, 0);
    }

    public function testSemesterFailedWithHighInvalidIndex()
    {
        self::expectException(Exception::class);
        semester(2014, 3);
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

    public function testSecond()
    {
        $today = new ExtendedDate('2008-07-01T22:35:17.123456+08:00');
        $period = second($today);
        self::assertEquals(new DateTimeImmutable('2008-07-01T22:35:17+08:00'), $period->getStartDate());
        self::assertEquals(new DateTimeImmutable('2008-07-01T22:35:18+08:00'), $period->getEndDate());
        self::assertEquals('+08:00', $period->getStartDate()->format('P'));
        self::assertEquals('+08:00', $period->getEndDate()->format('P'));
        self::assertInstanceOf(ExtendedDate::class, $period->getStartDate());
        self::assertInstanceOf(ExtendedDate::class, $period->getEndDate());
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
