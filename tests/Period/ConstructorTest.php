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
use DateTimeZone;
use Exception as PhpException;
use League\Period\Datepoint;
use League\Period\Duration;
use League\Period\Exception;
use League\Period\Period;
use LeagueTest\Period\ExtendedDate;
use LeagueTest\Period\TestCase;
use TypeError;

/**
 * @coversDefaultClass \League\Period\Period
 */
class ConstructorTest extends TestCase
{
    public function testConstructorThrowExceptionIfUnknownBoundPeriodDay(): void
    {
        self::expectException(Exception::class);
        new Period(new DateTime('2014-01-13'), new DateTime('2014-01-20'), 'foobar');
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
     * @dataProvider provideIntervalAfterData
     *
     * @param int|DateInterval|string $duration
     */
    public function testIntervalAfter(string $startDate, string $endDate, $duration): void
    {
        $period = Period::after($startDate, $duration);
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
                '2015-01-01 10:00:00', '2015-01-01 11:00:00', Datepoint::create('2012-01-03 12:00:00')->getHour(),
            ],
        ];
    }

    public function testIntervalAfterWithInvalidInteger(): void
    {
        self::expectException(PhpException::class);
        Period::after('2014-01-01', -1);
    }

    public function testIntervalAfterFailedWithOutofRangeInterval(): void
    {
        self::expectException(Exception::class);
        Period::after(new DateTime('2012-01-12'), '-1 DAY');
    }

    public function testIntervalAfterFailedWithInvalidInterval(): void
    {
        self::expectException(TypeError::class);
        Period::after(new DateTime('2012-01-12'), []);
    }

    /**
     * @dataProvider intervalBeforeProviderData
     *
     * @param int|DateInterval|string $duration
     */
    public function testIntervalBefore(string $startDate, string $endDate, $duration): void
    {
        $period = Period::before($endDate, $duration);
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
        Period::before(new DateTime('2012-01-12'), '-1 DAY');
    }

    public function testIntervalAround(): void
    {
        $date = '2012-06-05';
        $duration = '1 WEEK';

        $period = Period::around($date, $duration);
        self::assertTrue($period->contains($date));
        self::assertEquals(Datepoint::create($date)->sub(Duration::create($duration)), $period->getStartDate());
        self::assertEquals(Datepoint::create($date)->add(Duration::create($duration)), $period->getEndDate());
    }

    public function testIntervalAroundThrowsException(): void
    {
        self::expectException(Exception::class);
        Period::around(new DateTime('2012-06-05'), '-1 DAY');
    }

    public function testIntervalFromDatePeriod(): void
    {
        $datePeriod = new DatePeriod(
            new DateTime('2016-05-16T00:00:00Z'),
            new DateInterval('P1D'),
            new DateTime('2016-05-20T00:00:00Z')
        );
        $period = Period::fromDatePeriod($datePeriod);
        self::assertEquals($datePeriod->getStartDate(), $period->getStartDate());
        self::assertEquals($datePeriod->getEndDate(), $period->getEndDate());
    }

    public function testIntervalFromDatePeriodThrowsException(): void
    {
        self::expectException(TypeError::class);
        Period::fromDatePeriod(new DatePeriod('R4/2012-07-01T00:00:00Z/P7D'));
    }

    public function testIsoWeek(): void
    {
        $period = Period::fromIsoWeek(2014, 3);
        self::assertEquals(new DateTimeImmutable('2014-01-13'), $period->getStartDate());
        self::assertEquals(new DateTimeImmutable('2014-01-20'), $period->getEndDate());
    }

    public function testIsoWeekWithDefaultArgument(): void
    {
        self::assertTrue(Period::fromIsoWeek(2014)->equals(Period::fromIsoWeek(2014, 1)));
    }

    public function testMonth(): void
    {
        $period = Period::fromMonth(2014, 3);
        self::assertEquals(new DateTimeImmutable('2014-03-01'), $period->getStartDate());
        self::assertEquals(new DateTimeImmutable('2014-04-01'), $period->getEndDate());
    }

    public function testMonthWithDefaultArgument(): void
    {
        self::assertTrue(Period::fromMonth(2014)->equals(Period::fromMonth(2014, 1)));
    }

    public function testQuarter(): void
    {
        $period = Period::fromQuarter(2014, 3);
        self::assertEquals(new DateTimeImmutable('2014-07-01'), $period->getStartDate());
        self::assertEquals(new DateTimeImmutable('2014-10-01'), $period->getEndDate());
    }

    public function testQuarterWithDefaultArgument(): void
    {
        self::assertEquals(Period::fromQuarter(2014), Period::fromQuarter(2014, 1));
    }

    public function testSemester(): void
    {
        $period = Period::fromSemester(2014, 2);
        self::assertEquals(new DateTimeImmutable('2014-07-01'), $period->getStartDate());
        self::assertEquals(new DateTimeImmutable('2015-01-01'), $period->getEndDate());
    }

    public function testSemesterWithDefaultArgument(): void
    {
        self::assertEquals(Period::fromSemester(2014), Period::fromSemester(2014, 1));
    }

    public function testYear(): void
    {
        $period = Period::fromYear(2014);
        self::assertEquals(new DateTimeImmutable('2014-01-01'), $period->getStartDate());
        self::assertEquals(new DateTimeImmutable('2015-01-01'), $period->getEndDate());
    }

    public function testISOYear(): void
    {
        $period = Period::fromIsoYear(2014);
        $interval = Datepoint::create('2014-06-25')->getIsoYear();
        self::assertEquals(new DateTimeImmutable('2013-12-30'), $period->getStartDate());
        self::assertEquals(new DateTimeImmutable('2014-12-29'), $period->getEndDate());
        self::assertTrue($period->equals($interval));
    }

    public function testDay(): void
    {
        $period = Datepoint::create(new ExtendedDate('2008-07-01T22:35:17.123456+08:00'))->getDay();
        self::assertEquals(new DateTimeImmutable('2008-07-01T00:00:00+08:00'), $period->getStartDate());
        self::assertEquals(new DateTimeImmutable('2008-07-02T00:00:00+08:00'), $period->getEndDate());
        self::assertEquals('+08:00', $period->getStartDate()->format('P'));
        self::assertEquals('+08:00', $period->getEndDate()->format('P'));
    }

    public function testAlternateDay(): void
    {
        $period = Datepoint::create('2008-07-01')->getDay();
        $alt_period = Period::fromDay(2008, 7, 1);
        self::assertEquals($period, $alt_period);
    }

    public function testDayWithDefaultArgument(): void
    {
        self::assertEquals(Period::fromDay(2008), Period::fromDay(2008, 1, 1));
        self::assertEquals(Period::fromDay(2008, 1), Period::fromDay(2008, 1, 1));
    }

    public function testHour(): void
    {
        $today = new ExtendedDate('2008-07-01T22:35:17.123456+08:00');
        $period = Datepoint::create($today)->getHour();
        self::assertEquals(new DateTimeImmutable('2008-07-01T22:00:00+08:00'), $period->getStartDate());
        self::assertEquals(new DateTimeImmutable('2008-07-01T23:00:00+08:00'), $period->getEndDate());
        self::assertEquals('+08:00', $period->getStartDate()->format('P'));
        self::assertEquals('+08:00', $period->getEndDate()->format('P'));
    }

    public function testCreateFromWithDateTimeInterface(): void
    {
        self::assertTrue(Datepoint::create('2008W27')->getIsoWeek()->equals(Period::fromIsoWeek(2008, 27)));
        self::assertTrue(Datepoint::create('2008-07')->getMonth()->equals(Period::fromMonth(2008, 7)));
        self::assertTrue(Datepoint::create('2008-02')->getQuarter()->equals(Period::fromQuarter(2008, 1)));
        self::assertTrue(Datepoint::create('2008-10')->getSemester()->equals(Period::fromSemester(2008, 2)));
        self::assertTrue(Datepoint::create('2008-01')->getYear()->equals(Period::fromYear(2008)));
    }

    public function testMonthWithDateTimeInterface(): void
    {
        $today = new ExtendedDate('2008-07-01T22:35:17.123456+08:00');
        $period = Datepoint::create($today)->getMonth();
        self::assertEquals(new DateTimeImmutable('2008-07-01T00:00:00+08:00'), $period->getStartDate());
        self::assertEquals(new DateTimeImmutable('2008-08-01T00:00:00+08:00'), $period->getEndDate());
        self::assertEquals('+08:00', $period->getStartDate()->format('P'));
        self::assertEquals('+08:00', $period->getEndDate()->format('P'));
    }

    public function testYearWithDateTimeInterface(): void
    {
        $today = new ExtendedDate('2008-07-01T22:35:17.123456+08:00');
        $period = Datepoint::create($today)->getYear();
        self::assertEquals(new DateTimeImmutable('2008-01-01T00:00:00+08:00'), $period->getStartDate());
        self::assertEquals(new DateTimeImmutable('2009-01-01T00:00:00+08:00'), $period->getEndDate());
        self::assertEquals('+08:00', $period->getStartDate()->format('P'));
        self::assertEquals('+08:00', $period->getEndDate()->format('P'));
    }

    /**
     * @dataProvider isoStringProvider
     *
     * @covers ::fromISO8601
     * @covers \League\Period\Datepoint::fromISO8601
     * @covers \League\Period\Datepoint::formatIso8601
     */
    public function testFromISO8601(string $isoString, string $separator, string $expected): void
    {
        self::assertSame($expected, Period::fromISO8601($isoString, $separator)->__toString());
    }

    public function isoStringProvider(): iterable
    {
        return [
            'datepoints only' => [
                'isoString' => '2007-03-01T13:00:00Z/2008-05-11T15:30:00Z',
                'separator' => '/',
                'expected' => '2007-03-01T13:00:00.000000Z/2008-05-11T15:30:00.000000Z',
            ],
            'start and duration' => [
                'isoString' => '2007-03-01T13:00:00Z/P1Y2M10DT2H30M',
                'separator' => '/',
                'expected' => '2007-03-01T13:00:00.000000Z/2008-05-11T15:30:00.000000Z',
            ],
            'end and duration' => [
                'isoString' => 'P1Y2M10DT2H30M--2008-05-11T15:30:00Z',
                'separator' => '--',
                'expected' => '2007-03-01T13:00:00.000000Z/2008-05-11T15:30:00.000000Z',
            ],
            'partial datetime at the end' => [
                'isoString' => '2007-12-14T13:30/15:30',
                'separator' => '/',
                'expected' => '2007-12-14T13:30:00.000000Z/2007-12-14T15:30:00.000000Z',
            ],
            'partial datetime at the start' => [
                'isoString' => '2008-02-15/03-14',
                'separator' => '/',
                'expected' => '2008-02-15T00:00:00.000000Z/2008-03-14T00:00:00.000000Z',
            ],
            'years only' => [
                'isoString' => '2008/2009',
                'separator' => '/',
                'expected' => '2008-01-01T00:00:00.000000Z/2009-01-01T00:00:00.000000Z',
            ],
            'years and month' => [
                'isoString' => '2008-03/05',
                'separator' => '/',
                'expected' => '2008-03-01T00:00:00.000000Z/2008-05-01T00:00:00.000000Z',
            ],
            'years and month and different days' => [
                'isoString' => '2008-03-04/05',
                'separator' => '/',
                'expected' => '2008-03-04T00:00:00.000000Z/2008-03-05T00:00:00.000000Z',
            ],
        ];
    }

    /**
     * @dataProvider invalidIso8601FormatProvider
     *
     * @covers ::fromISO8601
     * @covers \League\Period\Datepoint::fromISO8601
     * @covers \League\Period\Datepoint::formatIso8601
     */
    public function testISO8601ThrowOnWrongFormat(string $format, string $separator): void
    {
        self::expectException(Exception::class);

        Period::fromISO8601($format, $separator);
    }

    public function invalidIso8601FormatProvider(): iterable
    {
        return [
            'separator not found' => [
                'isoString' => '2008-02-15/03-14',
                'separator' => '--',
            ],
            'too many separators' => [
                'isoString' => '2008-02-15/03-14',
                'separator' => '-',
            ],
            'invalid partial iso format (1)' => [
                'isoString' => '03-14/2008-02-15',
                'separator' => '/',
            ],
            'invalid partial iso format (2)' => [
                'isoString' => '2008-03-04/2008-05',
                'separator' => '/',
            ],
            'invalid iso interval format' => [
                'isoString' => 'foo/bar',
                'separator' => '/',
            ],
        ];
    }
}
