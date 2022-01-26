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

/**
 * @coversDefaultClass \League\Period\Period
 */
class PeriodRelationTest extends TestCase
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

    /**
     * @dataProvider isBeforeProvider
     *
     */
    public function testIsBefore(Period $interval, DateTimeInterface|Period $input, bool $expected): void
    {
        self::assertSame($expected, $interval->isBefore($input));
    }

    /**
     * @return array<string, array{interval:Period, input:DateTimeInterface|Period, expected:bool}>
     */
    public function isBeforeProvider(): array
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
                'interval' => Period::after(new DateTimeImmutable('2012-01-01'), DateInterval::createFromDateString('1 MONTH'), Bounds::EXCLUDE_START_INCLUDE_END),
                'input' => new DateTime('2015-01-01'),
                'expected' => true,
            ],
            'range exclude start date fails' => [
                'interval' => Period::after(new DateTimeImmutable('2012-01-01'), DateInterval::createFromDateString('1 MONTH'), Bounds::EXCLUDE_START_INCLUDE_END),
                'input' => new DateTime('2010-01-01'),
                'expected' => false,
            ],
            'range exclude start date abuts date success' => [
                'interval' => Period::after(new DateTimeImmutable('2012-01-01'), DateInterval::createFromDateString('1 MONTH'), Bounds::EXCLUDE_START_INCLUDE_END),
                'input' => new DateTime('2012-02-01'),
                'expected' => false,
            ],
            'exclude end date is before interval' => [
                'interval' => Period::fromMonth(2012, 1),
                'input' => Period::fromMonth(2011, 1),
                'expected' => false,
            ],
            'exclude end date is not before interval' => [
                'interval' => Period::fromMonth(2012, 1),
                'input' => Period::fromMonth(2013, 1),
                'expected' => true,
            ],
            'exclude end date abuts interval start date' => [
                'interval' => Period::fromMonth(2012, 1),
                'input' => Period::fromMonth(2012, 2),
                'expected' => true,
            ],
            'exclude start date is before interval' => [
                'interval' => Period::after(new DateTimeImmutable('2012-01-01'), DateInterval::createFromDateString('1 MONTH'), Bounds::EXCLUDE_START_INCLUDE_END),
                'input' => Period::fromMonth(2012, 2),
                'expected' => true,
            ],
            'exclude start date is not before interval' => [
                'interval' => Period::after(new DateTimeImmutable('2012-01-01'), DateInterval::createFromDateString('1 MONTH'), Bounds::EXCLUDE_START_INCLUDE_END),
                'input' => Period::fromMonth(2012, 2),
                'expected' => true,
            ],
            'exclude start date abuts interval start date' => [
                'interval' => Period::after(new DateTimeImmutable('2011-01-01'), DateInterval::createFromDateString('1 MONTH'), Bounds::EXCLUDE_START_INCLUDE_END),
                'input' => Period::after(new DateTimeImmutable('2012-01-01'), DateInterval::createFromDateString('1 MONTH'), Bounds::EXCLUDE_START_INCLUDE_END),
                'expected' => true,
            ],
        ];
    }

    /**
     * @dataProvider isAfterProvider
     *
     */
    public function testIsAfer(Period $interval, DateTimeInterface|Period $input, bool $expected): void
    {
        self::assertSame($expected, $interval->isAfter($input));
    }

    /**
     * @return array<string, array{interval:Period, input:DateTimeInterface|Period, expected:bool}>
     */
    public function isAfterProvider(): array
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
                'interval' => Period::after(new DateTimeImmutable('2012-01-01'), DateInterval::createFromDateString('1 MONTH'), Bounds::EXCLUDE_START_INCLUDE_END),
                'input' => new DateTime('2012-02-01'),
                'expected' => false,
            ],
            'range exclude start date success' => [
                'interval' => Period::after(new DateTimeImmutable('2012-01-01'), DateInterval::createFromDateString('1 MONTH'), Bounds::EXCLUDE_START_INCLUDE_END),
                'input' => new DateTime('2012-01-01'),
                'expected' => true,
            ],
            'exclude end date is before interval' => [
                'interval' => Period::fromMonth(2012, 1),
                'input' => Period::fromMonth(2011, 1),
                'expected' => true,
            ],
            'exclude end date is not before interval' => [
                'interval' => Period::fromMonth(2013, 1),
                'input' => Period::fromMonth(2012, 1),
                'expected' => true,
            ],
            'exclude end date abuts interval start date' => [
                'interval' => Period::fromMonth(2012, 2),
                'input' => Period::fromMonth(2012, 1),
                'expected' => true,
            ],
            'exclude start date is before interval' => [
                'interval' => Period::fromMonth(2012, 2),
                'input' => Period::after(new DateTimeImmutable('2012-01-01'), DateInterval::createFromDateString('1 MONTH'), Bounds::EXCLUDE_START_INCLUDE_END),
                'expected' => true,
            ],
            'exclude start date is not before interval' => [
                'interval' => Period::fromMonth(2012, 2),
                'input' => Period::after(new DateTimeImmutable('2012-01-01'), DateInterval::createFromDateString('1 MONTH'), Bounds::EXCLUDE_START_INCLUDE_END),
                'expected' => true,
            ],
            'exclude start date abuts interval start date' => [
                'interval' => Period::after(new DateTimeImmutable('2012-01-01'), DateInterval::createFromDateString('1 MONTH'), Bounds::EXCLUDE_START_INCLUDE_END),
                'input' => Period::after(new DateTimeImmutable('2011-12-01'), DateInterval::createFromDateString('1 MONTH'), Bounds::EXCLUDE_START_INCLUDE_END),
                'expected' => true,
            ],
            'exclude start date abuts interval start date -2-' => [
                'interval' => Period::fromMonth(2012, 1),
                'input' => Period::fromMonth(2012, 2),
                'expected' => false,
            ],
        ];
    }

    /**
     * @dataProvider abutsDataProvider
     */
    public function testAbuts(Period $interval, Period $arg, bool $expected): void
    {
        self::assertSame($expected, $interval->abuts($arg));
    }

    /**
     * @return array<string, array{0:Period, 1:Period, 2:bool}>
     */
    public function abutsDataProvider(): array
    {
        return [
            'test abuts returns true with equal datepoints by defaut' => [
                Period::fromDate(new DateTimeImmutable('2012-01-01'), new DateTimeImmutable('2012-02-01')),
                Period::fromDate(new DateTimeImmutable('2012-02-01'), new DateTimeImmutable('2012-05-01')),
                true,
            ],
            'test abuts returns fase without equal datepoints' => [
                Period::fromDate(new DateTimeImmutable('2012-01-01'), new DateTimeImmutable('2012-02-01')),
                Period::fromDate(new DateTimeImmutable('2012-01-01'), new DateTimeImmutable('2012-03-01')),
                false,
            ],
            'test abuts returns true with equal datepoints by if boundary is inclusif (1)' => [
                Period::fromDate(new DateTimeImmutable('2012-01-01'), new DateTimeImmutable('2012-02-01'), Bounds::INCLUDE_ALL),
                Period::fromDate(new DateTimeImmutable('2012-02-01'), new DateTimeImmutable('2012-05-01'), Bounds::INCLUDE_ALL),
                false,
            ],
            'test abuts returns true with equal datepoints by if boundary is inclusif (2)' => [
                Period::fromDate(new DateTimeImmutable('2012-02-01'), new DateTimeImmutable('2012-05-01'), Bounds::INCLUDE_ALL),
                Period::fromDate(new DateTimeImmutable('2012-01-01'), new DateTimeImmutable('2012-02-01'), Bounds::INCLUDE_ALL),
                false,
            ],
        ];
    }

    /**
     * @dataProvider overlapsDataProvider
     */
    public function testOverlaps(Period $interval, Period $arg, bool $expected): void
    {
        self::assertSame($expected, $interval->overlaps($arg));
    }

    /**
     * @return array<string, array{0:Period, 1:Period, 2:bool}>
     */
    public function overlapsDataProvider(): array
    {
        return [
            'overlaps returns false with gapped intervals' => [
                Period::fromDate(new DateTimeImmutable('2014-03-01'), new DateTimeImmutable('2014-04-01')),
                Period::fromDate(new DateTimeImmutable('2013-04-01'), new DateTimeImmutable('2013-05-01')),
                false,
            ],
            'overlaps returns false with abuts intervals' => [
                Period::fromDate(new DateTimeImmutable('2014-03-01'), new DateTimeImmutable('2014-04-01')),
                Period::fromDate(new DateTimeImmutable('2014-04-01'), new DateTimeImmutable('2014-05-01')),
                false,
            ],
            'overlaps returns' => [
                Period::fromDate(new DateTimeImmutable('2014-03-01'), new DateTimeImmutable('2014-04-01')),
                Period::fromDate(new DateTimeImmutable('2014-03-15'), new DateTimeImmutable('2014-04-07')),
                true,
            ],
            'overlaps returns with equals intervals' => [
                Period::fromDate(new DateTimeImmutable('2014-03-01'), new DateTimeImmutable('2014-04-01')),
                Period::fromDate(new DateTimeImmutable('2014-03-01'), new DateTimeImmutable('2014-04-01')),
                true,
            ],
            'overlaps returns with contained intervals' => [
                Period::fromDate(new DateTimeImmutable('2014-03-01'), new DateTimeImmutable('2014-04-01')),
                Period::fromDate(new DateTimeImmutable('2014-03-13'), new DateTimeImmutable('2014-03-15')),
                true,
            ],
            'overlaps returns with contained intervals backwards' => [
                Period::fromDate(new DateTimeImmutable('2014-03-13'), new DateTimeImmutable('2014-03-15')),
                Period::fromDate(new DateTimeImmutable('2014-03-01'), new DateTimeImmutable('2014-04-01')),
                true,
            ],
        ];
    }

    /**
     * @dataProvider containsDataProvider
     *
     */
    public function testContains(Period $interval, DateTimeInterface|Period|string $arg, bool $expected): void
    {
        if (is_string($arg)) {
            $arg = DatePoint::fromDateString($arg);
        }

        self::assertSame($expected, $interval->contains($arg));
        if ($arg instanceof Period) {
            self::assertSame($expected, $arg->isDuring($interval));
        }
    }

    /**
     * @return array<string, array{0:Period, 1:Period|DateTimeInterface|string, 2:bool}>
     */
    public function containsDataProvider(): array
    {
        return [
            'contains returns true with a DateTimeInterface object' => [
                Period::fromDate(new DateTimeImmutable('2014-03-10'), new DateTimeImmutable('2014-03-15')),
                new DateTime('2014-03-12'),
                true,
            ],
            'contains returns true with a Period object' => [
                Period::fromDate(new DateTimeImmutable('2014-01-01'), new DateTimeImmutable('2014-06-01')),
                Period::fromDate(new DateTimeImmutable('2014-01-01'), new DateTimeImmutable('2014-04-01')),
                true,
            ],
            'contains returns true with a Period object (2)' => [
                Period::fromDate(
                    new DateTimeImmutable('2014-03-01'),
                    new DateTimeImmutable('2014-06-01'),
                    Bounds::EXCLUDE_START_INCLUDE_END
                ),
                Period::fromDate(
                    new DateTimeImmutable('2014-05-01'),
                    new DateTimeImmutable('2014-06-01'),
                    Bounds::EXCLUDE_START_INCLUDE_END
                ),
                true,
            ],
            'contains returns true with a Period object (3)' => [
                Period::fromDate(
                    new DateTimeImmutable('2014-03-01'),
                    new DateTimeImmutable('2014-06-01'),
                    Bounds::EXCLUDE_ALL
                ),
                Period::fromDate(
                    new DateTimeImmutable('2014-05-01'),
                    new DateTimeImmutable('2014-06-01'),
                    Bounds::EXCLUDE_ALL
                ),
                true,
            ],
            'contains returns true with a Period object (4)' => [
                Period::fromDate(
                    new DateTimeImmutable('2014-03-01'),
                    new DateTimeImmutable('2014-06-01'),
                    Bounds::INCLUDE_ALL
                ),
                Period::fromDate(
                    new DateTimeImmutable('2014-05-01'),
                    new DateTimeImmutable('2014-06-01'),
                    Bounds::INCLUDE_ALL
                ),
                true,
            ],
            'contains returns false with a DateTimeInterface object' => [
                Period::fromDate(new DateTimeImmutable('2014-03-13'), new DateTimeImmutable('2014-03-15')),
                new DateTime('2015-03-12'),
                false,
            ],
            'contains returns false with a DateTimeInterface object after the interval' => [
                Period::fromDate(new DateTimeImmutable('2014-03-13'), new DateTimeImmutable('2014-03-15')),
                new DateTime('2012-03-12'),
                false,
            ],
            'contains returns false with a DateTimeInterface object before the interval' => [
                Period::fromDate(new DateTimeImmutable('2014-03-13'), new DateTimeImmutable('2014-03-15')),
                new DateTime('2014-04-01'),
                false,
            ],
            'contains returns false with abuts interval' => [
                Period::fromDate(new DateTimeImmutable('2014-01-01'), new DateTimeImmutable('2014-04-01')),
                Period::fromDate(new DateTimeImmutable('2014-01-01'), new DateTimeImmutable('2014-06-01')),
                false,
            ],
            'contains returns true with a Period objects sharing the same end date' => [
                Period::fromDate(new DateTimeImmutable('2015-01-01'), new DateTimeImmutable('2016-01-01')),
                Period::fromDate(new DateTimeImmutable('2015-12-01'), new DateTimeImmutable('2016-01-01')),
                true,
            ],
            'contains returns false with O duration Period object' => [
                Period::fromDate(new DateTimeImmutable('2012-03-12'), new DateTimeImmutable('2012-03-12')),
                new DateTime('2012-03-12'),
                false,
            ],
            'contains datetime edge case datetime equals start date' => [
                Period::after(new DateTimeImmutable('2012-01-08'), DateInterval::createFromDateString('1 DAY')),
                new DateTime('2012-01-08'),
                true,
            ],
            'contains datetime edge case datetime equals end date' => [
                Period::after(new DateTimeImmutable('2012-01-08'), DateInterval::createFromDateString('1 DAY')),
                new DateTime('2012-01-09'),
                false,
            ],
            'contains datetime edge case datetime equals start date OLCR interval' => [
                Period::after(new DateTimeImmutable('2012-01-08'), DateInterval::createFromDateString('1 DAY'), Bounds::EXCLUDE_START_INCLUDE_END),
                new DateTime('2012-01-08'),
                false,
            ],
            'contains datetime edge case datetime equals end date CLCR interval' => [
                Period::after(new DateTimeImmutable('2012-01-08'), DateInterval::createFromDateString('1 DAY'), Bounds::EXCLUDE_ALL),
                new DateTime('2012-01-09'),
                false,
            ],
            'contains period same duration + boundary type CLCR vs CLCR' => [
                Period::after(new DateTimeImmutable('2012-01-08'), DateInterval::createFromDateString('1 DAY'), Bounds::EXCLUDE_ALL),
                Period::after(new DateTimeImmutable('2012-01-08'), DateInterval::createFromDateString('1 DAY'), Bounds::EXCLUDE_ALL),
                true,
            ],
            'contains period same duration + boundary type OLOR vs OLOR' => [
                Period::after(new DateTimeImmutable('2012-01-08'), DateInterval::createFromDateString('1 DAY'), Bounds::INCLUDE_ALL),
                Period::after(new DateTimeImmutable('2012-01-08'), DateInterval::createFromDateString('1 DAY'), Bounds::INCLUDE_ALL),
                true,
            ],
            'contains period same duration + boundary type CLOR vs CLOR' => [
                Period::after(new DateTimeImmutable('2012-01-08'), DateInterval::createFromDateString('1 DAY'), Bounds::EXCLUDE_START_INCLUDE_END),
                Period::after(new DateTimeImmutable('2012-01-08'), DateInterval::createFromDateString('1 DAY'), Bounds::EXCLUDE_START_INCLUDE_END),
                true,
            ],
            'contains period same duration + boundary type CLOR vs OLCR' => [
                Period::after(new DateTimeImmutable('2012-01-08'), DateInterval::createFromDateString('1 DAY'), Bounds::EXCLUDE_START_INCLUDE_END),
                Period::after(new DateTimeImmutable('2012-01-08'), DateInterval::createFromDateString('1 DAY'), Bounds::INCLUDE_START_EXCLUDE_END),
                false,
            ],
            'contains period same duration + boundary type OLCR vs CLOR' => [
                Period::after(new DateTimeImmutable('2012-01-08'), DateInterval::createFromDateString('1 DAY'), Bounds::INCLUDE_START_EXCLUDE_END),
                Period::after(new DateTimeImmutable('2012-01-08'), DateInterval::createFromDateString('1 DAY'), Bounds::EXCLUDE_START_INCLUDE_END),
                false,
            ],
            'contains period same duration + boundary type CLCR vs OLOR' => [
                Period::after(new DateTimeImmutable('2012-01-08'), DateInterval::createFromDateString('1 DAY'), Bounds::EXCLUDE_ALL),
                Period::after(new DateTimeImmutable('2012-01-08'), DateInterval::createFromDateString('1 DAY'), Bounds::INCLUDE_ALL),
                false,
            ],
            'contains period same duration + boundary type OLOR vs CLCR' => [
                Period::after(new DateTimeImmutable('2012-01-08'), DateInterval::createFromDateString('1 DAY'), Bounds::INCLUDE_ALL),
                Period::after(new DateTimeImmutable('2012-01-08'), DateInterval::createFromDateString('1 DAY'), Bounds::EXCLUDE_ALL),
                true,
            ],
        ];
    }

    /**
     * @dataProvider startsDataProvider
     * @param DateTimeInterface|Period $index
     */
    public function testStarts(Period $interval, $index, bool $expected): void
    {
        self::assertSame($expected, $interval->isStartedBy($index));
        if ($index instanceof DateTimeInterface) {
            self::assertSame($expected, DatePoint::fromDate($index)->isStarting($interval));
        }
    }

    /**
     * @return array<array{0:Period, 1:Period|DateTimeInterface, 2:bool}>
     */
    public function startsDataProvider(): array
    {
        $startingDate = new DateTime('2012-01-01');
        $interval = Period::fromDate($startingDate, new DateTime('2012-01-15'));

        return [
            [
                $interval,
                $interval,
                true,
            ],
            [
                $interval,
                $interval->moveEndDate(Duration::fromDateString('+3 MINUTES')),
                true,
            ],
            [
                $interval,
                $interval->moveStartDate(Duration::fromDateString('+3 MINUTES')),
                false,
            ],
            [
                $interval->boundedBy(Bounds::INCLUDE_ALL),
                $interval,
                true,
            ],
            [
                $interval->boundedBy(Bounds::EXCLUDE_ALL),
                $interval->boundedBy(Bounds::INCLUDE_ALL),
                false,
            ],
            [
                $interval->boundedBy(Bounds::EXCLUDE_ALL),
                $startingDate,
                false,
            ],
            [
                $interval->boundedBy(Bounds::INCLUDE_START_EXCLUDE_END),
                $startingDate,
                true,
            ],
        ];
    }

    /**
     * @dataProvider finishesDataProvider
     */
    public function testFinishes(Period $interval, DateTimeInterface|Period $index, bool $expected): void
    {
        self::assertSame($expected, $interval->isEndedBy($index));
    }

    /**
     * @return array<array{0:Period, 1:Period|DateTimeInterface, 2:bool}>
     */
    public function finishesDataProvider(): array
    {
        $endingDate = new DateTime('2012-01-16');
        $interval = Period::fromDate(new DateTime('2012-01-01'), $endingDate);
        return [
            [
                $interval,
                $interval,
                true,
            ],
            [
                $interval->moveEndDate(Duration::fromDateString('+ 3 MINUTES')),
                $interval,
                false,
            ],
            [
                $interval,
                $interval->boundedBy(Bounds::EXCLUDE_ALL),
                true,
            ],
            [
                $interval->boundedBy(Bounds::EXCLUDE_ALL),
                $interval->boundedBy(Bounds::INCLUDE_ALL),
                false,
            ],
            [
                $interval->boundedBy(Bounds::EXCLUDE_ALL),
                $endingDate,
                false,
            ],
            [
                $interval->boundedBy(Bounds::INCLUDE_ALL),
                $endingDate,
                true,
            ],
        ];
    }

    /**
     * @dataProvider equalsDataProvider
     */
    public function testEquals(Period  $interval1, Period $interval2, bool $expected): void
    {
        self::assertSame($expected, $interval1->equals($interval2));
    }

    /**
     * @return array<string, array{0:Period, 1:Period, 2:bool}>
     */
    public function equalsDataProvider(): array
    {
        return [
            'returns true' => [
                Period::fromDate(new DateTime('2012-01-01 00:00:00'), new DateTime('2012-01-03 00:00:00')),
                Period::fromDate(new DateTime('2012-01-01 00:00:00'), new DateTime('2012-01-03 00:00:00')),
                true,
            ],
            'returns false' => [
                Period::fromDate(new DateTime('2012-01-01'), new DateTime('2012-01-15')),
                Period::fromDate(new DateTime('2012-01-01'), new DateTime('2012-01-07')),
                false,
            ],
            'returns false is argument order independent' => [
                Period::fromDate(new DateTime('2012-01-01'), new DateTime('2012-01-07')),
                Period::fromDate(new DateTime('2012-01-01'), new DateTime('2012-01-15')),
                false,
            ],
            'returns false with different range type' => [
                Period::fromDate(new DateTime('2012-01-01'), new DateTime('2012-01-15'), Bounds::INCLUDE_ALL),
                Period::fromDate(new DateTime('2012-01-01'), new DateTime('2012-01-15'), Bounds::EXCLUDE_ALL),
                false,
            ],
        ];
    }

    public function testIntersect(): void
    {
        $orig = Period::fromDate(new DateTime('2011-12-01'), new DateTime('2012-04-01'));
        $alt = Period::fromDate(new DateTime('2012-01-01'), new DateTime('2012-03-01'));
        self::assertTrue($orig->intersect($alt)->equals(Period::fromDate(new DateTime('2012-01-01'), new DateTime('2012-03-01'))));
    }

    public function testIntersectThrowsExceptionWithNoOverlappingTimeRange(): void
    {
        $this->expectException(DateRangeUnprocessable::class);
        $orig = Period::fromDate(new DateTime('2013-01-01'), new DateTime('2013-02-01'));
        $alt = Period::fromDate(new DateTime('2012-01-01'), new DateTime('2012-03-01'));
        $orig->intersect($alt);
    }

    /**
     * @dataProvider intersectBoundaryResultProvider
     */
    public function testIntersectBoundaryTypeResult(Bounds $boundary1, Bounds $boundary2, Bounds $expected): void
    {
        $interval0 = Period::fromDate(new DateTime('2014-03-01'), new DateTime('2014-06-01'), $boundary1);
        $interval1 = Period::fromDate(new DateTime('2014-05-01'), new DateTime('2014-08-01'), $boundary2);
        self::assertTrue($expected === $interval0->intersect($interval1)->bounds);
    }

    /**
     * @return array<string, array{boundary1:Bounds, boundary2:Bounds, expected:Bounds}>
     */
    public function intersectBoundaryResultProvider(): array
    {
        return [
            '() + ()' => [
                'boundary1' => Bounds::EXCLUDE_ALL,
                'boundary2' => Bounds::EXCLUDE_ALL,
                'expected' => Bounds::EXCLUDE_ALL,
            ],
            '() + []' => [
                'boundary1' => Bounds::EXCLUDE_ALL,
                'boundary2' => Bounds::INCLUDE_ALL,
                'expected' => Bounds::INCLUDE_START_EXCLUDE_END,
            ],
            '() + [)' => [
                'boundary1' => Bounds::EXCLUDE_ALL,
                'boundary2' => Bounds::INCLUDE_START_EXCLUDE_END,
                'expected' => Bounds::INCLUDE_START_EXCLUDE_END,
            ],
            '() + (]' => [
                'boundary1' => Bounds::EXCLUDE_ALL,
                'boundary2' => Bounds::EXCLUDE_START_INCLUDE_END,
                'expected' => Bounds::EXCLUDE_ALL,
            ],
            '[] + []' => [
                'boundary1' => Bounds::INCLUDE_ALL,
                'boundary2' => Bounds::INCLUDE_ALL,
                'expected' => Bounds::INCLUDE_ALL,
            ],
            '[] + [)' => [
                'boundary1' => Bounds::INCLUDE_ALL,
                'boundary2' => Bounds::INCLUDE_START_EXCLUDE_END,
                'expected' => Bounds::INCLUDE_ALL,
            ],
            '[] + (]' => [
                'boundary1' => Bounds::INCLUDE_ALL,
                'boundary2' => Bounds::EXCLUDE_START_INCLUDE_END,
                'expected' => Bounds::EXCLUDE_START_INCLUDE_END,
            ],
            '[] + ()' => [
                'boundary1' => Bounds::INCLUDE_ALL,
                'boundary2' => Bounds::EXCLUDE_ALL,
                'expected' => Bounds::EXCLUDE_START_INCLUDE_END,
            ],
            '[) + ()' => [
                'boundary1' => Bounds::INCLUDE_START_EXCLUDE_END,
                'boundary2' => Bounds::EXCLUDE_ALL,
                'expected' => Bounds::EXCLUDE_ALL,
            ],
            '[) + []' => [
                'boundary1' => Bounds::INCLUDE_START_EXCLUDE_END,
                'boundary2' => Bounds::INCLUDE_ALL,
                'expected' => Bounds::INCLUDE_START_EXCLUDE_END,
            ],
            '[) + (]' => [
                'boundary1' => Bounds::INCLUDE_START_EXCLUDE_END,
                'boundary2' => Bounds::EXCLUDE_START_INCLUDE_END,
                'expected' => Bounds::EXCLUDE_ALL,
            ],
            '[) + [)' => [
                'boundary1' => Bounds::INCLUDE_START_EXCLUDE_END,
                'boundary2' => Bounds::INCLUDE_START_EXCLUDE_END,
                'expected' => Bounds::INCLUDE_START_EXCLUDE_END,
            ],
            '(] + ()' => [
                'boundary1' => Bounds::EXCLUDE_START_INCLUDE_END,
                'boundary2' => Bounds::EXCLUDE_ALL,
                'expected' => Bounds::EXCLUDE_START_INCLUDE_END,
            ],
            '(] + []' => [
                'boundary1' => Bounds::EXCLUDE_START_INCLUDE_END,
                'boundary2' => Bounds::INCLUDE_ALL,
                'expected' => Bounds::INCLUDE_ALL,
            ],
            '(] + (]' => [
                'boundary1' => Bounds::EXCLUDE_START_INCLUDE_END,
                'boundary2' => Bounds::EXCLUDE_START_INCLUDE_END,
                'expected' => Bounds::EXCLUDE_START_INCLUDE_END,
            ],
            '(] + [)' => [
                'boundary1' => Bounds::EXCLUDE_START_INCLUDE_END,
                'boundary2' => Bounds::INCLUDE_START_EXCLUDE_END,
                'expected' => Bounds::INCLUDE_ALL,
            ],
        ];
    }

    public function testGap(): void
    {
        $orig = Period::fromDate(new DateTime('2011-12-01'), new DateTime('2012-02-01'));
        $alt = Period::fromDate(new DateTime('2012-06-01'), new DateTime('2012-09-01'));
        $gap = $orig->gap($alt);

        self::assertEquals($orig->endDate, $gap->startDate);
        self::assertEquals($alt->startDate, $gap->endDate);
        self::assertTrue($gap->equals($alt->gap($orig)));
    }

    public function testGapThrowsExceptionWithOverlapsInterval(): void
    {
        $this->expectException(DateRangeUnprocessable::class);
        $orig = Period::fromDate(new DateTime('2011-12-01'), new DateTime('2012-02-01'));
        $alt = Period::fromDate(new DateTime('2011-12-10'), new DateTime('2011-12-15'));
        $orig->gap($alt);
    }

    public function testGapWithSameStartingInterval(): void
    {
        $this->expectException(DateRangeUnprocessable::class);
        $orig = Period::fromDate(new DateTime('2011-12-01'), new DateTime('2012-02-01'));
        $alt = Period::fromDate(new DateTime('2011-12-01'), new DateTime('2011-12-15'));
        $orig->gap($alt);
    }

    public function testGapWithSameEndingInterval(): void
    {
        $this->expectException(DateRangeUnprocessable::class);
        $orig = Period::fromDate(new DateTime('2011-12-01'), new DateTime('2012-02-01'));
        $alt = Period::fromDate(new DateTime('2012-01-15'), new DateTime('2012-02-01'));
        $orig->gap($alt);
    }

    public function testGapWithAdjacentInterval(): void
    {
        $orig = Period::fromDate(new DateTime('2011-12-01'), new DateTime('2012-02-01'));
        $alt = Period::fromDate(new DateTime('2012-02-01'), new DateTime('2012-02-02'));
        self::assertEquals(0, $orig->gap($alt)->seconds());
    }

    /**
     * @dataProvider gapBoundaryResultProvider
     */
    public function testGapBoundaryTypeResult(Bounds $boundary1, Bounds $boundary2, Bounds $expected): void
    {
        $interval0 = Period::fromDate(new DateTime('2014-03-01'), new DateTime('2014-06-01'), $boundary1);
        $interval1 = Period::fromDate(new DateTime('2014-07-01'), new DateTime('2014-09-01'), $boundary2);
        self::assertTrue($interval0->gap($interval1)->bounds === $expected);
    }

    /**
     * @return array<string, array{boundary1:Bounds, boundary2:Bounds, expected:Bounds}>
     */
    public function gapBoundaryResultProvider(): array
    {
        return [
            '() + ()' => [
                'boundary1' => Bounds::EXCLUDE_ALL,
                'boundary2' => Bounds::EXCLUDE_ALL,
                'expected' => Bounds::INCLUDE_ALL,
            ],
            '() + []' => [
                'boundary1' => Bounds::EXCLUDE_ALL,
                'boundary2' => Bounds::INCLUDE_ALL,
                'expected' => Bounds::INCLUDE_START_EXCLUDE_END,
            ],
            '() + [)' => [
                'boundary1' => Bounds::EXCLUDE_ALL,
                'boundary2' => Bounds::INCLUDE_START_EXCLUDE_END,
                'expected' => Bounds::INCLUDE_START_EXCLUDE_END,
            ],
            '() + (]' => [
                'boundary1' => Bounds::EXCLUDE_ALL,
                'boundary2' => Bounds::EXCLUDE_START_INCLUDE_END,
                'expected' => Bounds::INCLUDE_ALL,
            ],
            '[] + []' => [
                'boundary1' => Bounds::INCLUDE_ALL,
                'boundary2' => Bounds::INCLUDE_ALL,
                'expected' => Bounds::EXCLUDE_ALL,
            ],
            '[] + [)' => [
                'boundary1' => Bounds::INCLUDE_ALL,
                'boundary2' => Bounds::INCLUDE_START_EXCLUDE_END,
                'expected' => Bounds::EXCLUDE_ALL,
            ],
            '[] + (]' => [
                'boundary1' => Bounds::INCLUDE_ALL,
                'boundary2' => Bounds::EXCLUDE_START_INCLUDE_END,
                'expected' => Bounds::EXCLUDE_START_INCLUDE_END,
            ],
            '[] + ()' => [
                'boundary1' => Bounds::INCLUDE_ALL,
                'boundary2' => Bounds::EXCLUDE_ALL,
                'expected' => Bounds::EXCLUDE_START_INCLUDE_END,
            ],
            '[) + ()' => [
                'boundary1' => Bounds::INCLUDE_START_EXCLUDE_END,
                'boundary2' => Bounds::EXCLUDE_ALL,
                'expected' => Bounds::INCLUDE_ALL,
            ],
            '[) + []' => [
                'boundary1' => Bounds::INCLUDE_START_EXCLUDE_END,
                'boundary2' => Bounds::INCLUDE_ALL,
                'expected' => Bounds::INCLUDE_START_EXCLUDE_END,
            ],
            '[) + (]' => [
                'boundary1' => Bounds::INCLUDE_START_EXCLUDE_END,
                'boundary2' => Bounds::EXCLUDE_START_INCLUDE_END,
                'expected' => Bounds::INCLUDE_ALL,
            ],
            '[) + [)' => [
                'boundary1' => Bounds::INCLUDE_START_EXCLUDE_END,
                'boundary2' => Bounds::INCLUDE_START_EXCLUDE_END,
                'expected' => Bounds::INCLUDE_START_EXCLUDE_END,
            ],
            '(] + ()' => [
                'boundary1' => Bounds::EXCLUDE_START_INCLUDE_END,
                'boundary2' => Bounds::EXCLUDE_ALL,
                'expected' => Bounds::EXCLUDE_START_INCLUDE_END,
            ],
            '(] + []' => [
                'boundary1' => Bounds::EXCLUDE_START_INCLUDE_END,
                'boundary2' => Bounds::INCLUDE_ALL,
                'expected' => Bounds::EXCLUDE_ALL,
            ],
            '(] + (]' => [
                'boundary1' => Bounds::EXCLUDE_START_INCLUDE_END,
                'boundary2' => Bounds::EXCLUDE_START_INCLUDE_END,
                'expected' => Bounds::EXCLUDE_START_INCLUDE_END,
            ],
            '(] + [)' => [
                'boundary1' => Bounds::EXCLUDE_START_INCLUDE_END,
                'boundary2' => Bounds::INCLUDE_START_EXCLUDE_END,
                'expected' => Bounds::EXCLUDE_ALL,
            ],
        ];
    }

    public function testDiffThrowsException(): void
    {
        $interval1 = Period::fromDate(new DateTimeImmutable('2015-01-01'), new DateTimeImmutable('2016-01-01'));
        $interval2 = Period::fromDate(new DateTimeImmutable('2013-01-01'), new DateTimeImmutable('2014-01-01'));

        $this->expectException(DateRangeUnprocessable::class);
        $interval1->diff($interval2);
    }

    public function testDiffWithEqualsPeriod(): void
    {
        $period = Period::fromDate(new DateTimeImmutable('2013-01-01'), new DateTimeImmutable('2014-01-01'));
        $alt = Period::fromDate(new DateTimeImmutable('2013-01-01'), new DateTimeImmutable('2014-01-01'));

        self::assertTrue($alt->diff($period)->isEmpty());
        self::assertEquals($alt->diff($period), $period->diff($alt));
    }

    public function testDiffWithPeriodSharingStartingDatepoints(): void
    {
        $period = Period::fromDate(new DateTimeImmutable('2013-01-01'), new DateTimeImmutable('2014-01-01'));
        $alt = Period::fromDate(new DateTimeImmutable('2013-01-01'), new DateTimeImmutable('2013-04-01'));
        $sequence = $alt->diff($period);

        self::assertCount(1, $sequence);
        self::assertEquals(new DateTimeImmutable('2013-04-01'), $sequence[0]->startDate);
        self::assertEquals(new DateTimeImmutable('2014-01-01'), $sequence[0]->endDate);
        self::assertEquals($alt->diff($period), $period->diff($alt));
    }

    public function testDiffWithPeriodSharingEndingDatepoints(): void
    {
        $period = Period::fromDate(new DateTimeImmutable('2013-01-01'), new DateTimeImmutable('2014-01-01'));
        $alt = Period::fromDate(new DateTimeImmutable('2013-10-01'), new DateTimeImmutable('2014-01-01'));
        $sequence = $alt->diff($period);

        self::assertCount(1, $sequence);
        self::assertEquals(new DateTimeImmutable('2013-01-01'), $sequence[0]->startDate);
        self::assertEquals(new DateTimeImmutable('2013-10-01'), $sequence[0]->endDate);
        self::assertEquals($alt->diff($period), $period->diff($alt));
    }

    public function testDiffWithOverlapsPeriod(): void
    {
        $period = Period::fromDate(new DateTimeImmutable('2013-01-01 10:00:00'), new DateTimeImmutable('2013-01-01 13:00:00'));
        $alt = Period::fromDate(new DateTimeImmutable('2013-01-01 11:00:00'), new DateTimeImmutable('2013-01-01 14:00:00'));
        $sequence = $alt->diff($period);

        self::assertCount(2, $sequence);
        self::assertSame(3600, $sequence[0]->seconds());
        self::assertSame(3600, $sequence[1]->seconds());
        self::assertEquals($alt->diff($period), $period->diff($alt));
    }

    /**
     * @dataProvider diffBoundaryResultProvider
     */
    public function testDiffBoundaryTypeResult(
        Bounds $boundary1,
        Bounds $boundary2,
        Bounds $expected1,
        Bounds $expected2
    ): void {
        $interval0 = Period::fromDate(new DateTimeImmutable('2014-03-01'), new DateTimeImmutable('2014-06-01'), $boundary1);
        $interval1 = Period::fromDate(new DateTimeImmutable('2014-05-01'), new DateTimeImmutable('2014-09-01'), $boundary2);
        $sequence = $interval0->diff($interval1);

        if (0 < count($sequence)) {
            self::assertSame($expected1, $sequence[0]->bounds);
        }

        if (1 < count($sequence)) {
            self::assertSame($expected2, $sequence[1]->bounds);
        }
    }

    /**
     * @return array<string, array{boundary1:Bounds, boundary2:Bounds, expected1:Bounds, expected2:Bounds}>
     */
    public function diffBoundaryResultProvider(): array
    {
        return [
            '() + ()' => [
                'boundary1' => Bounds::EXCLUDE_ALL,
                'boundary2' => Bounds::EXCLUDE_ALL,
                'expected1' => Bounds::EXCLUDE_START_INCLUDE_END,
                'expected2' => Bounds::INCLUDE_START_EXCLUDE_END,
            ],
            '() + []' => [
                'boundary1' => Bounds::EXCLUDE_ALL,
                'boundary2' => Bounds::INCLUDE_ALL,
                'expected1' => Bounds::EXCLUDE_ALL,
                'expected2' => Bounds::INCLUDE_ALL,
            ],
            '() + [)' => [
                'boundary1' => Bounds::EXCLUDE_ALL,
                'boundary2' => Bounds::INCLUDE_START_EXCLUDE_END,
                'expected1' => Bounds::EXCLUDE_ALL,
                'expected2' => Bounds::INCLUDE_START_EXCLUDE_END,
            ],
            '() + (]' => [
                'boundary1' => Bounds::EXCLUDE_ALL,
                'boundary2' => Bounds::EXCLUDE_START_INCLUDE_END,
                'expected1' => Bounds::EXCLUDE_START_INCLUDE_END,
                'expected2' => Bounds::INCLUDE_ALL,
            ],
            '[] + []' => [
                'boundary1' => Bounds::INCLUDE_ALL,
                'boundary2' => Bounds::INCLUDE_ALL,
                'expected1' => Bounds::INCLUDE_START_EXCLUDE_END,
                'expected2' => Bounds::EXCLUDE_START_INCLUDE_END,
            ],
            '[] + [)' => [
                'boundary1' => Bounds::INCLUDE_ALL,
                'boundary2' => Bounds::INCLUDE_START_EXCLUDE_END,
                'expected1' => Bounds::INCLUDE_START_EXCLUDE_END,
                'expected2' => Bounds::EXCLUDE_ALL,
            ],
            '[] + (]' => [
                'boundary1' => Bounds::INCLUDE_ALL,
                'boundary2' => Bounds::EXCLUDE_START_INCLUDE_END,
                'expected1' => Bounds::INCLUDE_ALL,
                'expected2' => Bounds::EXCLUDE_START_INCLUDE_END,
            ],
            '[] + ()' => [
                'boundary1' => Bounds::INCLUDE_ALL,
                'boundary2' => Bounds::EXCLUDE_ALL,
                'expected1' => Bounds::INCLUDE_ALL,
                'expected2' => Bounds::EXCLUDE_ALL,
            ],
            '[) + ()' => [
                'boundary1' => Bounds::INCLUDE_START_EXCLUDE_END,
                'boundary2' => Bounds::EXCLUDE_ALL,
                'expected1' => Bounds::INCLUDE_ALL,
                'expected2' => Bounds::INCLUDE_START_EXCLUDE_END,
            ],
            '[) + []' => [
                'boundary1' => Bounds::INCLUDE_START_EXCLUDE_END,
                'boundary2' => Bounds::INCLUDE_ALL,
                'expected1' => Bounds::INCLUDE_START_EXCLUDE_END,
                'expected2' => Bounds::INCLUDE_ALL,
            ],
            '[) + (]' => [
                'boundary1' => Bounds::INCLUDE_START_EXCLUDE_END,
                'boundary2' => Bounds::EXCLUDE_START_INCLUDE_END,
                'expected1' => Bounds::INCLUDE_ALL,
                'expected2' => Bounds::INCLUDE_ALL,
            ],
            '[) + [)' => [
                'boundary1' => Bounds::INCLUDE_START_EXCLUDE_END,
                'boundary2' => Bounds::INCLUDE_START_EXCLUDE_END,
                'expected1' => Bounds::INCLUDE_START_EXCLUDE_END,
                'expected2' => Bounds::INCLUDE_START_EXCLUDE_END,
            ],
            '(] + ()' => [
                'boundary1' => Bounds::EXCLUDE_START_INCLUDE_END,
                'boundary2' => Bounds::EXCLUDE_ALL,
                'expected1' => Bounds::EXCLUDE_START_INCLUDE_END,
                'expected2' => Bounds::EXCLUDE_ALL,
            ],
            '(] + []' => [
                'boundary1' => Bounds::EXCLUDE_START_INCLUDE_END,
                'boundary2' => Bounds::INCLUDE_ALL,
                'expected1' => Bounds::EXCLUDE_ALL,
                'expected2' => Bounds::EXCLUDE_START_INCLUDE_END,
            ],
            '(] + (]' => [
                'boundary1' => Bounds::EXCLUDE_START_INCLUDE_END,
                'boundary2' => Bounds::EXCLUDE_START_INCLUDE_END,
                'expected1' => Bounds::EXCLUDE_START_INCLUDE_END,
                'expected2' => Bounds::EXCLUDE_START_INCLUDE_END,
            ],
            '(] + [)' => [
                'boundary1' => Bounds::EXCLUDE_START_INCLUDE_END,
                'boundary2' => Bounds::INCLUDE_START_EXCLUDE_END,
                'expected1' => Bounds::EXCLUDE_ALL,
                'expected2' => Bounds::EXCLUDE_ALL,
            ],
        ];
    }

    public function testDiffAndIntersect(): void
    {
        foreach (['[]', '[)', '()', '(]'] as $bound1) {
            foreach (['[]', '[)', '()', '(]'] as $bound2) {
                $interval0 = Period::fromDate(new DateTime('2014-03-01'), new DateTime('2014-06-01'), Bounds::fromNotation($bound1));
                $interval1 = Period::fromDate(new DateTime('2014-05-01'), new DateTime('2014-08-01'), Bounds::fromNotation($bound2));
                $sequence = $interval0->diff($interval1);
                $intersect = $interval0->intersect($interval1);

                if (0 < count($sequence)) {
                    self::assertTrue($sequence[0]->bordersOnStart($intersect));
                }

                if (1 < count($sequence)) {
                    self::assertTrue($sequence[1]->bordersOnEnd($intersect));
                }

                $sequence->push($intersect);
                $period = $sequence->length();
                if (null !== $period) {
                    self::assertTrue($period->equals($interval0->merge($interval1)));
                }
            }
        }
    }

    public function testSubtractWithOverlappingUnequalPeriods(): void
    {
        $periodA = Period::after(new DateTimeImmutable('2000-01-01 10:00:00'), DateInterval::createFromDateString('8 HOURS'));
        $periodB = Period::after(new DateTimeImmutable('2000-01-01 14:00:00'), DateInterval::createFromDateString('6 HOURS'));

        $diff1 = $periodA->subtract($periodB);

        self::assertCount(1, $diff1);
        self::assertEquals($periodA->startDate, $diff1[0]->startDate);
        self::assertEquals($periodB->startDate, $diff1[0]->endDate);

        $diff2 = $periodB->subtract($periodA);

        self::assertCount(1, $diff2);
        self::assertEquals($periodA->endDate, $diff2[0]->startDate);
        self::assertEquals($periodB->endDate, $diff2[0]->endDate);
    }

    public function testSubtractWithSeparatePeriods(): void
    {
        $periodA = Period::after(new DateTimeImmutable('2000-01-01 10:00:00'), DateInterval::createFromDateString('4 HOURS'));
        $periodB = Period::after(new DateTimeImmutable('2000-01-01 15:00:00'), DateInterval::createFromDateString('3 HOURS'));

        $diff1 = $periodA->subtract($periodB);

        self::assertCount(1, $diff1);
        self::assertTrue($diff1[0]->equals($periodA));

        $diff2 = $periodB->subtract($periodA);

        self::assertCount(1, $diff2);
        self::assertTrue($diff2[0]->equals($periodB));
    }

    public function testSubtractWithOnePeriodContainedInAnother(): void
    {
        $periodA = Period::after(new DateTimeImmutable('2000-01-01 10:00:00'), DateInterval::createFromDateString('8 HOURS'));
        $periodB = Period::after(new DateTimeImmutable('2000-01-01 15:00:00'), DateInterval::createFromDateString('1 HOUR'));

        $diff1 = $periodA->subtract($periodB);

        self::assertCount(2, $diff1);
        self::assertEquals($periodA->startDate, $diff1[0]->startDate);
        self::assertEquals($periodB->startDate, $diff1[0]->endDate);
        self::assertEquals($periodB->endDate, $diff1[1]->startDate);
        self::assertEquals($periodA->endDate, $diff1[1]->endDate);

        $diff2 = $periodB->subtract($periodA);

        self::assertCount(0, $diff2);
    }

    public function testSubtractWithEqualPeriodObjec(): void
    {
        $periodA = Period::after(new DateTimeImmutable('2000-01-01 10:00:00'), DateInterval::createFromDateString('8 HOURS'));
        $diff = $periodA->subtract($periodA);

        self::assertCount(0, $diff);
        self::assertEquals($diff, $periodA->subtract($periodA));
    }
}
