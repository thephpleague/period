<?php

/**
 * League.Period (https://period.thephpleague.com).
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
use Exception as PhpException;
use League\Period\Duration;
use League\Period\Exception;
use PHPUnit\Framework\TestCase;
use TypeError;
use function get_object_vars;
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
     *
     * @param string|int|DateTimeInterface $input
     */
    public function testDatepoint(DateTimeImmutable $expected, $input): void
    {
        self::assertEquals($expected, datepoint($input));
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

    public function testDatepointThrowsTypeError(): void
    {
        self::expectException(TypeError::class);
        datepoint([]);
    }

    /**
     * @dataProvider durationProvider
     *
     * @param mixed                   $expected DateInterval object
     * @param int|DateInterval|string $duration
     */
    public function testDuration($expected, $duration): void
    {
        self::assertEquals(get_object_vars($expected), get_object_vars(duration($duration)));
    }

    public function durationProvider(): array
    {
        return [
            'DateInterval' => [
                'expected' => new DateInterval('P1D'),
                'input' => new DateInterval('P1D'),
            ],
            'string' => [
                'expected' => new Duration('P1D'),
                'input' => '+1 DAY',
            ],
            'int' => [
                'expected' => new DateInterval('PT30S'),
                'input' => 30,
            ],
        ];
    }

    public function testDurationThrowsTypeError(): void
    {
        self::expectException(TypeError::class);
        duration([]);
    }

    /**
     * @dataProvider provideIntervalAfterData
     *
     * @param int|DateInterval|string $duration
     */
    public function testIntervalAfter(string $startDate, string $endDate, $duration): void
    {
        $period = interval_after($startDate, $duration);
        self::assertEquals(new DateTimeImmutable($startDate), $period->getStartDate());
        self::assertEquals(new DateTimeImmutable($endDate), $period->getEndDate());
    }

    public function provideIntervalAfterData(): array
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

    public function testIntervalAfterWithInvalidInteger(): void
    {
        self::expectException(PhpException::class);
        interval_after('2014-01-01', -1);
    }

    public function testIntervalAfterFailedWithOutofRangeInterval(): void
    {
        self::expectException(Exception::class);
        interval_after(new DateTime('2012-01-12'), '-1 DAY');
    }

    public function testIntervalAfterFailedWithInvalidInterval(): void
    {
        self::expectException(TypeError::class);
        interval_after(new DateTime('2012-01-12'), []);
    }

    /**
     * @dataProvider intervalBeforeProviderData
     *
     * @param int|DateInterval|string $duration
     */
    public function testIntervalBefore(string $startDate, string $endDate, $duration): void
    {
        $period = interval_before($endDate, $duration);
        self::assertEquals(new DateTimeImmutable($startDate), $period->getStartDate());
        self::assertEquals(new DateTimeImmutable($endDate), $period->getEndDate());
    }

    public function intervalBeforeProviderData(): array
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

    public function testIntervalBeforeFailedWithOutofRangeInterval(): void
    {
        self::expectException(Exception::class);
        interval_before(new DateTime('2012-01-12'), '-1 DAY');
    }

    public function testIntervalAround(): void
    {
        $date = '2012-06-05';
        $duration = '1 WEEK';

        $period = interval_around($date, $duration);
        self::assertTrue($period->contains($date));
        self::assertEquals(datepoint($date)->sub(duration($duration)), $period->getStartDate());
        self::assertEquals(datepoint($date)->add(duration($duration)), $period->getEndDate());
    }

    public function testIntervalAroundThrowsException(): void
    {
        self::expectException(Exception::class);
        interval_around(new DateTime('2012-06-05'), '-1 DAY');
    }

    public function testIntervalFromDatePeriod(): void
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

    public function testIntervalFromDatePeriodThrowsException(): void
    {
        self::expectException(TypeError::class);
        interval_from_dateperiod(new DatePeriod('R4/2012-07-01T00:00:00Z/P7D'));
    }

    public function testIsoWeek(): void
    {
        $period = iso_week(2014, 3);
        self::assertEquals(new DateTimeImmutable('2014-01-13'), $period->getStartDate());
        self::assertEquals(new DateTimeImmutable('2014-01-20'), $period->getEndDate());
    }

    public function testIsoWeekFailedWithInvalidYearIndex(): void
    {
        self::expectException(TypeError::class);
        iso_week([]);
    }

    public function testIsoWeekWithDefaultArgument(): void
    {
        self::assertTrue(iso_week(2014)->equals(iso_week(2014, 1)));
    }

    public function testMonth(): void
    {
        $period = month(2014, 3);
        self::assertEquals(new DateTimeImmutable('2014-03-01'), $period->getStartDate());
        self::assertEquals(new DateTimeImmutable('2014-04-01'), $period->getEndDate());
    }

    public function testMonthFailedWithInvalidYearIndex(): void
    {
        self::expectException(TypeError::class);
        month([]);
    }

    public function testMonthWithDefaultArgument(): void
    {
        self::assertTrue(month(2014)->equals(month(2014, 1)));
    }

    public function testQuarter(): void
    {
        $period = quarter(2014, 3);
        self::assertEquals(new DateTimeImmutable('2014-07-01'), $period->getStartDate());
        self::assertEquals(new DateTimeImmutable('2014-10-01'), $period->getEndDate());
    }

    public function testQuarterWithDefaultArgument(): void
    {
        self::assertEquals(quarter(2014), quarter(2014, 1));
    }

    public function testQuarterThrowsWithInvalidYearType(): void
    {
        self::expectException(TypeError::class);
        quarter([]);
    }

    public function testSemester(): void
    {
        $period = semester(2014, 2);
        self::assertEquals(new DateTimeImmutable('2014-07-01'), $period->getStartDate());
        self::assertEquals(new DateTimeImmutable('2015-01-01'), $period->getEndDate());
    }

    public function testSemesterWithDefaultArgument(): void
    {
        self::assertEquals(semester(2014), semester(2014, 1));
    }

    public function testSemesterThrowsWithInvalidYearType(): void
    {
        self::expectException(TypeError::class);
        semester([]);
    }

    public function testYear(): void
    {
        $period = year(2014);
        self::assertEquals(new DateTimeImmutable('2014-01-01'), $period->getStartDate());
        self::assertEquals(new DateTimeImmutable('2015-01-01'), $period->getEndDate());
    }

    public function testISOYear(): void
    {
        $period = iso_year(2014);
        $interval = iso_year('2014-06-25');
        self::assertEquals(new DateTimeImmutable('2013-12-30'), $period->getStartDate());
        self::assertEquals(new DateTimeImmutable('2014-12-29'), $period->getEndDate());
        self::assertTrue($period->equals($interval));
    }

    public function testDay(): void
    {
        $period = day(new ExtendedDate('2008-07-01T22:35:17.123456+08:00'));
        self::assertEquals(new DateTimeImmutable('2008-07-01T00:00:00+08:00'), $period->getStartDate());
        self::assertEquals(new DateTimeImmutable('2008-07-02T00:00:00+08:00'), $period->getEndDate());
        self::assertEquals('+08:00', $period->getStartDate()->format('P'));
        self::assertEquals('+08:00', $period->getEndDate()->format('P'));
        self::assertInstanceOf(ExtendedDate::class, $period->getStartDate());
        self::assertInstanceOf(ExtendedDate::class, $period->getEndDate());
    }

    public function testAlternateDay(): void
    {
        $period = day('2008-07-01');
        $alt_period = day(2008, 7, 1);
        self::assertEquals($period, $alt_period);
    }

    public function testDayWithDefaultArgument(): void
    {
        self::assertEquals(day(2008), day(2008, 1, 1));
        self::assertEquals(day(2008, 1), day(2008, 1, 1));
    }

    public function testHour(): void
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

    public function testAlternateHour(): void
    {
        $period = hour('2008-07-01 12:03:04');
        $alt_period = hour(2008, 7, 1, 12);
        self::assertEquals($period, $alt_period);
    }

    public function testHourWithDefaultArgument(): void
    {
        $default = hour(2008, 1, 1, 0);
        self::assertEquals(hour(2008), $default);
        self::assertEquals(hour(2008, 1), $default);
        self::assertEquals(hour(2008, 1, 1), $default);
    }

    public function testMinute(): void
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

    public function testAlternateMinute(): void
    {
        $period = minute('2008-07-01 12:03:04');
        $alt_period = minute(2008, 7, 1, 12, 3);
        self::assertEquals($period, $alt_period);
    }

    public function testMinuteWithDefaultArgument(): void
    {
        $default = minute(2008, 1, 1, 0, 0);
        self::assertEquals(minute(2008), $default);
        self::assertEquals(minute(2008, 1), $default);
        self::assertEquals(minute(2008, 1, 1), $default);
        self::assertEquals(minute(2008, 1, 1, 0), $default);
    }

    public function testSecond(): void
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

    public function testAlternateSecond(): void
    {
        $period = second('2008-07-01 12:03:04');
        $alt_period = second(2008, 7, 1, 12, 3, 4);
        self::assertEquals($period, $alt_period);
    }

    public function testSecondWithDefaultArgument(): void
    {
        $default = second(2008, 1, 1, 0, 0, 0);
        self::assertEquals(second(2008), $default);
        self::assertEquals(second(2008, 1), $default);
        self::assertEquals(second(2008, 1, 1), $default);
        self::assertEquals(second(2008, 1, 1, 0), $default);
        self::assertEquals(second(2008, 1, 1, 0, 0), $default);
    }

    public function testInstant(): void
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

    public function testAlternateInstant(): void
    {
        $period = instant('2008-07-01 12:03:04');
        $alt_period = instant(2008, 7, 1, 12, 3, 4);
        self::assertEquals($period, $alt_period);
    }

    public function testInstantWithDefaultArgument(): void
    {
        $default = instant(2008, 1, 1, 0, 0, 0, 0);
        self::assertEquals(instant(2008), $default);
        self::assertEquals(instant(2008, 1), $default);
        self::assertEquals(instant(2008, 1, 1), $default);
        self::assertEquals(instant(2008, 1, 1, 0), $default);
        self::assertEquals(instant(2008, 1, 1, 0, 0), $default);
        self::assertEquals(instant(2008, 1, 1, 0, 0, 0), $default);
    }

    public function testCreateFromWithDateTimeInterface(): void
    {
        self::assertTrue(iso_week('2008W27')->equals(iso_week(2008, 27)));
        self::assertTrue(month('2008-07')->equals(month(2008, 7)));
        self::assertTrue(quarter('2008-02')->equals(quarter(2008, 1)));
        self::assertTrue(semester('2008-10')->equals(semester(2008, 2)));
        self::assertTrue(year('2008-01')->equals(year(2008)));
    }

    public function testMonthWithDateTimeInterface(): void
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

    public function testYearWithDateTimeInterface(): void
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
