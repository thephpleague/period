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
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use PHPUnit\Framework\TestCase;

final class DatePointTest extends TestCase
{
    private string $timezone;

    protected function setUp(): void
    {
        $this->timezone = date_default_timezone_get();
    }

    protected function tearDown(): void
    {
        date_default_timezone_set($this->timezone);
    }

    public function testInstantiationFromSetState(): void
    {
        $datePoint = DatePoint::fromDateString('TOMORROW');
        /** @var DatePoint $generatedDatePoint */
        $generatedDatePoint = eval('return '.var_export($datePoint, true).';');

        self::assertEquals($datePoint, $generatedDatePoint);
    }

    public function testInstantiationFromMinute(): void
    {
        $datePoint = DatePoint::fromDateString('2021-07-08 13:23:58');
        $minutePeriod = $datePoint->toMinute();

        self::assertEquals('2021-07-08 13:23:00', $minutePeriod->startDate->format('Y-m-d H:i:s'));
        self::assertEquals('2021-07-08 13:24:00', $minutePeriod->endDate->format('Y-m-d H:i:s'));
    }

    public function testInstantiationFromSeconds(): void
    {
        $datePoint = DatePoint::fromDateString('2021-07-08 13:23:58');
        $secondPeriod = $datePoint->toSecond();

        self::assertEquals('2021-07-08 13:23:58', $secondPeriod->startDate->format('Y-m-d H:i:s'));
        self::assertEquals('2021-07-08 13:23:59', $secondPeriod->endDate->format('Y-m-d H:i:s'));
    }
    /**
     * @dataProvider isAfterProvider
     */
    public function testIsAfter(Period $interval, DateTimeInterface $input, bool $expected): void
    {
        self::assertSame($expected, DatePoint::fromDate($input)->isAfter($interval));
    }

    /**
     * @return array<string, array{interval:Period, input:DateTimeInterface, expected:bool}>
     */
    public function isAfterProvider(): array
    {
        return [
            'range exclude end date success' => [
                'interval' => Period::fromMonth(2012, 1),
                'input' => new DateTime('2015-01-01'),
                'expected' => true,
            ],
            'range exclude end date fails' => [
                'interval' => Period::fromMonth(2012, 1),
                'input' => new DateTime('2010-01-01'),
                'expected' => false,
            ],
            'range exclude end date abuts date fails' => [
                'interval' => Period::fromMonth(2012, 1),
                'input' => new DateTime('2012-01-01'),
                'expected' => false,
            ],
            'range exclude start date success' => [
                'interval' => Period::after(new DateTime('2012-01-01'), DateInterval::createFromDateString('1 MONTH'), Bounds::EXCLUDE_START_INCLUDE_END),
                'input' => new DateTime('2015-01-01'),
                'expected' => true,
            ],
            'range exclude start date fails' => [
                'interval' => Period::after(new DateTime('2012-01-01'), DateInterval::createFromDateString('1 MONTH'), Bounds::EXCLUDE_START_INCLUDE_END),
                'input' => new DateTime('2010-01-01'),
                'expected' => false,
            ],
            'range exclude start date abuts date success' => [
                'interval' => Period::after(new DateTime('2012-01-01'), DateInterval::createFromDateString('1 MONTH'), Bounds::EXCLUDE_START_INCLUDE_END),
                'input' => new DateTime('2012-02-01'),
                'expected' => false,
            ],
        ];
    }

    /**
     * @dataProvider isBeforeProvider
     */
    public function testIsBefore(Period $interval, DateTimeInterface $input, bool $expected): void
    {
        self::assertSame($expected, DatePoint::fromDate($input)->isBefore($interval));
    }

    /**
     * @return array<string, array{interval:Period, input:DateTimeInterface, expected:bool}>
     */
    public function isBeforeProvider(): array
    {
        return [
            'range exclude end date success' => [
                'interval' => Period::fromMonth(2012, 1),
                'input' => new DateTime('2010-01-01'),
                'expected' => true,
            ],
            'range exclude end date fails' => [
                'interval' => Period::fromMonth(2012, 1),
                'input' => new DateTime('2015-01-01'),
                'expected' => false,
            ],
            'range exclude end date abuts date fails' => [
                'interval' => Period::after(new DateTime('2012-01-01'), DateInterval::createFromDateString('1 MONTH'), Bounds::EXCLUDE_START_INCLUDE_END),
                'input' => new DateTime('2012-02-01'),
                'expected' => false,
            ],
            'range exclude start date success' => [
                'interval' => Period::after(new DateTime('2012-01-01'), DateInterval::createFromDateString('1 MONTH'), Bounds::EXCLUDE_START_INCLUDE_END),
                'input' => new DateTime('2012-01-01'),
                'expected' => true,
            ],
        ];
    }

