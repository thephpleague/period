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
use Generator;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \League\Period\Period
 */
final class PeriodDurationTest extends TestCase
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

    public function testGetDateInterval(): void
    {
        $interval = Period::fromDate(new DateTimeImmutable('2012-02-01'), new DateTimeImmutable('2012-02-02'));
        self::assertSame(1, $interval->toDateInterval()->days);
    }

    public function testGetTimestampInterval(): void
    {
        $interval = Period::fromDate(new DateTimeImmutable('2012-02-01'), new DateTimeImmutable('2012-02-02'));
        self::assertSame(86400, $interval->toTimeDuration());
    }

    /**
     * @dataProvider providerGetDatePeriod
     *
     */
    public function testGetDatePeriod(DateInterval|int|string $duration, InitialDatePresence $option, int $count): void
    {
        if (is_string($duration)) {
            $duration = DateInterval::createFromDateString($duration);
        } elseif (!$duration instanceof DateInterval) {
            $duration = Duration::fromSeconds($duration);
        }

        $period = Period::fromDate(new DateTime('2012-01-12'), new DateTime('2012-01-13'));
        $range = $period->dateRangeForward($duration, $option);
        self::assertCount($count, iterator_to_array($range));
    }

    /**
     * @return array<string, array{0:DateInterval|int|string, 1:InitialDatePresence, 2:int}>
     */
    public function providerGetDatePeriod(): array
    {
        return [
            'useDateInterval' => [new DateInterval('PT1H'), InitialDatePresence::INCLUDED, 24],
            'useString' => ['2 HOUR', InitialDatePresence::INCLUDED, 12],
            'useInt' => [9600, InitialDatePresence::INCLUDED, 9],
            'exclude start date use DateInterval' => [new DateInterval('PT1H'), InitialDatePresence::EXCLUDED, 23],
            'exclude start date use String' => ['2 HOUR', InitialDatePresence::EXCLUDED, 11],
            'exclude start date use Int' => [9600, InitialDatePresence::EXCLUDED, 8],
            'exclude start date use Float' => [14400, InitialDatePresence::EXCLUDED, 5],
        ];
    }

    /**
     * @dataProvider providerGetDatePeriodBackwards
     *
     * @param DateInterval|int|string $duration
     */
    public function testGetDatePeriodBackwards($duration, InitialDatePresence $option, int $count): void
    {
        if (is_string($duration)) {
            $duration = DateInterval::createFromDateString($duration);
        } elseif (!$duration instanceof DateInterval) {
            $duration = Duration::fromSeconds($duration);
        }

        $period = Period::fromDate(new DateTime('2012-01-12'), new DateTime('2012-01-13'));
        $range = $period->dateRangeBackwards($duration, $option);
        self::assertCount($count, iterator_to_array($range));
    }

    /**
     * @return array<string,array{0:DateInterval|string|int, 1:InitialDatePresence, 2:int}>
     */
    public function providerGetDatePeriodBackwards(): array
    {
        return [
            'useDateInterval' => [new DateInterval('PT1H'), InitialDatePresence::INCLUDED, 24],
            'useString' => ['2 HOUR', InitialDatePresence::INCLUDED, 12],
            'useInt' => [9600, InitialDatePresence::INCLUDED, 9],
            'exclude start date useDateInterval' => [new DateInterval('PT1H'), InitialDatePresence::EXCLUDED, 23],
            'exclude start date useString' => ['2 HOUR', InitialDatePresence::EXCLUDED, 11],
            'exclude start date useInt' => [9600, InitialDatePresence::EXCLUDED, 8],
            'exclude start date useFloat' => [14400, InitialDatePresence::EXCLUDED, 5],
        ];
    }
    /**
     * @dataProvider durationCompareDataProvider
     */
    public function testDurationCompare(Period $interval1, Period $interval2, int $expected): void
    {
        self::assertSame($expected, $interval1->durationCompare($interval2));
    }

    /**
     * @return array<string,array{0:Period, 1:Period, 2:int}>
     */
    public function durationCompareDataProvider(): array
    {
        return [
            'duration less than' => [
                Period::fromDate(new DateTime('2012-01-01'), new DateTime('2012-01-15')),
                Period::fromDate(new DateTime('2013-01-01'), new DateTime('2013-01-16')),
                -1,
            ],
            'duration greater than' => [
                Period::fromDate(new DateTime('2012-01-01'), new DateTime('2012-01-15')),
                Period::fromDate(new DateTime('2012-01-01'), new DateTime('2012-01-07')),
                1,
            ],
            'duration equals with microsecond' => [
                Period::fromDate(new DateTime('2012-01-01 00:00:00'), new DateTime('2012-01-03 00:00:00.123456')),
                Period::fromDate(new DateTime('2012-02-02 00:00:00'), new DateTime('2012-02-04 00:00:00.123456')),
                0,
            ],
            'duration with DST' => [
                Period::fromDate(new DateTimeImmutable('2014-03-01'), new DateTimeImmutable('2014-04-01')),
                Period::fromDate(new DateTimeImmutable('2014-03-01'), new DateTimeImmutable('2014-04-01')),
                0,
            ],
        ];
    }

    public function testDurationCompareInnerMethods(): void
    {
        $period1 = Period::fromDate('2012-01-01', '2012-01-07');
        $period2 = Period::fromDate('2013-01-01', '2013-02-01');

        self::assertTrue($period1->durationLessThan($period2));
        self::assertTrue($period1->durationLessThanOrEquals($period2));

        $period3 = Period::fromDate('2012-01-01', '2012-02-01');
        $period4 = Period::fromDate('2012-01-01', '2012-01-07');

        self::assertTrue($period3->durationGreaterThan($period4));

        $period5 = Period::fromDate(new DateTime('2012-01-01 00:00:00'), new DateTime('2012-01-03 00:00:00'));
        $period6 = Period::fromDate(new DateTime('2012-02-02 00:00:00'), new DateTime('2012-02-04 00:00:00'));
        self::assertTrue($period5->durationEquals($period6));
        self::assertTrue($period5->durationGreaterThanOrEquals($period6));
        self::assertTrue($period5->durationLessThanOrEquals($period6));
    }

    public function testDateIntervalDiff(): void
    {
        $orig = Period::after(new DateTimeImmutable('2012-01-01'), DateInterval::createFromDateString('1 HOUR'));
        $alt = Period::after(new DateTimeImmutable('2012-01-01'), DateInterval::createFromDateString('2 HOUR'));
        self::assertSame(1, $orig->dateIntervalDiff($alt)->h);
        self::assertSame(0, $orig->dateIntervalDiff($alt)->days);
    }

    public function testTimestampIntervalDiff(): void
    {
        $orig = Period::after(new DateTimeImmutable('2012-01-01'), DateInterval::createFromDateString('1 HOUR'));
        $alt = Period::after(new DateTimeImmutable('2012-01-01'), DateInterval::createFromDateString('2 HOUR'));
        self::assertEquals(-3600, $orig->timeDurationDiff($alt));
    }

    public function testDateIntervalDiffPositionIrrelevant(): void
    {
        $orig = Period::after(new DateTimeImmutable('2012-01-01'), DateInterval::createFromDateString('1 HOUR'));
        $alt = Period::after(new DateTimeImmutable('2012-01-01'), DateInterval::createFromDateString('2 HOUR'));
        $fromOrig = $orig->dateIntervalDiff($alt);
        $fromOrig->invert = 1;
        self::assertEquals($fromOrig, $alt->dateIntervalDiff($orig));
    }

    public function testSplit(): void
    {
        $period = Period::fromDate(new DateTime('2012-01-12'), new DateTime('2012-01-13'));
        /** @var Generator<Period> $range */
        $range = $period->splitForward(new DateInterval('PT1H'));

        self::assertSame(24, iterator_count($range));
    }

    public function testSplitMustRecreateParentObject(): void
    {
        $period = Period::fromDate(new DateTime('2012-01-12'), new DateTime('2012-01-13'));
        $range = $period->splitForward(new DateInterval('PT1H'));
        /** @var Period|null $total */
        $total = null;
        foreach ($range as $part) {
            if (null === $total) {
                $total = $part;
                continue;
            }
            $total = $total->endingOn($part->endDate);
        }
        self::assertInstanceOf(Period::class, $total);
        self::assertTrue($total->equals($period));
    }

    public function testSplitWithLargeInterval(): void
    {
        $period = Period::fromDate(new DateTime('2012-01-12'), new DateTime('2012-01-13'));
        $range = [];
        foreach ($period->splitForward(new DateInterval('P1Y')) as $innerPeriod) {
            $range[] = $innerPeriod;
        }
        self::assertCount(1, $range);
        self::assertTrue($range[0]->equals($period));
    }

    public function testSplitWithInconsistentInterval(): void
    {
        $last = null;
        $period = Period::fromDate(new DateTime('2012-01-12'), new DateTime('2012-01-13'));

        foreach ($period->splitForward(new DateInterval('PT10H')) as $innerPeriod) {
            $last = $innerPeriod;
        }
        self::assertNotNull($last);
        self::assertSame(14400, $last->toTimeDuration());
    }

    public function testSplitBackwards(): void
    {
        $period = Period::fromDate(new DateTime('2015-01-01'), new DateTime('2015-01-04'));
        $range = $period->splitBackwards(new DateInterval('P1D'));
        $list = [];
        foreach ($range as $innerPeriod) {
            $list[] = $innerPeriod;
        }

        $result = array_map(function (Period $range): array {
            return [
                'start' => $range->startDate->format('Y-m-d H:i:s'),
                'end'   => $range->endDate->format('Y-m-d H:i:s'),
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

    public function testSplitBackwardsWithInconsistentInterval(): void
    {
        $period = Period::fromDate(new DateTime('2010-01-01'), new DateTime('2010-01-02'));
        $last = null;
        foreach ($period->splitBackwards(new DateInterval('PT10H')) as $innerPeriod) {
            $last = $innerPeriod;
        }

        self::assertNotNull($last);
        self::assertEquals(14400, $last->toTimeDuration());
    }

    public function testSplitDaylightSavingsDayIntoHoursEndInterval(): void
    {
        date_default_timezone_set('Canada/Central');
        $period = Period::fromDate(new DateTime('2018-11-04 00:00:00.000000'), new DateTime('2018-11-04 05:00:00.000000'));
        /** @var Generator<Period> $splits */
        $splits = $period->splitForward(new DateInterval('PT30M'));
        self::assertSame(10, iterator_count($splits));
    }

    public function testSplitBackwardsDaylightSavingsDayIntoHoursStartInterval(): void
    {
        date_default_timezone_set('Canada/Central');
        $period = Period::fromDate(new DateTime('2018-04-11 00:00:00.000000'), new DateTime('2018-04-11 05:00:00.000000'));
        /** @var Generator<Period> $splits */
        $splits = $period->splitBackwards(new DateInterval('PT30M'));
        self::assertSame(10, iterator_count($splits));
    }
}
