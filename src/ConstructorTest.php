<?php

/**
 * League.Period (https://period.thephpleague.com)
 *
 * (c) Ignace Nyamagana Butera <nyamsprod@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace League\Period;

use DateInterval;
use DatePeriod;
use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \League\Period\Period
 */
final class ConstructorTest extends TestCase
{
    /**
     * @var string
     */
    private $timezone;

    public function setUp(): void
    {
        $this->timezone = date_default_timezone_get();
    }

    public function tearDown(): void
    {
        date_default_timezone_set($this->timezone);
    }

    public function testConstructorThrowExceptionIfUnknownBoundPeriodDay(): void
    {
        $this->expectException(InvalidTimeRange::class);
        Period::fromDatepoint(new DateTime('2014-01-13'), new DateTime('2014-01-20'), 'foobar');
    }

    public function testCreateFromDateTimeInterface(): void
    {
        self::assertEquals(
            Period::fromDatepoint(new DateTime('TODAY'), new DateTimeImmutable('TOMORROW')),
            Period::fromDatepoint(new DateTime('TODAY'), new DateTime('TOMORROW'))
        );
    }

    public function testSetState(): void
    {
        $period = Period::fromDatepoint(DatePoint::fromDateString('2014-05-01'), DatePoint::fromDateString('2014-05-08'));
        $generatedPeriod = eval('return '.var_export($period, true).';');
        self::assertTrue($generatedPeriod->equals($period));
        self::assertEquals($generatedPeriod, $period);
    }

    public function testConstructor(): void
    {
        $period = Period::fromDatepoint(DatePoint::fromDateString('2014-05-01'), DatePoint::fromDateString('2014-05-08'));
        self::assertEquals(new DateTimeImmutable('2014-05-01'), $period->startDate());
        self::assertEquals(new DateTimeImmutable('2014-05-08'), $period->endDate());
    }

    public function testConstructorWithMicroSecondsSucceed(): void
    {
        $period = Period::fromDatepoint(new DateTimeImmutable('2014-05-01 00:00:00'), new DateTimeImmutable('2014-05-01 00:00:00'));
        self::assertEquals(new DateInterval('PT0S'), $period->dateInterval());
    }

    public function testConstructorThrowException(): void
    {
        $this->expectException(InvalidTimeRange::class);
        Period::fromDatepoint(
            new DateTime('2014-05-01', new DateTimeZone('Europe/Paris')),
            new DateTime('2014-05-01', new DateTimeZone('Africa/Nairobi'))
        );
    }

    public function testConstructorWithDateTimeInterface(): void
    {
        $start = '2014-05-01';
        $end = new DateTime('2014-05-08');
        $period = Period::fromDatepoint(new DateTimeImmutable($start), $end);
        self::assertSame($start, $period->startDate()->format('Y-m-d'));
        self::assertEquals($end, $period->endDate());
    }

