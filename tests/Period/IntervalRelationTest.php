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

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use League\Period\Exception;
use League\Period\Period;
use League\Period\Sequence;
use LeagueTest\Period\TestCase;

/**
 * @coversDefaultClass \League\Period\Period
 */
class IntervalRelationTest extends TestCase
{
    /**
     * @dataProvider isBeforeProvider
     *
     * @param DateTimeInterface|Period $input
     */
    public function testIsBefore(Period $interval, $input, bool $expected): void
    {
        self::assertSame($expected, $interval->isBefore($input));
    }

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
                'interval' => Period::after('2012-01-01', '1 MONTH', Period::EXCLUDE_START_INCLUDE_END),
                'input' => new DateTime('2015-01-01'),
                'expected' => true,
            ],
            'range exclude start date fails' => [
                'interval' => Period::after('2012-01-01', '1 MONTH', Period::EXCLUDE_START_INCLUDE_END),
                'input' => new DateTime('2010-01-01'),
                'expected' => false,
            ],
            'range exclude start date abuts date success' => [
                'interval' => Period::after('2012-01-01', '1 MONTH', Period::EXCLUDE_START_INCLUDE_END),
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
                'interval' => Period::after('2012-01-01', '1 MONTH', Period::EXCLUDE_START_INCLUDE_END),
                'input' => Period::fromMonth(2012, 2),
                'expected' => true,
            ],
            'exclude start date is not before interval' => [
                'interval' => Period::after('2012-01-01', '1 MONTH', Period::EXCLUDE_START_INCLUDE_END),
                'input' => Period::fromMonth(2012, 2),
                'expected' => true,
            ],
            'exclude start date abuts interval start date' => [
                'interval' => Period::after('2011-12-01', '1 MONTH', Period::EXCLUDE_START_INCLUDE_END),
                'input' => Period::after('2012-01-01', '1 MONTH', Period::EXCLUDE_START_INCLUDE_END),
                'expected' => true,
            ],
        ];
    }

    /**
     * @dataProvider isAfterProvider
     *
     * @param DateTimeInterface|Period $input
     */
    public function testIsAfer(Period $interval, $input, bool $expected): void
    {
        self::assertSame($expected, $interval->isAfter($input));
    }


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
                'interval' => Period::after('2012-01-01', '1 MONTH', Period::EXCLUDE_START_INCLUDE_END),
                'input' => new DateTime('2012-02-01'),
                'expected' => false,
            ],
            'range exclude start date success' => [
                'interval' => Period::after('2012-01-01', '1 MONTH', Period::EXCLUDE_START_INCLUDE_END),
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
                'input' => Period::after('2012-01-01', '1 MONTH', Period::EXCLUDE_START_INCLUDE_END),
                'expected' => true,
            ],
            'exclude start date is not before interval' => [
                'interval' => Period::fromMonth(2012, 2),
                'input' => Period::after('2012-01-01', '1 MONTH', Period::EXCLUDE_START_INCLUDE_END),
                'expected' => true,
            ],
            'exclude start date abuts interval start date' => [
                'interval' => Period::after('2012-01-01', '1 MONTH', Period::EXCLUDE_START_INCLUDE_END),
                'input' => Period::after('2011-12-01', '1 MONTH', Period::EXCLUDE_START_INCLUDE_END),
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

    public function abutsDataProvider(): array
    {
        return [
            'test abuts returns true with equal datepoints by defaut' => [
                new Period(new DateTimeImmutable('2012-01-01'), new DateTimeImmutable('2012-02-01')),
                new Period(new DateTimeImmutable('2012-02-01'), new DateTimeImmutable('2012-05-01')),
                true,
            ],
            'test abuts returns fase without equal datepoints' => [
                new Period(new DateTimeImmutable('2012-01-01'), new DateTimeImmutable('2012-02-01')),
                new Period(new DateTimeImmutable('2012-01-01'), new DateTimeImmutable('2012-03-01')),
                false,
            ],
            'test abuts returns true with equal datepoints by if boundary is inclusif (1)' => [
                new Period(new DateTimeImmutable('2012-01-01'), new DateTimeImmutable('2012-02-01'), Period::INCLUDE_ALL),
                new Period(new DateTimeImmutable('2012-02-01'), new DateTimeImmutable('2012-05-01'), Period::INCLUDE_ALL),
                false,
            ],
            'test abuts returns true with equal datepoints by if boundary is inclusif (2)' => [
                new Period(new DateTimeImmutable('2012-02-01'), new DateTimeImmutable('2012-05-01'), Period::INCLUDE_ALL),
                new Period(new DateTimeImmutable('2012-01-01'), new DateTimeImmutable('2012-02-01'), Period::INCLUDE_ALL),
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

    public function overlapsDataProvider(): array
    {
        return [
            'overlaps returns false with gapped intervals' => [
                new Period(new DateTimeImmutable('2014-03-01'), new DateTimeImmutable('2014-04-01')),
                new Period(new DateTimeImmutable('2013-04-01'), new DateTimeImmutable('2013-05-01')),
                false,
            ],
            'overlaps returns false with abuts intervals' => [
                new Period(new DateTimeImmutable('2014-03-01'), new DateTimeImmutable('2014-04-01')),
                new Period(new DateTimeImmutable('2014-04-01'), new DateTimeImmutable('2014-05-01')),
                false,
            ],
            'overlaps returns' => [
                new Period(new DateTimeImmutable('2014-03-01'), new DateTimeImmutable('2014-04-01')),
                new Period(new DateTimeImmutable('2014-03-15'), new DateTimeImmutable('2014-04-07')),
                true,
            ],
            'overlaps returns with equals intervals' => [
                new Period(new DateTimeImmutable('2014-03-01'), new DateTimeImmutable('2014-04-01')),
                new Period(new DateTimeImmutable('2014-03-01'), new DateTimeImmutable('2014-04-01')),
                true,
            ],
            'overlaps returns with contained intervals' => [
                new Period(new DateTimeImmutable('2014-03-01'), new DateTimeImmutable('2014-04-01')),
                new Period(new DateTimeImmutable('2014-03-13'), new DateTimeImmutable('2014-03-15')),
                true,
            ],
            'overlaps returns with contained intervals backwards' => [
                new Period(new DateTimeImmutable('2014-03-13'), new DateTimeImmutable('2014-03-15')),
                new Period(new DateTimeImmutable('2014-03-01'), new DateTimeImmutable('2014-04-01')),
                true,
            ],
        ];
    }

    /**
     * @dataProvider containsDataProvider
     *
     * @param DateTimeInterface|Period|string $arg
     */
    public function testContains(Period $interval, $arg, bool $expected): void
    {
        self::assertSame($expected, $interval->contains($arg));
        if ($arg instanceof Period) {
            self::assertSame($expected, $arg->isDuring($interval));
        }
    }

    public function containsDataProvider(): array
    {
        return [
            'contains returns true with a DateTimeInterface object' => [
                new Period(new DateTimeImmutable('2014-03-10'), new DateTimeImmutable('2014-03-15')),
                new DateTime('2014-03-12'),
                true,
            ],
            'contains returns true with a Period object' => [
                new Period(new DateTimeImmutable('2014-01-01'), new DateTimeImmutable('2014-06-01')),
                new Period(new DateTimeImmutable('2014-01-01'), new DateTimeImmutable('2014-04-01')),
                true,
            ],
            'contains returns true with a Period object (2)' => [
                new Period(
                    new DateTimeImmutable('2014-03-01'),
                    new DateTimeImmutable('2014-06-01'),
                    Period::EXCLUDE_START_INCLUDE_END
                ),
                new Period(
                    new DateTimeImmutable('2014-05-01'),
                    new DateTimeImmutable('2014-06-01'),
                    Period::EXCLUDE_START_INCLUDE_END
                ),
                true,
            ],
            'contains returns true with a Period object (3)' => [
                new Period(
                    new DateTimeImmutable('2014-03-01'),
                    new DateTimeImmutable('2014-06-01'),
                    Period::EXCLUDE_ALL
                ),
                new Period(
                    new DateTimeImmutable('2014-05-01'),
                    new DateTimeImmutable('2014-06-01'),
                    Period::EXCLUDE_ALL
                ),
                true,
            ],
            'contains returns true with a Period object (4)' => [
                new Period(
                    new DateTimeImmutable('2014-03-01'),
                    new DateTimeImmutable('2014-06-01'),
                    Period::INCLUDE_ALL
                ),
                new Period(
                    new DateTimeImmutable('2014-05-01'),
                    new DateTimeImmutable('2014-06-01'),
                    Period::INCLUDE_ALL
                ),
                true,
            ],
            'contains returns false with a DateTimeInterface object' => [
                new Period(new DateTimeImmutable('2014-03-13'), new DateTimeImmutable('2014-03-15')),
                new DateTime('2015-03-12'),
                false,
            ],
            'contains returns false with a DateTimeInterface object after the interval' => [
                new Period(new DateTimeImmutable('2014-03-13'), new DateTimeImmutable('2014-03-15')),
                '2012-03-12',
                false,
            ],
            'contains returns false with a DateTimeInterface object before the interval' => [
                new Period(new DateTimeImmutable('2014-03-13'), new DateTimeImmutable('2014-03-15')),
                '2014-04-01',
                false,
            ],
            'contains returns false with abuts interval' => [
                new Period(new DateTimeImmutable('2014-01-01'), new DateTimeImmutable('2014-04-01')),
                new Period(new DateTimeImmutable('2014-01-01'), new DateTimeImmutable('2014-06-01')),
                false,
            ],
            'contains returns true with a Period objects sharing the same end date' => [
                new Period(new DateTimeImmutable('2015-01-01'), new DateTimeImmutable('2016-01-01')),
                new Period(new DateTimeImmutable('2015-12-01'), new DateTimeImmutable('2016-01-01')),
                true,
            ],
            'contains returns false with O duration Period object' => [
                new Period(new DateTimeImmutable('2012-03-12'), new DateTimeImmutable('2012-03-12')),
                new DateTime('2012-03-12'),
                false,
            ],
            'contains datetime edge case datetime equals start date' => [
                Period::after('2012-01-08', '1 DAY'),
                new DateTime('2012-01-08'),
                true,
            ],
            'contains datetime edge case datetime equals end date' => [
                Period::after('2012-01-08', '1 DAY'),
                new DateTime('2012-01-09'),
                false,
            ],
            'contains datetime edge case datetime equals start date OLCR interval' => [
                Period::after('2012-01-08', '1 DAY', Period::EXCLUDE_START_INCLUDE_END),
                new DateTime('2012-01-08'),
                false,
            ],
            'contains datetime edge case datetime equals end date CLCR interval' => [
                Period::after('2012-01-08', '1 DAY', Period::EXCLUDE_ALL),
                new DateTime('2012-01-09'),
                false,
            ],
            'contains period same duration + boundary type CLCR vs CLCR' => [
                Period::after('2012-01-08', '1 DAY', Period::EXCLUDE_ALL),
                Period::after('2012-01-08', '1 DAY', Period::EXCLUDE_ALL),
                true,
            ],
            'contains period same duration + boundary type OLOR vs OLOR' => [
                Period::after('2012-01-08', '1 DAY', Period::INCLUDE_ALL),
                Period::after('2012-01-08', '1 DAY', Period::INCLUDE_ALL),
                true,
            ],
            'contains period same duration + boundary type CLOR vs CLOR' => [
                Period::after('2012-01-08', '1 DAY', Period::EXCLUDE_START_INCLUDE_END),
                Period::after('2012-01-08', '1 DAY', Period::EXCLUDE_START_INCLUDE_END),
                true,
            ],
            'contains period same duration + boundary type CLOR vs OLCR' => [
                Period::after('2012-01-08', '1 DAY', Period::EXCLUDE_START_INCLUDE_END),
                Period::after('2012-01-08', '1 DAY', Period::INCLUDE_START_EXCLUDE_END),
                false,
            ],
            'contains period same duration + boundary type OLCR vs CLOR' => [
                Period::after('2012-01-08', '1 DAY', Period::INCLUDE_START_EXCLUDE_END),
                Period::after('2012-01-08', '1 DAY', Period::EXCLUDE_START_INCLUDE_END),
                false,
            ],
            'contains period same duration + boundary type CLCR vs OLOR' => [
                Period::after('2012-01-08', '1 DAY', Period::EXCLUDE_ALL),
                Period::after('2012-01-08', '1 DAY', Period::INCLUDE_ALL),
                false,
            ],
            'contains period same duration + boundary type OLOR vs CLCR' => [
                Period::after('2012-01-08', '1 DAY', Period::INCLUDE_ALL),
                Period::after('2012-01-08', '1 DAY', Period::EXCLUDE_ALL),
                true,
            ],
        ];
    }

    /**
     * @dataProvider startsDataProvider
     * @param DateTimeInterface|Period $index
     */
    public function testStarts(Period $interval1, $index, bool $expected): void
    {
        self::assertSame($expected, $interval1->isStartedBy($index));
    }

    public function startsDataProvider(): array
    {
        return [
            [
                new Period(new DateTime('2012-01-01'), new DateTime('2012-01-15')),
                new Period(new DateTime('2012-01-01'), new DateTime('2013-01-16')),
                true,
            ],
            [
                new Period(new DateTime('2012-01-02'), new DateTime('2012-01-15')),
                new Period(new DateTime('2012-01-01'), new DateTime('2013-01-16')),
                false,
            ],
            [
                new Period(new DateTime('2012-01-01'), new DateTime('2012-01-15'), Period::INCLUDE_ALL),
                new Period(new DateTime('2012-01-01'), new DateTime('2013-01-16')),
                true,
            ],
            [
                new Period(new DateTime('2012-01-01'), new DateTime('2012-01-15'), Period::EXCLUDE_ALL),
                new Period(new DateTime('2012-01-01'), new DateTime('2013-01-16'), Period::INCLUDE_ALL),
                false,
            ],
            [
                new Period(new DateTime('2012-01-01'), new DateTime('2012-01-15'), Period::EXCLUDE_ALL),
                new DateTime('2012-01-01'),
                false,
            ],
            [
                new Period(new DateTime('2012-01-01'), new DateTime('2012-01-15'), Period::INCLUDE_START_EXCLUDE_END),
                new DateTime('2012-01-01'),
                true,
            ],
        ];
    }

    /**
     * @dataProvider finishesDataProvider
     * @param DateTimeInterface|Period $index
     */
    public function testFinishes(Period $interval1, $index, bool $expected): void
    {
        self::assertSame($expected, $interval1->isEndedBy($index));
    }

    public function finishesDataProvider(): array
    {
        return [
            [
                new Period(new DateTime('2012-01-01'), new DateTime('2012-01-16')),
                new Period(new DateTime('2012-01-01'), new DateTime('2012-01-16')),
                true,
            ],
            [
                new Period(new DateTime('2012-01-02'), new DateTime('2012-01-15')),
                new Period(new DateTime('2012-01-01'), new DateTime('2013-01-16')),
                false,
            ],
            [
                new Period(new DateTime('2012-01-01'), new DateTime('2012-01-16')),
                new Period(new DateTime('2012-01-01'), new DateTime('2012-01-16'), Period::EXCLUDE_ALL),
                true,
            ],
            [
                new Period(new DateTime('2012-01-01'), new DateTime('2012-01-16'), Period::EXCLUDE_ALL),
                new Period(new DateTime('2012-01-01'), new DateTime('2012-01-16'), Period::INCLUDE_ALL),
                false,
            ],
            [
                new Period(new DateTime('2012-01-01'), new DateTime('2012-01-16'), Period::EXCLUDE_ALL),
                new DateTime('2012-01-16'),
                false,
            ],
            [
                new Period(new DateTime('2012-01-01'), new DateTime('2012-01-16'), Period::INCLUDE_ALL),
                new DateTime('2012-01-16'),
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

    public function equalsDataProvider(): array
    {
        return [
            'returns true' => [
                new Period(new DateTime('2012-01-01 00:00:00'), new DateTime('2012-01-03 00:00:00')),
                new Period(new DateTime('2012-01-01 00:00:00'), new DateTime('2012-01-03 00:00:00')),
                true,
            ],
            'returns false' => [
                new Period(new DateTime('2012-01-01'), new DateTime('2012-01-15')),
                new Period(new DateTime('2012-01-01'), new DateTime('2012-01-07')),
                false,
            ],
            'returns false is argument order independent' => [
                new Period(new DateTime('2012-01-01'), new DateTime('2012-01-07')),
                new Period(new DateTime('2012-01-01'), new DateTime('2012-01-15')),
                false,
            ],
            'returns false with different range type' => [
                new Period(new DateTime('2012-01-01'), new DateTime('2012-01-15'), Period::INCLUDE_ALL),
                new Period(new DateTime('2012-01-01'), new DateTime('2012-01-15'), Period::EXCLUDE_ALL),
                false,
            ],
        ];
    }

    public function testIntersect(): void
    {
        $orig = new Period(new DateTime('2011-12-01'), new DateTime('2012-04-01'));
        $alt = new Period(new DateTime('2012-01-01'), new DateTime('2012-03-01'));
        self::assertTrue($orig->intersect($alt)->equals(new Period('2012-01-01', '2012-03-01')));
    }

    public function testIntersectThrowsExceptionWithNoOverlappingTimeRange(): void
    {
        self::expectException(Exception::class);
        $orig = new Period(new DateTime('2013-01-01'), new DateTime('2013-02-01'));
        $alt = new Period(new DateTime('2012-01-01'), new DateTime('2012-03-01'));
        $orig->intersect($alt);
    }

    /**
     * @dataProvider intersectBoundaryResultProvider
     */
    public function testIntersectBoundaryTypeResult(string $boundary1, string $boundary2, string $expected): void
    {
        $interval0 = new Period('2014-03-01', '2014-06-01', $boundary1);
        $interval1 = new Period('2014-05-01', '2014-08-01', $boundary2);
        self::assertSame($expected, $interval0->intersect($interval1)->getBoundaryType());
    }

    public function intersectBoundaryResultProvider(): array
    {
        return [
            '() + ()' => [
                'boundary1' => Period::EXCLUDE_ALL,
                'boundary2' => Period::EXCLUDE_ALL,
                'expected' => Period::EXCLUDE_ALL,
            ],
            '() + []' => [
                'boundary1' => Period::EXCLUDE_ALL,
                'boundary2' => Period::INCLUDE_ALL,
                'expected' => Period::INCLUDE_START_EXCLUDE_END,
            ],
            '() + [)' => [
                'boundary1' => Period::EXCLUDE_ALL,
                'boundary2' => Period::INCLUDE_START_EXCLUDE_END,
                'expected' => Period::INCLUDE_START_EXCLUDE_END,
            ],
            '() + (]' => [
                'boundary1' => Period::EXCLUDE_ALL,
                'boundary2' => Period::EXCLUDE_START_INCLUDE_END,
                'expected' => Period::EXCLUDE_ALL,
            ],
            '[] + []' => [
                'boundary1' => Period::INCLUDE_ALL,
                'boundary2' => Period::INCLUDE_ALL,
                'expected' => Period::INCLUDE_ALL,
            ],
            '[] + [)' => [
                'boundary1' => Period::INCLUDE_ALL,
                'boundary2' => Period::INCLUDE_START_EXCLUDE_END,
                'expected' => Period::INCLUDE_ALL,
            ],
            '[] + (]' => [
                'boundary1' => Period::INCLUDE_ALL,
                'boundary2' => Period::EXCLUDE_START_INCLUDE_END,
                'expected' => Period::EXCLUDE_START_INCLUDE_END,
            ],
            '[] + ()' => [
                'boundary1' => Period::INCLUDE_ALL,
                'boundary2' => Period::EXCLUDE_ALL,
                'expected' => Period::EXCLUDE_START_INCLUDE_END,
            ],
            '[) + ()' => [
                'boundary1' => Period::INCLUDE_START_EXCLUDE_END,
                'boundary2' => Period::EXCLUDE_ALL,
                'expected' => Period::EXCLUDE_ALL,
            ],
            '[) + []' => [
                'boundary1' => Period::INCLUDE_START_EXCLUDE_END,
                'boundary2' => Period::INCLUDE_ALL,
                'expected' => Period::INCLUDE_START_EXCLUDE_END,
            ],
            '[) + (]' => [
                'boundary1' => Period::INCLUDE_START_EXCLUDE_END,
                'boundary2' => Period::EXCLUDE_START_INCLUDE_END,
                'expected' => Period::EXCLUDE_ALL,
            ],
            '[) + [)' => [
                'boundary1' => Period::INCLUDE_START_EXCLUDE_END,
                'boundary2' => Period::INCLUDE_START_EXCLUDE_END,
                'expected' => Period::INCLUDE_START_EXCLUDE_END,
            ],
            '(] + ()' => [
                'boundary1' => Period::EXCLUDE_START_INCLUDE_END,
                'boundary2' => Period::EXCLUDE_ALL,
                'expected' => Period::EXCLUDE_START_INCLUDE_END,
            ],
            '(] + []' => [
                'boundary1' => Period::EXCLUDE_START_INCLUDE_END,
                'boundary2' => Period::INCLUDE_ALL,
                'expected' => Period::INCLUDE_ALL,
            ],
            '(] + (]' => [
                'boundary1' => Period::EXCLUDE_START_INCLUDE_END,
                'boundary2' => Period::EXCLUDE_START_INCLUDE_END,
                'expected' => Period::EXCLUDE_START_INCLUDE_END,
            ],
            '(] + [)' => [
                'boundary1' => Period::EXCLUDE_START_INCLUDE_END,
                'boundary2' => Period::INCLUDE_START_EXCLUDE_END,
                'expected' => Period::INCLUDE_ALL,
            ],
        ];
    }



    public function testGap(): void
    {
        $orig = new Period(new DateTime('2011-12-01'), new DateTime('2012-02-01'));
        $alt = new Period(new DateTime('2012-06-01'), new DateTime('2012-09-01'));
        $gap = $orig->gap($alt);

        self::assertEquals($orig->getEndDate(), $gap->getStartDate());
        self::assertEquals($alt->getStartDate(), $gap->getEndDate());
        self::assertTrue($gap->equals($alt->gap($orig)));
    }

    public function testGapThrowsExceptionWithOverlapsInterval(): void
    {
        self::expectException(Exception::class);
        $orig = new Period(new DateTime('2011-12-01'), new DateTime('2012-02-01'));
        $alt = new Period(new DateTime('2011-12-10'), new DateTime('2011-12-15'));
        $orig->gap($alt);
    }

    public function testGapWithSameStartingInterval(): void
    {
        self::expectException(Exception::class);
        $orig = new Period(new DateTime('2011-12-01'), new DateTime('2012-02-01'));
        $alt = new Period(new DateTime('2011-12-01'), new DateTime('2011-12-15'));
        $orig->gap($alt);
    }

    public function testGapWithSameEndingInterval(): void
    {
        self::expectException(Exception::class);
        $orig = new Period(new DateTime('2011-12-01'), new DateTime('2012-02-01'));
        $alt = new Period(new DateTime('2012-01-15'), new DateTime('2012-02-01'));
        $orig->gap($alt);
    }

    public function testGapWithAdjacentInterval(): void
    {
        $orig = new Period(new DateTime('2011-12-01'), new DateTime('2012-02-01'));
        $alt = new Period(new DateTime('2012-02-01'), new DateTime('2012-02-02'));
        self::assertEquals(0, $orig->gap($alt)->getTimestampInterval());
    }

    /**
     * @dataProvider gapBoundaryResultProvider
     */
    public function testGapBoundaryTypeResult(string $boundary1, string $boundary2, string $expected): void
    {
        $interval0 = new Period('2014-03-01', '2014-06-01', $boundary1);
        $interval1 = new Period('2014-07-01', '2014-09-01', $boundary2);
        self::assertSame($expected, $interval0->gap($interval1)->getBoundaryType());
    }

    public function gapBoundaryResultProvider(): array
    {
        return [
            '() + ()' => [
                'boundary1' => Period::EXCLUDE_ALL,
                'boundary2' => Period::EXCLUDE_ALL,
                'expected' => Period::INCLUDE_ALL,
            ],
            '() + []' => [
                'boundary1' => Period::EXCLUDE_ALL,
                'boundary2' => Period::INCLUDE_ALL,
                'expected' => Period::INCLUDE_START_EXCLUDE_END,
            ],
            '() + [)' => [
                'boundary1' => Period::EXCLUDE_ALL,
                'boundary2' => Period::INCLUDE_START_EXCLUDE_END,
                'expected' => Period::INCLUDE_START_EXCLUDE_END,
            ],
            '() + (]' => [
                'boundary1' => Period::EXCLUDE_ALL,
                'boundary2' => Period::EXCLUDE_START_INCLUDE_END,
                'expected' => Period::INCLUDE_ALL,
            ],
            '[] + []' => [
                'boundary1' => Period::INCLUDE_ALL,
                'boundary2' => Period::INCLUDE_ALL,
                'expected' => Period::EXCLUDE_ALL,
            ],
            '[] + [)' => [
                'boundary1' => Period::INCLUDE_ALL,
                'boundary2' => Period::INCLUDE_START_EXCLUDE_END,
                'expected' => Period::EXCLUDE_ALL,
            ],
            '[] + (]' => [
                'boundary1' => Period::INCLUDE_ALL,
                'boundary2' => Period::EXCLUDE_START_INCLUDE_END,
                'expected' => Period::EXCLUDE_START_INCLUDE_END,
            ],
            '[] + ()' => [
                'boundary1' => Period::INCLUDE_ALL,
                'boundary2' => Period::EXCLUDE_ALL,
                'expected' => Period::EXCLUDE_START_INCLUDE_END,
            ],
            '[) + ()' => [
                'boundary1' => Period::INCLUDE_START_EXCLUDE_END,
                'boundary2' => Period::EXCLUDE_ALL,
                'expected' => Period::INCLUDE_ALL,
            ],
            '[) + []' => [
                'boundary1' => Period::INCLUDE_START_EXCLUDE_END,
                'boundary2' => Period::INCLUDE_ALL,
                'expected' => Period::INCLUDE_START_EXCLUDE_END,
            ],
            '[) + (]' => [
                'boundary1' => Period::INCLUDE_START_EXCLUDE_END,
                'boundary2' => Period::EXCLUDE_START_INCLUDE_END,
                'expected' => Period::INCLUDE_ALL,
            ],
            '[) + [)' => [
                'boundary1' => Period::INCLUDE_START_EXCLUDE_END,
                'boundary2' => Period::INCLUDE_START_EXCLUDE_END,
                'expected' => Period::INCLUDE_START_EXCLUDE_END,
            ],
            '(] + ()' => [
                'boundary1' => Period::EXCLUDE_START_INCLUDE_END,
                'boundary2' => Period::EXCLUDE_ALL,
                'expected' => Period::EXCLUDE_START_INCLUDE_END,
            ],
            '(] + []' => [
                'boundary1' => Period::EXCLUDE_START_INCLUDE_END,
                'boundary2' => Period::INCLUDE_ALL,
                'expected' => Period::EXCLUDE_ALL,
            ],
            '(] + (]' => [
                'boundary1' => Period::EXCLUDE_START_INCLUDE_END,
                'boundary2' => Period::EXCLUDE_START_INCLUDE_END,
                'expected' => Period::EXCLUDE_START_INCLUDE_END,
            ],
            '(] + [)' => [
                'boundary1' => Period::EXCLUDE_START_INCLUDE_END,
                'boundary2' => Period::INCLUDE_START_EXCLUDE_END,
                'expected' => Period::EXCLUDE_ALL,
            ],
        ];
    }

    public function testDiffThrowsException(): void
    {
        $interval1 = new Period(new DateTimeImmutable('2015-01-01'), new DateTimeImmutable('2016-01-01'));
        $interval2 = new Period(new DateTimeImmutable('2013-01-01'), new DateTimeImmutable('2014-01-01'));

        self::expectException(Exception::class);
        $interval1->diff($interval2);
    }

    public function testDiffWithEqualsPeriod(): void
    {
        $period = new Period(new DateTimeImmutable('2013-01-01'), new DateTimeImmutable('2014-01-01'));
        $alt = new Period(new DateTimeImmutable('2013-01-01'), new DateTimeImmutable('2014-01-01'));
        [$diff1, $diff2] = $alt->diff($period);
        self::assertNull($diff1);
        self::assertNull($diff2);
        self::assertEquals($alt->diff($period), $period->diff($alt));
    }

    public function testDiffWithPeriodSharingStartingDatepoints(): void
    {
        $period = new Period(new DateTimeImmutable('2013-01-01'), new DateTimeImmutable('2014-01-01'));
        $alt = new Period(new DateTimeImmutable('2013-01-01'), new DateTimeImmutable('2013-04-01'));
        [$diff1, $diff2] = $alt->diff($period);
        self::assertInstanceOf(Period::class, $diff1);
        self::assertNull($diff2);
        self::assertEquals(new DateTimeImmutable('2013-04-01'), $diff1->getStartDate());
        self::assertEquals(new DateTimeImmutable('2014-01-01'), $diff1->getEndDate());
        self::assertEquals($alt->diff($period), $period->diff($alt));
    }

    public function testDiffWithPeriodSharingEndingDatepoints(): void
    {
        $period = new Period(new DateTimeImmutable('2013-01-01'), new DateTimeImmutable('2014-01-01'));
        $alt = new Period(new DateTimeImmutable('2013-10-01'), new DateTimeImmutable('2014-01-01'));
        [$diff1, $diff2] = $alt->diff($period);
        self::assertInstanceOf(Period::class, $diff1);
        self::assertNull($diff2);
        self::assertEquals(new DateTimeImmutable('2013-01-01'), $diff1->getStartDate());
        self::assertEquals(new DateTimeImmutable('2013-10-01'), $diff1->getEndDate());
        self::assertEquals($alt->diff($period), $period->diff($alt));
    }

    public function testDiffWithOverlapsPeriod(): void
    {
        $period = new Period(new DateTimeImmutable('2013-01-01 10:00:00'), new DateTimeImmutable('2013-01-01 13:00:00'));
        $alt = new Period(new DateTimeImmutable('2013-01-01 11:00:00'), new DateTimeImmutable('2013-01-01 14:00:00'));
        [$diff1, $diff2] = $alt->diff($period);
        self::assertInstanceOf(Period::class, $diff1);
        self::assertInstanceOf(Period::class, $diff2);
        self::assertSame(3600.0, $diff1->getTimestampInterval());
        self::assertSame(3600.0, $diff2->getTimestampInterval());
        self::assertEquals($alt->diff($period), $period->diff($alt));
    }

    /**
     * @dataProvider diffBoundaryResultProvider
     */
    public function testDiffBoundaryTypeResult(
        string $boundary1,
        string $boundary2,
        string $expected1,
        string $expected2
    ): void {
        $interval0 = new Period('2014-03-01', '2014-06-01', $boundary1);
        $interval1 = new Period('2014-05-01', '2014-09-01', $boundary2);
        [$diff1, $diff2] = $interval0->diff($interval1);
        if (null !== $diff1) {
            self::assertSame($expected1, $diff1->getBoundaryType());
        }

        if (null !== $diff2) {
            self::assertSame($expected2, $diff2->getBoundaryType());
        }
    }

    public function diffBoundaryResultProvider(): array
    {
        return [
            '() + ()' => [
                'boundary1' => Period::EXCLUDE_ALL,
                'boundary2' => Period::EXCLUDE_ALL,
                'expected1' => Period::EXCLUDE_START_INCLUDE_END,
                'expected2' => Period::INCLUDE_START_EXCLUDE_END,
            ],
            '() + []' => [
                'boundary1' => Period::EXCLUDE_ALL,
                'boundary2' => Period::INCLUDE_ALL,
                'expected1' => Period::EXCLUDE_ALL,
                'expected2' => Period::INCLUDE_ALL,
            ],
            '() + [)' => [
                'boundary1' => Period::EXCLUDE_ALL,
                'boundary2' => Period::INCLUDE_START_EXCLUDE_END,
                'expected1' => Period::EXCLUDE_ALL,
                'expected2' => Period::INCLUDE_START_EXCLUDE_END,
            ],
            '() + (]' => [
                'boundary1' => Period::EXCLUDE_ALL,
                'boundary2' => Period::EXCLUDE_START_INCLUDE_END,
                'expected1' => Period::EXCLUDE_START_INCLUDE_END,
                'expected2' => Period::INCLUDE_ALL,
            ],
            '[] + []' => [
                'boundary1' => Period::INCLUDE_ALL,
                'boundary2' => Period::INCLUDE_ALL,
                'expected1' => Period::INCLUDE_START_EXCLUDE_END,
                'expected2' => Period::EXCLUDE_START_INCLUDE_END,
            ],
            '[] + [)' => [
                'boundary1' => Period::INCLUDE_ALL,
                'boundary2' => Period::INCLUDE_START_EXCLUDE_END,
                'expected1' => Period::INCLUDE_START_EXCLUDE_END,
                'expected2' => Period::EXCLUDE_ALL,
            ],
            '[] + (]' => [
                'boundary1' => Period::INCLUDE_ALL,
                'boundary2' => Period::EXCLUDE_START_INCLUDE_END,
                'expected1' => Period::INCLUDE_ALL,
                'expected2' => Period::EXCLUDE_START_INCLUDE_END,
            ],
            '[] + ()' => [
                'boundary1' => Period::INCLUDE_ALL,
                'boundary2' => Period::EXCLUDE_ALL,
                'expected1' => Period::INCLUDE_ALL,
                'expected2' => Period::EXCLUDE_ALL,
            ],
            '[) + ()' => [
                'boundary1' => Period::INCLUDE_START_EXCLUDE_END,
                'boundary2' => Period::EXCLUDE_ALL,
                'expected1' => Period::INCLUDE_ALL,
                'expected2' => Period::INCLUDE_START_EXCLUDE_END,
            ],
            '[) + []' => [
                'boundary1' => Period::INCLUDE_START_EXCLUDE_END,
                'boundary2' => Period::INCLUDE_ALL,
                'expected1' => Period::INCLUDE_START_EXCLUDE_END,
                'expected2' => Period::INCLUDE_ALL,
            ],
            '[) + (]' => [
                'boundary1' => Period::INCLUDE_START_EXCLUDE_END,
                'boundary2' => Period::EXCLUDE_START_INCLUDE_END,
                'expected1' => Period::INCLUDE_ALL,
                'expected2' => Period::INCLUDE_ALL,
            ],
            '[) + [)' => [
                'boundary1' => Period::INCLUDE_START_EXCLUDE_END,
                'boundary2' => Period::INCLUDE_START_EXCLUDE_END,
                'expected1' => Period::INCLUDE_START_EXCLUDE_END,
                'expected2' => Period::INCLUDE_START_EXCLUDE_END,
            ],
            '(] + ()' => [
                'boundary1' => Period::EXCLUDE_START_INCLUDE_END,
                'boundary2' => Period::EXCLUDE_ALL,
                'expected1' => Period::EXCLUDE_START_INCLUDE_END,
                'expected2' => Period::EXCLUDE_ALL,
            ],
            '(] + []' => [
                'boundary1' => Period::EXCLUDE_START_INCLUDE_END,
                'boundary2' => Period::INCLUDE_ALL,
                'expected1' => Period::EXCLUDE_ALL,
                'expected2' => Period::EXCLUDE_START_INCLUDE_END,
            ],
            '(] + (]' => [
                'boundary1' => Period::EXCLUDE_START_INCLUDE_END,
                'boundary2' => Period::EXCLUDE_START_INCLUDE_END,
                'expected1' => Period::EXCLUDE_START_INCLUDE_END,
                'expected2' => Period::EXCLUDE_START_INCLUDE_END,
            ],
            '(] + [)' => [
                'boundary1' => Period::EXCLUDE_START_INCLUDE_END,
                'boundary2' => Period::INCLUDE_START_EXCLUDE_END,
                'expected1' => Period::EXCLUDE_ALL,
                'expected2' => Period::EXCLUDE_ALL,
            ],
        ];
    }

    public function testDiffAndIntersect(): void
    {
        foreach (['[]', '[)', '()', '(]'] as $bound1) {
            foreach (['[]', '[)', '()', '(]'] as $bound2) {
                $interval0 = new Period('2014-03-01', '2014-06-01', $bound1);
                $interval1 = new Period('2014-05-01', '2014-08-01', $bound2);
                [$diff1, $diff2] = $interval0->diff($interval1);
                $intersect = $interval0->intersect($interval1);

                if (null !== $diff1) {
                    self::assertTrue($diff1->bordersOnStart($intersect));
                }

                if (null !== $diff2) {
                    self::assertTrue($diff2->bordersOnEnd($intersect));
                }

                $seq = new Sequence(...array_filter([$diff1, $diff2, $intersect]));
                $boundaries = $seq->getBoundaries();
                if (null !== $boundaries) {
                    self::assertTrue($boundaries->equals($interval0->merge($interval1)));
                }
            }
        }
    }
}
