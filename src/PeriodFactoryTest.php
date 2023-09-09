<?php

/**
 * League.Period (https://period.thephpleague.com)
 *
 * (c) Ignace Nyamagana Butera <nyamsprod@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

/**
 * League.Period (https://period.thephpleague.com).
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
use DateTimeInterface;
use DateTimeZone;
use PHPUnit\Framework\Attributes\DataProvider;

final class PeriodFactoryTest extends PeriodTestCase
{
    public function testInstantiationFromDatePointInstance(): void
    {
        self::assertEquals(
            Period::fromDate(DatePoint::fromDateString('TODAY'), DatePoint::fromDateString('TOMORROW')),
            Period::fromDate(new DateTimeImmutable('TODAY'), new DateTime('TOMORROW'))
        );
    }

    public function testInstantiationFromDateTimeInterfaceImplementingInstanceResultInEqualInstance(): void
    {
        self::assertEquals(
            Period::fromDate(new DateTime('TODAY'), new DateTimeImmutable('TOMORROW')),
            Period::fromDate(new DateTimeImmutable('TODAY'), new DateTime('TOMORROW'))
        );
    }

    public function testInstantiationFromSetState(): void
    {
        $period = Period::fromDate(DatePoint::fromDateString('2014-05-01'), DatePoint::fromDateString('2014-05-08'));
        /** @var Period $generatedPeriod */
        $generatedPeriod = eval('return '.var_export($period, true).';');
        self::assertTrue($generatedPeriod->equals($period));
    }

    public function testInstantiationFromTimestamp(): void
    {
        $dateStart = new DateTimeImmutable('@1');
        $dateEnd = new DateTimeImmutable('@2');

        self::assertEquals(Period::fromDate($dateStart, $dateEnd), Period::fromTimestamp(1, 2));
    }

    public function testInstantiationPrecision(): void
    {
        $date = new DateTimeImmutable('2014-05-01 00:00:00');
        self::assertEquals(new DateInterval('PT0S'), Period::fromDate($date, $date)->dateInterval());
    }

    public function testInstantiationThrowExceptionIfTimeZoneIsWronglyUsed(): void
    {
        $this->expectException(InvalidInterval::class);
        Period::fromDate(
            new DateTime('2014-05-01', new DateTimeZone('Europe/Paris')),
            new DateTime('2014-05-01', new DateTimeZone('Africa/Nairobi'))
        );
    }

    #[DataProvider('provideIntervalAfterData')]
    public function testIntervalAfter(string $startDate, string $endDate, Period|DateInterval|int|string $duration): void
    {
        $start = new DateTimeImmutable($startDate);
        $period = match (true) {
            $duration instanceof Period => Period::after($start, $duration->dateInterval()),
            is_string($duration) => Period::after($start, DateInterval::createFromDateString($duration)), /* @phpstan-ignore-line */
            !$duration instanceof DateInterval => Period::after($start, Duration::fromSeconds($duration)),
            default => Period::after($start, $duration),
        };

        self::assertEquals($start, $period->startDate);
        self::assertEquals(new DateTimeImmutable($endDate), $period->endDate);
    }

    /**
     * @return array<string, array{0:string, 1:string, 2:int|DateInterval|string|Period}>
     */
    public static function provideIntervalAfterData(): array
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

    public function testIntervalAfterFailedWithOutOfRangeInterval(): void
    {
        $this->expectException(InvalidInterval::class);
        $duration = new DateInterval('PT1S');
        $duration->invert = 1;

        Period::after(new DateTime('2012-01-12'), $duration);
    }

    #[DataProvider('intervalBeforeProviderData')]
    public function testIntervalBefore(string $startDate, string $endDate, int|DateInterval|string $duration): void
    {
        $end = new DateTimeImmutable($endDate);
        /** @var DateInterval $dateInterval */
        $dateInterval = match (true) {
            is_string($duration) => DateInterval::createFromDateString($duration),
            !$duration instanceof DateInterval => Duration::fromSeconds($duration),
            default => $duration,
        };

        $period = Period::before($end, $dateInterval);
        self::assertEquals(new DateTimeImmutable($startDate), $period->startDate);
        self::assertEquals($end, $period->endDate);
    }

    /**
     * @return array<string, array{0:string, 1:string, 2:int|DateInterval|string}>
     */
    public static function intervalBeforeProviderData(): array
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
        $this->expectException(InvalidInterval::class);
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
        self::assertEquals($datepoint->sub($interval), $period->startDate);
        self::assertEquals($datepoint->add($interval), $period->endDate);
    }

    public function testIntervalAroundThrowsException(): void
    {
        $this->expectException(InvalidInterval::class);

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
        $period = Period::fromDateRange($datePeriod);  /* @phpstan-ignore-line */
        self::assertEquals($datePeriod->getStartDate(), $period->startDate);
        self::assertEquals($datePeriod->getEndDate(), $period->endDate);
    }

    public function testFromDateRangeThrowsException(): void
    {
        $this->expectException(InvalidInterval::class);

        Period::fromRange(new DatePeriod('R4/2012-07-01T00:00:00Z/P7D'));
    }

    public function testFromRangeThrowsException(): void
    {
        $this->expectException(InvalidInterval::class);

        Period::fromRange(new DatePeriod('R4/2012-07-01T00:00:00Z/P7D'));
    }

    #[DataProvider('provideDatePeriodOptions')]
    public function testFromRange(int $option, Bounds $expectedBounds): void
    {
        $datePeriod = new DatePeriod(
            new DateTime('2016-05-16T00:00:00Z'),
            new DateInterval('P1D'),
            new DateTime('2016-05-20T00:00:00Z'),
            $option
        );

        $period = Period::fromRange($datePeriod);
        self::assertSame($expectedBounds, $period->bounds);
        self::assertEquals($datePeriod->getStartDate(), $period->startDate);
        self::assertEquals($datePeriod->getEndDate(), $period->endDate);
    }

    /**
     * @return iterable<string, array{option: int, expectedBounds: Bounds}>
     */
    public static function provideDatePeriodOptions(): iterable
    {
        yield 'include start date legacy' => [
            'options' => DatePeriod::EXCLUDE_START_DATE,
            'expectedBounds' => Bounds::ExcludeAll,
        ];

        yield 'exclude start date legacy' => [
            'options' => 0,
            'expectedBounds' => Bounds::IncludeStartExcludeEnd,
        ];

        if (defined('DatePeriod::INCLUDE_END_DATE')) {
            yield 'include all new' => [
                'options' => DatePeriod::INCLUDE_END_DATE,
                'expectedBounds' => Bounds::IncludeAll,
            ];

            yield 'exclude start date new' => [
                'options' => DatePeriod::INCLUDE_END_DATE | DatePeriod::EXCLUDE_START_DATE,
                'expectedBounds' => Bounds::ExcludeStartIncludeEnd,
            ];
        }
    }

    public function testIsoWeek(): void
    {
        $period = Period::fromIsoWeek(2014, 3);
        self::assertEquals(new DateTimeImmutable('2014-01-13'), $period->startDate);
        self::assertEquals(new DateTimeImmutable('2014-01-20'), $period->endDate);
    }

    public function testMonth(): void
    {
        $period = Period::fromMonth(2014, 3);
        self::assertEquals(new DateTimeImmutable('2014-03-01'), $period->startDate);
        self::assertEquals(new DateTimeImmutable('2014-04-01'), $period->endDate);
    }

    public function testQuarter(): void
    {
        $period = Period::fromQuarter(2014, 3);
        self::assertEquals(new DateTimeImmutable('2014-07-01'), $period->startDate);
        self::assertEquals(new DateTimeImmutable('2014-10-01'), $period->endDate);
    }

    public function testSemester(): void
    {
        $period = Period::fromSemester(2014, 2);
        self::assertEquals(new DateTimeImmutable('2014-07-01'), $period->startDate);
        self::assertEquals(new DateTimeImmutable('2015-01-01'), $period->endDate);
    }

    public function testYear(): void
    {
        $period = Period::fromYear(2014);
        self::assertEquals(new DateTimeImmutable('2014-01-01'), $period->startDate);
        self::assertEquals(new DateTimeImmutable('2015-01-01'), $period->endDate);
    }

    public function testISOYear(): void
    {
        $period = Period::fromIsoYear(2014);
        $interval = DatePoint::fromDateString('2014-06-25')->isoYear();
        self::assertEquals(new DateTimeImmutable('2013-12-30'), $period->startDate);
        self::assertEquals(new DateTimeImmutable('2014-12-29'), $period->endDate);
        self::assertTrue($period->equals($interval));
    }

    public function testDay(): void
    {
        $extendedDate = new class ('2008-07-01T22:35:17.123456+08:00') extends DateTimeImmutable {
        };

        $period = DatePoint::fromDate($extendedDate)->day();
        self::assertEquals(new DateTimeImmutable('2008-07-01T00:00:00+08:00'), $period->startDate);
        self::assertEquals(new DateTimeImmutable('2008-07-02T00:00:00+08:00'), $period->endDate);
        self::assertEquals('+08:00', $period->startDate->format('P'));
        self::assertEquals('+08:00', $period->endDate->format('P'));
    }

    public function testAlternateDay(): void
    {
        $period = DatePoint::fromDateString('2008-07-01')->day();
        $alt_period = Period::fromDay(2008, 7, 1);
        self::assertEquals($period, $alt_period);
    }

    public function testHour(): void
    {
        $today = new class ('2008-07-01T22:35:17.123456+08:00') extends DateTimeImmutable {
        };
        $period = DatePoint::fromDate($today)->hour();
        self::assertEquals(new DateTimeImmutable('2008-07-01T22:00:00+08:00'), $period->startDate);
        self::assertEquals(new DateTimeImmutable('2008-07-01T23:00:00+08:00'), $period->endDate);
        self::assertEquals('+08:00', $period->startDate->format('P'));
        self::assertEquals('+08:00', $period->endDate->format('P'));
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
        $today = new class ('2008-07-01T22:35:17.123456+08:00') extends DateTimeImmutable {
        };
        $period = DatePoint::fromDate($today)->month();
        self::assertEquals(new DateTimeImmutable('2008-07-01T00:00:00+08:00'), $period->startDate);
        self::assertEquals(new DateTimeImmutable('2008-08-01T00:00:00+08:00'), $period->endDate);
        self::assertEquals('+08:00', $period->startDate->format('P'));
        self::assertEquals('+08:00', $period->endDate->format('P'));
    }

    public function testYearWithDateTimeInterface(): void
    {
        $today = new class ('2008-07-01T22:35:17.123456+08:00') extends DateTimeImmutable {
        };
        $period = DatePoint::fromDate($today)->year();
        self::assertEquals(new DateTimeImmutable('2008-01-01T00:00:00+08:00'), $period->startDate);
        self::assertEquals(new DateTimeImmutable('2009-01-01T00:00:00+08:00'), $period->endDate);
        self::assertEquals('+08:00', $period->startDate->format('P'));
        self::assertEquals('+08:00', $period->endDate->format('P'));
    }

    public function testInstantiateWithTimeStamp(): void
    {
        $period = Period::after(DatePoint::fromTimestamp(12000000), new DateInterval('P1D'));

        self::assertEquals('+00:00', $period->endDate->format('P'));
    }

    #[DataProvider('provideValidIntervalIso80000')]
    public function testCreateNewInstanceFromNotation(string $notation, string $format, string $expected): void
    {
        self::assertSame($expected, Period::fromIso80000($format, $notation)->toIso80000($format));
    }

    /**
     * @return iterable<string, array{notation:string, format:string, expected:string}>
     */
    public static function provideValidIntervalIso80000(): iterable
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

        $now = (new DateTimeImmutable('now'))->format(DateTimeInterface::ATOM);
        $tomorrow = (new DateTimeImmutable('tomorrow'))->format(DateTimeInterface::ATOM);

        yield 'date string with dynamic names' => [
            'notation' => '[    '.$now.'   , '.$tomorrow.'   ]',
            'format' => DateTimeInterface::ATOM,
            'expected' =>   '['.$now.', '.$tomorrow.']',
        ];
    }

    #[DataProvider('provideInvalidIntervalNotation')]
    public function testFailsToCreateNewInstanceFromIso80000(string $notation, string $format): void
    {
        $this->expectException(InvalidInterval::class);

        Period::fromIso80000($format, $notation);
    }

    /**
     * @return iterable<string, array<string>>
     */
    public static function provideInvalidIntervalNotation(): iterable
    {
        return [
            'empty string' => ['', 'Y-m-d'],
            'missing separator' => ['[2021-01-02 2021-01-03]', 'Y-m-d'],
            'missing bounds' => ['2021-01-02,2021-01-03', 'Y-m-d'],
            'too many bounds' => ['[2021-01-02,2021-)01-03]', 'Y-m-d'],
            'too many separator' => ['[2021-01-02,2021-,01-03]', 'Y-m-d'],
            'missing dates' => ['[2021-01-02,  ]', 'Y-m-d'],
            'wrong format' => ['[2021-01-02, 2021-01-03]', 'Ymd'],
            'wrong bourbaki' => [']2021-01-02,2021-01-03)', 'Y-m-d'],
        ];
    }

    #[DataProvider('provideValidIntervalBourbaki')]
    public function testCreateNewInstanceFromBourbaki(string $notation, string $format, string $expected): void
    {
        self::assertSame($expected, Period::fromBourbaki($format, $notation)->toBourbaki($format));
    }

    /**
     * @return iterable<string, array{notation:string, format:string, expected:string}>
     */
    public static function provideValidIntervalBourbaki(): iterable
    {
        yield 'date string' => [
            'notation' => '[2021-01-03,2021-01-04[',
            'format' => 'Y-m-d',
            'expected' =>   '[2021-01-03, 2021-01-04[',
        ];

        yield 'date string with spaces' => [
            'notation' => ']   2021-01-03  ,  2021-01-04  ]',
            'format' => 'Y-m-d',
            'expected' =>   ']2021-01-03, 2021-01-04]',
        ];

        $now = (new DateTimeImmutable('now'))->format(DateTimeInterface::ATOM);
        $tomorrow = (new DateTimeImmutable('tomorrow'))->format(DateTimeInterface::ATOM);

        yield 'date string with dynamic names' => [
            'notation' => '[    '.$now.'   , '.$tomorrow.'   ]',
            'format' => DateTimeInterface::ATOM,
            'expected' =>   '['.$now.', '.$tomorrow.']',
        ];
    }

    #[DataProvider('provideInvalidIntervalBourbaki')]
    public function testFailsToCreateNewInstanceFromBourbaki(string $notation, string $format): void
    {
        $this->expectException(InvalidInterval::class);

        Period::fromBourbaki($format, $notation);
    }

    /**
     * @return iterable<string, array<string>>
     */
    public static function provideInvalidIntervalBourbaki(): iterable
    {
        return [
            'empty string' => ['', 'Y-m-d'],
            'missing separator' => ['[2021-01-02 2021-01-03]', 'Y-m-d'],
            'missing bounds' => ['2021-01-02,2021-01-03', 'Y-m-d'],
            'too many bounds' => ['[2021-01-02,2021-[01-03]', 'Y-m-d'],
            'too many separator' => ['[2021-01-02,2021-,01-03]', 'Y-m-d'],
            'missing dates' => ['[2021-01-02,  ]', 'Y-m-d'],
            'wrong format' => ['[2021-01-02, 2021-01-03]', 'Ymd'],
            'wrong bourbaki' => ['[2021-01-02,2021-01-03)', 'Y-m-d'],
        ];
    }

    #[DataProvider('providesValidIso8601Notation')]
    public function testCreateNewInstanceFromIsoNotation(
        string $inputFormat,
        string $notation,
        Bounds $bounds,
        string $outputFormat,
        string $expected
    ): void {
        $period = Period::fromIso8601($inputFormat, $notation, $bounds);

        self::assertSame($expected, $period->toIso8601($outputFormat));
        self::assertSame($bounds, $period->bounds);
    }

    /**
     * @return array<string, array{inputFormat:string, notation:string, bounds:Bounds, outputFormat:string, expected:string}>
     */
    public static function providesValidIso8601Notation(): array
    {
        return [
            'same input/output format' => [
                'inputFormat' => 'Y-m-d',
                'notation' => '2021-03-25/2021-03-26',
                'bounds' => Bounds::IncludeAll,
                'outputFormat' => 'Y-m-d',
                'expected' => '2021-03-25/2021-03-26',
            ],
            'different input/output format' => [
                'inputFormat' => 'Y-m-d',
                'notation' => '2021-03-25/2021-03-26',
                'bounds' => Bounds::ExcludeAll,
                'outputFormat' => 'Y-n-d',
                'expected' => '2021-3-25/2021-3-26',
            ],
            'same input/output format extended' => [
                'inputFormat' => 'Y-m-d',
                'notation' => '2021-03-25/26',
                'bounds' => Bounds::IncludeAll,
                'outputFormat' => 'Y-m-d',
                'expected' => '2021-03-25/2021-03-26',
            ],
            'different input/output format extended' => [
                'inputFormat' => 'Y-m-d',
                'notation' => '2021-03-25/03-26',
                'bounds' => Bounds::ExcludeAll,
                'outputFormat' => 'Y-n-d',
                'expected' => '2021-3-25/2021-3-26',
            ],
            'different input/output format with interval duration after start' => [
                'inputFormat' => 'Y-m-d',
                'notation' => '2021-03-25/P1D',
                'bounds' => Bounds::ExcludeAll,
                'outputFormat' => 'Y-n-d',
                'expected' => '2021-3-25/2021-3-26',
            ],
            'different input/output format with interval duration before end' => [
                'inputFormat' => 'Y-m-d',
                'notation' => 'P1D/2021-03-26',
                'bounds' => Bounds::ExcludeAll,
                'outputFormat' => 'Y-n-d',
                'expected' => '2021-3-25/2021-3-26',
            ],
        ];
    }

    #[DataProvider('provideInvalidIsoNotation')]
    public function testFailsToCreateNewInstanceFromIsoNotation(string $notation, string $format, Bounds $bounds): void
    {
        $this->expectException(InvalidInterval::class);

        Period::fromIso8601($format, $notation, $bounds);
    }

    /**
     * @return iterable<string, array{0:string, 1:string, 2:Bounds}>
     */
    public static function provideInvalidIsoNotation(): iterable
    {
        return [
            'empty string' => ['', 'Y-m-d', Bounds::IncludeAll],
            'missing separator' => ['2021-01-02 2021-01-03', 'Y-m-d', Bounds::IncludeAll],
            'too many separator' => ['2021-01-02/2021-/01-03', 'Y-m-d', Bounds::IncludeAll],
            'missing dates' => ['2021-01-02/', 'Y-m-d', Bounds::IncludeAll],
            'wrong format' => ['2021-01-02/2021-01-03', 'Ymd', Bounds::IncludeAll],
            'invalid extended format delimiters are different' => ['2021-01-02/01:03', 'Ymd', Bounds::IncludeAll],
            'invalid extended format start date is shorter than end date' => ['01/2021-01-02', 'Ymd', Bounds::IncludeAll],
            'invalid date with wrong period' => ['PMD/2021-01-02', 'Ymd', Bounds::IncludeAll],
        ];
    }
}