    /**
     * @dataProvider provideIntervalAfterData
     *
     * @param Period|DateInterval|int|string $duration
     */
    public function testIntervalAfter(string $startDate, string $endDate, Period|DateInterval|int|string $duration): void
    {
        if ($duration instanceof Period) {
            $duration = $duration->dateInterval();
        } elseif (is_string($duration)) {
            $duration = DateInterval::createFromDateString($duration);
        } elseif (!$duration instanceof DateInterval) {
            $duration = Duration::fromSeconds($duration);
        }

        $period = Period::after(DatePoint::fromDateString($startDate), $duration);
        self::assertEquals(new DateTimeImmutable($startDate), $period->startDate());
        self::assertEquals(new DateTimeImmutable($endDate), $period->endDate());
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
            'usingAnInterval' => [
                '2015-01-01 10:00:00', '2015-01-01 11:00:00', DatePoint::fromDateString('2012-01-03 12:00:00')->hour(),
            ],
        ];
    }

    public function testIntervalAfterFailedWithOutofRangeInterval(): void
    {
        $this->expectException(InvalidTimeRange::class);
        $duration = new DateInterval('PT1S');
        $duration->invert = 1;

        Period::after(new DateTime('2012-01-12'), $duration);
    }

    /**
     * @dataProvider intervalBeforeProviderData
     *
     * @param int|DateInterval|string $duration
     */
    public function testIntervalBefore(string $startDate, string $endDate, $duration): void
    {
        if (is_string($duration)) {
            $duration = DateInterval::createFromDateString($duration);
        } elseif (!$duration instanceof DateInterval) {
            $duration = Duration::fromSeconds($duration);
        }

        $period = Period::before(DatePoint::fromDateString($endDate), $duration);
        self::assertEquals(new DateTimeImmutable($startDate), $period->startDate());
        self::assertEquals(new DateTimeImmutable($endDate), $period->endDate());
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
        $this->expectException(InvalidTimeRange::class);
        $duration = new DateInterval('PT1S');
        $duration->invert = 1;

        Period::before(new DateTime('2012-01-12'), $duration);
    }

    public function testIntervalAround(): void
    {
        $datepoint = new DateTimeImmutable('2012-06-05');
        $interval = DateInterval::createFromDateString('1 WEEK');
        $period = Period::around($datepoint, $interval);

        self::assertTrue($period->contains($datepoint));
        self::assertEquals($datepoint->sub($interval), $period->startDate());
        self::assertEquals($datepoint->add($interval), $period->endDate());
    }

    public function testIntervalAroundThrowsException(): void
    {
        $this->expectException(InvalidTimeRange::class);

        $duration = new DateInterval('PT1S');
        $duration->invert = 1;
        Period::around(new DateTime('2012-06-05'), $duration);
    }

    public function testIntervalFromDatePeriod(): void
    {
        $datePeriod = new DatePeriod(
            new DateTime('2016-05-16T00:00:00Z'),
            new DateInterval('P1D'),
            new DateTime('2016-05-20T00:00:00Z')
        );
        $period = Period::fromDatePeriod($datePeriod);
        self::assertEquals($datePeriod->getStartDate(), $period->startDate());
        self::assertEquals($datePeriod->getEndDate(), $period->endDate());
    }

    public function testIntervalFromDatePeriodThrowsException(): void
    {
        $this->expectException(InvalidTimeRange::class);

        Period::fromDatePeriod(new DatePeriod('R4/2012-07-01T00:00:00Z/P7D'));
    }

    public function testIsoWeek(): void
    {
        $period = Period::fromIsoWeek(2014, 3);
        self::assertEquals(new DateTimeImmutable('2014-01-13'), $period->startDate());
        self::assertEquals(new DateTimeImmutable('2014-01-20'), $period->endDate());
    }

    public function testMonth(): void
    {
        $period = Period::fromMonth(2014, 3);
        self::assertEquals(new DateTimeImmutable('2014-03-01'), $period->startDate());
        self::assertEquals(new DateTimeImmutable('2014-04-01'), $period->endDate());
    }

    public function testQuarter(): void
    {
        $period = Period::fromQuarter(2014, 3);
        self::assertEquals(new DateTimeImmutable('2014-07-01'), $period->startDate());
        self::assertEquals(new DateTimeImmutable('2014-10-01'), $period->endDate());
    }

    public function testSemester(): void
    {
        $period = Period::fromSemester(2014, 2);
        self::assertEquals(new DateTimeImmutable('2014-07-01'), $period->startDate());
        self::assertEquals(new DateTimeImmutable('2015-01-01'), $period->endDate());
    }

    public function testYear(): void
    {
        $period = Period::fromYear(2014);
        self::assertEquals(new DateTimeImmutable('2014-01-01'), $period->startDate());
        self::assertEquals(new DateTimeImmutable('2015-01-01'), $period->endDate());
    }

    public function testISOYear(): void
    {
        $period = Period::fromIsoYear(2014);
        $interval = DatePoint::fromDateString('2014-06-25')->isoYear();
        self::assertEquals(new DateTimeImmutable('2013-12-30'), $period->startDate());
        self::assertEquals(new DateTimeImmutable('2014-12-29'), $period->endDate());
        self::assertTrue($period->equals($interval));
    }

    public function testDay(): void
    {
        $extendedDate = new /** @psalm-immutable */ class('2008-07-01T22:35:17.123456+08:00') extends DateTimeImmutable {
        };

        $period = DatePoint::fromDate($extendedDate)->day();
        self::assertEquals(new DateTimeImmutable('2008-07-01T00:00:00+08:00'), $period->startDate());
        self::assertEquals(new DateTimeImmutable('2008-07-02T00:00:00+08:00'), $period->endDate());
        self::assertEquals('+08:00', $period->startDate()->format('P'));
        self::assertEquals('+08:00', $period->endDate()->format('P'));
    }

    public function testAlternateDay(): void
    {
        $period = DatePoint::fromDateString('2008-07-01')->day();
        $alt_period = Period::fromDay(2008, 7, 1);
        self::assertEquals($period, $alt_period);
    }

    public function testHour(): void
    {
        $today = new /** @psalm-immutable */ class('2008-07-01T22:35:17.123456+08:00') extends DateTimeImmutable {
        };
        $period = DatePoint::fromDate($today)->hour();
        self::assertEquals(new DateTimeImmutable('2008-07-01T22:00:00+08:00'), $period->startDate());
        self::assertEquals(new DateTimeImmutable('2008-07-01T23:00:00+08:00'), $period->endDate());
        self::assertEquals('+08:00', $period->startDate()->format('P'));
        self::assertEquals('+08:00', $period->endDate()->format('P'));
    }

    public function testCreateFromWithDateTimeInterface(): void
    {
        self::assertTrue(DatePoint::fromDateString('2008W27')->isoWeek()->equals(Period::fromIsoWeek(2008, 27)));
        self::assertTrue(DatePoint::fromDateString('2008-07')->month()->equals(Period::fromMonth(2008, 7)));
        self::assertTrue(DatePoint::fromDateString('2008-02')->quarter()->equals(Period::fromQuarter(2008, 1)));
        self::assertTrue(DatePoint::fromDateString('2008-10')->semester()->equals(Period::fromSemester(2008, 2)));
        self::assertTrue(DatePoint::fromDateString('2008-01')->year()->equals(Period::fromYear(2008)));
    }

    public function testMonthWithDateTimeInterface(): void
    {
        $today = new /** @psalm-immutable */ class('2008-07-01T22:35:17.123456+08:00') extends DateTimeImmutable {
        };
        $period = DatePoint::fromDate($today)->month();
        self::assertEquals(new DateTimeImmutable('2008-07-01T00:00:00+08:00'), $period->startDate());
        self::assertEquals(new DateTimeImmutable('2008-08-01T00:00:00+08:00'), $period->endDate());
        self::assertEquals('+08:00', $period->startDate()->format('P'));
        self::assertEquals('+08:00', $period->endDate()->format('P'));
    }

    public function testYearWithDateTimeInterface(): void
    {
        $today = new /** @psalm-immutable */ class('2008-07-01T22:35:17.123456+08:00') extends DateTimeImmutable {
        };
        $period = DatePoint::fromDate($today)->year();
        self::assertEquals(new DateTimeImmutable('2008-01-01T00:00:00+08:00'), $period->startDate());
        self::assertEquals(new DateTimeImmutable('2009-01-01T00:00:00+08:00'), $period->endDate());
        self::assertEquals('+08:00', $period->startDate()->format('P'));
        self::assertEquals('+08:00', $period->endDate()->format('P'));
    }

    public function testInstantiateWithTimeStamp(): void
    {
        $period = Period::after(DatePoint::fromTimestamp(12000000), new DateInterval('P1D'));

        self::assertEquals('+00:00', $period->endDate()->format('P'));
    }

    /**
     * @dataProvider provideValidIntervalNotation
     */
    public function testCreateNewInstanceFromNotation(string $notation, string $format, string $expected): void
    {
        self::assertSame($expected, Period::fromNotation($notation)->toNotation($format));
    }

    public function provideValidIntervalNotation(): iterable
    {
        yield 'date string' => [
          'notation' => '[2021-01-03,2021-01-04)',
          'format' => 'Y-m-d',
          'expected' =>   '[2021-01-03, 2021-01-04)',
        ];

        yield 'date string with spaces' => [
            'notation' => '(   2021-01-03  ,  2021-01-04  ]',
            'format' => 'Y-m-d',
            'expected' =>   '(2021-01-03, 2021-01-04]',
        ];

        $now = (new DateTimeImmutable('now'))->format('Y-m-d');
        $tomorrow = (new DateTimeImmutable('tomorrow'))->format('Y-m-d');

        yield 'date string with dynamic names' => [
            'notation' => '[now  ,  tomorrow]',
            'format' => 'Y-m-d',
            'expected' =>   '['.$now.', '.$tomorrow.']',
        ];
    }

    /**
     * @dataProvider provideInvalidIntervalNotation
     */
    public function testFailsToCreateNewInstanceFromNotation(string $notation, ): void
    {
        $this->expectException(InvalidTimeRange::class);

        Period::fromNotation($notation);
    }

    public function provideInvalidIntervalNotation(): iterable
    {
        return [
            'empty string' => [''],
            'missing separator' => ['[2021-01-02 2021-01-03]'],
            'missing boundaries' => ['2021-01-02,2021-01-03'],
            'too many boundaries' => ['[2021-01-02,2021-)01-03]'],
            'too many separator' => ['[2021-01-02,2021-,01-03]'],
            'missing dates' => ['[2021-01-02,  ]'],
        ];
    }
}