    public function testDatepointBorderingOn(): void
    {
        $datepoint = DatePoint::fromDateString('2018-01-18 10:00:00');
        $duration = Duration::fromDateString('3 minutes');

        $intervalBorderOnStartTrue = Period::after($datepoint, $duration, Bounds::EXCLUDE_START_INCLUDE_END);
        self::assertTrue($datepoint->bordersOnStart($intervalBorderOnStartTrue));
        self::assertTrue($datepoint->abuts($intervalBorderOnStartTrue));

        $intervalBorderOnStartFalse = Period::after($datepoint, $duration, Bounds::INCLUDE_ALL);
        self::assertFalse($datepoint->bordersOnStart($intervalBorderOnStartFalse));
        self::assertFalse($datepoint->abuts($intervalBorderOnStartFalse));

        $intervalBorderOnEndTrue = Period::before($datepoint, $duration, Bounds::INCLUDE_START_EXCLUDE_END);
        self::assertTrue($datepoint->bordersOnEnd($intervalBorderOnEndTrue));
        self::assertTrue($datepoint->abuts($intervalBorderOnEndTrue));

        $intervalBorderOnEndFalse = Period::before($datepoint, $duration, Bounds::EXCLUDE_START_INCLUDE_END);
        self::assertFalse($datepoint->bordersOnEnd($intervalBorderOnEndFalse));
        self::assertFalse($datepoint->abuts($intervalBorderOnEndFalse));
    }

    /**
     * @dataProvider isDuringDataProvider
     *
     */
    public function testIsDuring(Period $interval, DateTimeInterface|string $input, bool $expected): void
    {
        $datepoint = $input instanceof DateTimeInterface ? DatePoint::fromDate($input) : DatePoint::fromDateString($input);

        self::assertSame($expected, $datepoint->isDuring($interval));
    }

    /**
     * @return array<string, array{0:Period, 1:DateTimeInterface|string, 2:bool}>
     */
    public function isDuringDataProvider(): array
    {
        return [
            'contains returns true with a DateTimeInterface object' => [
                Period::fromDate(new DateTimeImmutable('2014-03-10'), new DateTimeImmutable('2014-03-15')),
                new DateTime('2014-03-12'),
                true,
            ],
            'contains returns false with a DateTimeInterface object' => [
                Period::fromDate(new DateTimeImmutable('2014-03-13'), new DateTimeImmutable('2014-03-15')),
                new DateTime('2015-03-12'),
                false,
            ],
            'contains returns false with a DateTimeInterface object after the interval' => [
                Period::fromDate(new DateTimeImmutable('2014-03-13'), new DateTimeImmutable('2014-03-15')),
                '2012-03-12',
                false,
            ],
            'contains returns false with a DateTimeInterface object before the interval' => [
                Period::fromDate(new DateTimeImmutable('2014-03-13'), new DateTimeImmutable('2014-03-15')),
                '2014-04-01',
                false,
            ],
            'contains returns false with O duration Period object' => [
                Period::fromDate(new DateTimeImmutable('2012-03-12'), new DateTimeImmutable('2012-03-12')),
                new DateTime('2012-03-12'),
                false,
            ],
            'contains datetime edge case datetime equals start date' => [
                Period::after(new DateTime('2012-01-08'), Duration::fromDateString('1 DAY')),
                new DateTime('2012-01-08'),
                true,
            ],
            'contains datetime edge case datetime equals end date' => [
                Period::after(new DateTime('2012-01-08'), Duration::fromDateString('1 DAY')),
                new DateTime('2012-01-09'),
                false,
            ],
            'contains datetime edge case datetime equals start date OLCR interval' => [
                Period::after(new DateTime('2012-01-08'), Duration::fromDateString('1 DAY'), Bounds::EXCLUDE_START_INCLUDE_END),
                new DateTime('2012-01-08'),
                false,
            ],
            'contains datetime edge case datetime equals end date CLCR interval' => [
                Period::after(new DateTime('2012-01-08'), Duration::fromDateString('1 DAY'), Bounds::EXCLUDE_ALL),
                new DateTime('2012-01-09'),
                false,
            ],
        ];
    }

    /**
     * @dataProvider startsDataProvider
     */
    public function testStarts(Period $interval, DateTimeInterface $index, bool $expected): void
    {
        self::assertSame($expected, DatePoint::fromDate($index)->isStarting($interval));
    }

    /**
     * @return array<array{0:Period, 1:DateTimeInterface, 2:bool}>
     */
    public function startsDataProvider(): array
    {
        $datepoint = new DateTime('2012-01-01');

        return [
            [
                Period::fromDate($datepoint, new DateTime('2012-01-15'), Bounds::EXCLUDE_ALL),
                $datepoint,
                false,
            ],
            [
                Period::fromDate($datepoint, new DateTime('2012-01-15'), Bounds::INCLUDE_START_EXCLUDE_END),
                $datepoint,
                true,
            ],
        ];
    }

    /**
     * @dataProvider isEndingDataProvider
     */
    public function testFinishes(Period $interval, DateTimeInterface $index, bool $expected): void
    {
        self::assertSame($expected, DatePoint::fromDate($index)->isEnding($interval));
    }

    /**
     * @return array<array{0:Period, 1:DateTimeInterface, 2:bool}>
     */
    public function isEndingDataProvider(): array
    {
        $datepoint = new DateTime('2012-01-16');

        return [
            [
                Period::fromDate(new DateTime('2012-01-01'), $datepoint, Bounds::EXCLUDE_ALL),
                $datepoint,
                false,
            ],
            [
                Period::fromDate(new DateTime('2012-01-01'), $datepoint, Bounds::INCLUDE_ALL),
                $datepoint,
                true,
            ],
        ];
    }
}
