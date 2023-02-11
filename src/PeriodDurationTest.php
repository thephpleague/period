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
use DateTimeImmutable;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;

final class PeriodDurationTest extends PeriodTestCase
{
    public function testGetDateInterval(): void
    {
        self::assertSame(1, Period::fromDate('2012-02-01', '2012-02-02')->dateInterval()->days);
    }

    public function testGetTimestampInterval(): void
    {
        self::assertSame(86400, Period::fromDate('2012-02-01', '2012-02-02')->timeDuration());
    }

    #[DataProvider('providerGetDatePeriod')]
    public function testGetDatePeriod(DateInterval|int|string $duration, InitialDatePresence $option, int $count): void
    {
        if (is_string($duration)) {
            $duration = DateInterval::createFromDateString($duration);
        } elseif (!$duration instanceof DateInterval) {
            $duration = Duration::fromSeconds($duration);
        }

        $period = Period::fromDate('2012-01-12', '2012-01-13');
        self::assertCount($count, iterator_to_array($period->dateRangeForward($duration, $option))); /* @phpstan-ignore-line */
    }

    /**
     * @return array<string, array{0:DateInterval|int|string, 1:InitialDatePresence, 2:int}>
     */
    public static function providerGetDatePeriod(): array
    {
        return [
            'useDateInterval' => [new DateInterval('PT1H'), InitialDatePresence::Included, 24],
            'useString' => ['2 HOUR', InitialDatePresence::Included, 12],
            'useInt' => [9600, InitialDatePresence::Included, 9],
            'exclude start date use DateInterval' => [new DateInterval('PT1H'), InitialDatePresence::Excluded, 23],
            'exclude start date use String' => ['2 HOUR', InitialDatePresence::Excluded, 11],
            'exclude start date use Int' => [9600, InitialDatePresence::Excluded, 8],
            'exclude start date use Float' => [14400, InitialDatePresence::Excluded, 5],
        ];
    }

    #[DataProvider('providerGetDatePeriodBackwards')]
    public function testGetDatePeriodBackwards(DateInterval|int|string $duration, InitialDatePresence $option, int $count): void
    {
        if (is_int($duration)) {
            $duration = Duration::fromSeconds($duration);
        }

        $period = Period::fromDate('2012-01-12', '2012-01-13');

        self::assertCount($count, iterator_to_array($period->dateRangeBackwards($duration, $option)));  /* @phpstan-ignore-line */
    }

    /**
     * @return array<string,array{0:DateInterval|string|int, 1:InitialDatePresence, 2:int}>
     */
    public static function providerGetDatePeriodBackwards(): array
    {
        return [
            'useDateInterval' => [new DateInterval('PT1H'), InitialDatePresence::Included, 24],
            'useString' => ['2 HOUR', InitialDatePresence::Included, 12],
            'useInt' => [9600, InitialDatePresence::Included, 9],
            'exclude start date useDateInterval' => [new DateInterval('PT1H'), InitialDatePresence::Excluded, 23],
            'exclude start date useString' => ['2 HOUR', InitialDatePresence::Excluded, 11],
            'exclude start date useInt' => [9600, InitialDatePresence::Excluded, 8],
            'exclude start date useFloat' => [14400, InitialDatePresence::Excluded, 5],
        ];
    }

    /**
     * @param array<DateTimeImmutable> $range
     */
    #[DataProvider('provideRangedData')]
    public function testRangeForwards(Period $period, DateInterval $dateInterval, int $count, array $range): void
    {
        $result = iterator_to_array($period->rangeForward($dateInterval));

        self::assertEquals($range, $result);
        self::assertCount($count, $result);
    }

    /**
     * @param array<DateTimeImmutable> $range
     */
    #[DataProvider('provideRangedData')]
    public function testRangeBackwards(Period $period, DateInterval $dateInterval, int $count, array $range): void
    {
        $result = iterator_to_array($period->rangeBackwards($dateInterval));

        self::assertEquals($range, array_reverse($result));
        self::assertCount($count, $result);
    }

    /**
     * @return iterable<string, array{period:Period, dateInterval:DateInterval, count:int}>
     */
    public static function provideRangedData(): iterable
    {
        $period = Period::fromDate('2012-01-12 00:00:00', '2012-01-12 01:00:00');
        $dateInterval = new DateInterval('PT10M');

        yield 'bounds include start exclude end' => [
            'period' => $period->boundedBy(Bounds::IncludeStartExcludeEnd),
            'dateInterval' => $dateInterval,
            'count' => 6,
            'range' => [
                new DateTimeImmutable('2012-01-12 00:00:00'),
                new DateTimeImmutable('2012-01-12 00:10:00'),
                new DateTimeImmutable('2012-01-12 00:20:00'),
                new DateTimeImmutable('2012-01-12 00:30:00'),
                new DateTimeImmutable('2012-01-12 00:40:00'),
                new DateTimeImmutable('2012-01-12 00:50:00'),
            ],
        ];

        yield 'bounds exclude start include end' => [
            'period' => $period->boundedBy(Bounds::ExcludeStartIncludeEnd),
            'dateInterval' => $dateInterval,
            'count' => 6,
            'range' => [
                new DateTimeImmutable('2012-01-12 00:10:00'),
                new DateTimeImmutable('2012-01-12 00:20:00'),
                new DateTimeImmutable('2012-01-12 00:30:00'),
                new DateTimeImmutable('2012-01-12 00:40:00'),
                new DateTimeImmutable('2012-01-12 00:50:00'),
                new DateTimeImmutable('2012-01-12 01:00:00'),
            ],
        ];

        yield 'bounds include all' => [
            'period' => $period->boundedBy(Bounds::IncludeAll),
            'dateInterval' => $dateInterval,
            'count' => 7,
            'range' => [
                new DateTimeImmutable('2012-01-12 00:00:00'),
                new DateTimeImmutable('2012-01-12 00:10:00'),
                new DateTimeImmutable('2012-01-12 00:20:00'),
                new DateTimeImmutable('2012-01-12 00:30:00'),
                new DateTimeImmutable('2012-01-12 00:40:00'),
                new DateTimeImmutable('2012-01-12 00:50:00'),
                new DateTimeImmutable('2012-01-12 01:00:00'),
            ],
        ];

        yield 'bounds exclude all' => [
            'period' => $period->boundedBy(Bounds::ExcludeAll),
            'dateInterval' => $dateInterval,
            'count' => 5,
            'range' => [
                new DateTimeImmutable('2012-01-12 00:10:00'),
                new DateTimeImmutable('2012-01-12 00:20:00'),
                new DateTimeImmutable('2012-01-12 00:30:00'),
                new DateTimeImmutable('2012-01-12 00:40:00'),
                new DateTimeImmutable('2012-01-12 00:50:00'),
            ],
        ];
    }

    #[DataProvider('durationCompareDataProvider')]
    public function testDurationCompare(Period $interval1, Period $interval2, int $expected): void
    {
        self::assertSame($expected, $interval1->durationCompare($interval2));
    }

    /**
     * @return array<string,array{0:Period, 1:Period, 2:int}>
     */
    public static function durationCompareDataProvider(): array
    {
        return [
            'duration less than' => [
                Period::fromDate('2012-01-01', '2012-01-15'),
                Period::fromDate('2013-01-01', '2013-01-16'),
                -1,
            ],
            'duration greater than' => [
                Period::fromDate('2012-01-01', '2012-01-15'),
                Period::fromDate('2012-01-01', '2012-01-07'),
                1,
            ],
            'duration equals with microsecond' => [
                Period::fromDate('2012-01-01 00:00:00', '2012-01-03 00:00:00.123456'),
                Period::fromDate('2012-02-02 00:00:00', '2012-02-04 00:00:00.123456'),
                0,
            ],
            'duration with DST' => [
                Period::fromDate('2014-03-01', '2014-04-01'),
                Period::fromDate('2014-03-01', '2014-04-01'),
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

        $period5 = Period::fromDate('2012-01-01 00:00:00', '2012-01-03 00:00:00');
        $period6 = Period::fromDate('2012-02-02 00:00:00', '2012-02-04 00:00:00');

        self::assertTrue($period5->durationEquals($period6));
        self::assertTrue($period5->durationGreaterThanOrEquals($period6));
        self::assertTrue($period5->durationLessThanOrEquals($period6));
    }

    public function testDateIntervalDiff(): void
    {
        $orig = Period::after('2012-01-01', '1 HOUR');
        $alt = Period::after('2012-01-01', '2 HOUR');

        self::assertSame(1, $orig->dateIntervalDiff($alt)->h);
        self::assertSame(0, $orig->dateIntervalDiff($alt)->days);
    }

    public function testTimestampIntervalDiff(): void
    {
        $orig = Period::after('2012-01-01', '1 HOUR');
        $alt = Period::after('2012-01-01', '2 HOUR');

        self::assertEquals(-3600, $orig->timeDurationDiff($alt));
    }

    public function testDateIntervalDiffPositionIrrelevant(): void
    {
        $orig = Period::after('2012-01-01', '1 HOUR');
        $alt = Period::after('2012-01-01', '2 HOUR');
        $fromOrig = $orig->dateIntervalDiff($alt);
        $fromOrig->invert = 1;

        self::assertEquals($fromOrig, $alt->dateIntervalDiff($orig));
    }

    public function testSplit(): void
    {
        $period = Period::fromDate('2012-01-12', '2012-01-13');
        /** @var Generator<Period> $range */
        $range = $period->splitForward(new DateInterval('PT1H'));

        self::assertSame(24, iterator_count($range));
    }

    public function testSplitMustRecreateParentObject(): void
    {
        $period = Period::fromDate('2012-01-12', '2012-01-13');
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
        $period = Period::fromDate('2012-01-12', '2012-01-13');
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
        $period = Period::fromDate('2012-01-12', '2012-01-13');

        foreach ($period->splitForward('10 HOURS') as $innerPeriod) {
            $last = $innerPeriod;
        }
        self::assertNotNull($last);
        self::assertSame(14400, $last->timeDuration());
    }

    public function testSplitBackwards(): void
    {
        $period = Period::fromDate('2015-01-01', '2015-01-04');
        $range = $period->splitBackwards('1 DAY');
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
        $period = Period::fromDate('2010-01-01', '2010-01-02');
        $last = null;
        foreach ($period->splitBackwards(new DateInterval('PT10H')) as $innerPeriod) {
            $last = $innerPeriod;
        }

        self::assertNotNull($last);
        self::assertEquals(14400, $last->timeDuration());
    }

    public function testSplitDaylightSavingsDayIntoHoursEndInterval(): void
    {
        date_default_timezone_set('Canada/Central');
        $period = Period::fromDate('2018-11-04 00:00:00.000000', '2018-11-04 05:00:00.000000');
        /** @var Generator<Period> $splits */
        $splits = $period->splitForward('30 MINUTES');

        self::assertSame(10, iterator_count($splits));
    }

    public function testSplitBackwardsDaylightSavingsDayIntoHoursStartInterval(): void
    {
        date_default_timezone_set('Canada/Central');
        $period = Period::fromDate('2018-04-11 00:00:00.000000', '2018-04-11 05:00:00.000000');
        /** @var Generator<Period> $splits */
        $splits = $period->splitBackwards(new DateInterval('PT30M'));

        self::assertSame(10, iterator_count($splits));
    }
}
