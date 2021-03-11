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

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \League\Period\Period
 */
class IntervalRelationTest extends TestCase
{
    /** @var string **/
    private $timezone;

    public function setUp(): void
    {
        $this->timezone = date_default_timezone_get();
    }

    public function tearDown(): void
    {
        date_default_timezone_set($this->timezone);
    }

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
                Period::fromDatepoint(new DateTimeImmutable('2012-01-01'), new DateTimeImmutable('2012-02-01')),
                Period::fromDatepoint(new DateTimeImmutable('2012-02-01'), new DateTimeImmutable('2012-05-01')),
                true,
            ],
            'test abuts returns fase without equal datepoints' => [
                Period::fromDatepoint(new DateTimeImmutable('2012-01-01'), new DateTimeImmutable('2012-02-01')),
                Period::fromDatepoint(new DateTimeImmutable('2012-01-01'), new DateTimeImmutable('2012-03-01')),
                false,
            ],
            'test abuts returns true with equal datepoints by if boundary is inclusif (1)' => [
                Period::fromDatepoint(new DateTimeImmutable('2012-01-01'), new DateTimeImmutable('2012-02-01'), Period::INCLUDE_ALL),
                Period::fromDatepoint(new DateTimeImmutable('2012-02-01'), new DateTimeImmutable('2012-05-01'), Period::INCLUDE_ALL),
                false,
            ],
            'test abuts returns true with equal datepoints by if boundary is inclusif (2)' => [
                Period::fromDatepoint(new DateTimeImmutable('2012-02-01'), new DateTimeImmutable('2012-05-01'), Period::INCLUDE_ALL),
                Period::fromDatepoint(new DateTimeImmutable('2012-01-01'), new DateTimeImmutable('2012-02-01'), Period::INCLUDE_ALL),
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
                Period::fromDatepoint(new DateTimeImmutable('2014-03-01'), new DateTimeImmutable('2014-04-01')),
                Period::fromDatepoint(new DateTimeImmutable('2013-04-01'), new DateTimeImmutable('2013-05-01')),
                false,
            ],
            'overlaps returns false with abuts intervals' => [
                Period::fromDatepoint(new DateTimeImmutable('2014-03-01'), new DateTimeImmutable('2014-04-01')),
                Period::fromDatepoint(new DateTimeImmutable('2014-04-01'), new DateTimeImmutable('2014-05-01')),
                false,
            ],
            'overlaps returns' => [
                Period::fromDatepoint(new DateTimeImmutable('2014-03-01'), new DateTimeImmutable('2014-04-01')),
                Period::fromDatepoint(new DateTimeImmutable('2014-03-15'), new DateTimeImmutable('2014-04-07')),
                true,
            ],
            'overlaps returns with equals intervals' => [
                Period::fromDatepoint(new DateTimeImmutable('2014-03-01'), new DateTimeImmutable('2014-04-01')),
                Period::fromDatepoint(new DateTimeImmutable('2014-03-01'), new DateTimeImmutable('2014-04-01')),
                true,
            ],
            'overlaps returns with contained intervals' => [
                Period::fromDatepoint(new DateTimeImmutable('2014-03-01'), new DateTimeImmutable('2014-04-01')),
                Period::fromDatepoint(new DateTimeImmutable('2014-03-13'), new DateTimeImmutable('2014-03-15')),
                true,
            ],
            'overlaps returns with contained intervals backwards' => [
                Period::fromDatepoint(new DateTimeImmutable('2014-03-13'), new DateTimeImmutable('2014-03-15')),
                Period::fromDatepoint(new DateTimeImmutable('2014-03-01'), new DateTimeImmutable('2014-04-01')),
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
                Period::fromDatepoint(new DateTimeImmutable('2014-03-10'), new DateTimeImmutable('2014-03-15')),
                new DateTime('2014-03-12'),
                true,
            ],
            'contains returns true with a Period object' => [
                Period::fromDatepoint(new DateTimeImmutable('2014-01-01'), new DateTimeImmutable('2014-06-01')),
                Period::fromDatepoint(new DateTimeImmutable('2014-01-01'), new DateTimeImmutable('2014-04-01')),
                true,
            ],
            'contains returns true with a Period object (2)' => [
                Period::fromDatepoint(
                    new DateTimeImmutable('2014-03-01'),
                    new DateTimeImmutable('2014-06-01'),
                    Period::EXCLUDE_START_INCLUDE_END
                ),
                Period::fromDatepoint(
                    new DateTimeImmutable('2014-05-01'),
                    new DateTimeImmutable('2014-06-01'),
                    Period::EXCLUDE_START_INCLUDE_END
                ),
                true,
            ],
            'contains returns true with a Period object (3)' => [
                Period::fromDatepoint(
                    new DateTimeImmutable('2014-03-01'),
                    new DateTimeImmutable('2014-06-01'),
                    Period::EXCLUDE_ALL
                ),
                Period::fromDatepoint(
                    new DateTimeImmutable('2014-05-01'),
                    new DateTimeImmutable('2014-06-01'),
                    Period::EXCLUDE_ALL
                ),
                true,
            ],
            'contains returns true with a Period object (4)' => [
                Period::fromDatepoint(
                    new DateTimeImmutable('2014-03-01'),
                    new DateTimeImmutable('2014-06-01'),
                    Period::INCLUDE_ALL
                ),
                Period::fromDatepoint(
                    new DateTimeImmutable('2014-05-01'),
                    new DateTimeImmutable('2014-06-01'),
                    Period::INCLUDE_ALL
                ),
                true,
            ],
            'contains returns false with a DateTimeInterface object' => [
                Period::fromDatepoint(new DateTimeImmutable('2014-03-13'), new DateTimeImmutable('2014-03-15')),
                new DateTime('2015-03-12'),
                false,
            ],
            'contains returns false with a DateTimeInterface object after the interval' => [
                Period::fromDatepoint(new DateTimeImmutable('2014-03-13'), new DateTimeImmutable('2014-03-15')),
                '2012-03-12',
                false,
            ],
            'contains returns false with a DateTimeInterface object before the interval' => [
                Period::fromDatepoint(new DateTimeImmutable('2014-03-13'), new DateTimeImmutable('2014-03-15')),
                '2014-04-01',
                false,
            ],
            'contains returns false with abuts interval' => [
                Period::fromDatepoint(new DateTimeImmutable('2014-01-01'), new DateTimeImmutable('2014-04-01')),
                Period::fromDatepoint(new DateTimeImmutable('2014-01-01'), new DateTimeImmutable('2014-06-01')),
                false,
            ],
            'contains returns true with a Period objects sharing the same end date' => [
                Period::fromDatepoint(new DateTimeImmutable('2015-01-01'), new DateTimeImmutable('2016-01-01')),
                Period::fromDatepoint(new DateTimeImmutable('2015-12-01'), new DateTimeImmutable('2016-01-01')),
                true,
            ],
            'contains returns false with O duration Period object' => [
                Period::fromDatepoint(new DateTimeImmutable('2012-03-12'), new DateTimeImmutable('2012-03-12')),
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
    public function testStarts(Period $interval, $index, bool $expected): void
    {
        self::assertSame($expected, $interval->isStartedBy($index));
        if ($index instanceof DateTimeInterface) {
            self::assertSame($expected, Datepoint::fromDateTimeInterface($index)->isStarting($interval));
        }
    }

    public function startsDataProvider(): array
    {
        $startingDate = new DateTime('2012-01-01');
        $interval = Period::fromDatepoint($startingDate, new DateTime('2012-01-15'));

        return [
            [
                $interval,
                $interval,
                true,
            ],
            [
                $interval,
                $interval->moveEndDate('+3 MINUTES'),
                true,
            ],
            [
                $interval,
                $interval->moveStartDate('+3 MINUTES'),
                false,
            ],
            [
                $interval->withBoundaryType(Period::INCLUDE_ALL),
                $interval,
                true,
            ],
            [
                $interval->withBoundaryType(Period::EXCLUDE_ALL),
                $interval->withBoundaryType(Period::INCLUDE_ALL),
                false,
            ],
            [
                $interval->withBoundaryType(Period::EXCLUDE_ALL),
                $startingDate,
                false,
            ],
            [
                $interval->withBoundaryType(Period::INCLUDE_START_EXCLUDE_END),
                $startingDate,
                true,
            ],
        ];
    }

    /**
     * @dataProvider finishesDataProvider
     * @param DateTimeInterface|Period $index
     */
    public function testFinishes(Period $interval, $index, bool $expected): void
    {
        self::assertSame($expected, $interval->isEndedBy($index));
    }

    public function finishesDataProvider(): array
    {
        $endingDate = new DateTime('2012-01-16');
        $interval = Period::fromDatepoint(new DateTime('2012-01-01'), $endingDate);
        return [
            [
                $interval,
                $interval,
                true,
            ],
            [
                $interval->moveEndDate('+ 3 MINUTES'),
                $interval,
                false,
            ],
            [
                $interval,
                $interval->withBoundaryType(Period::EXCLUDE_ALL),
                true,
            ],
            [
                $interval->withBoundaryType(Period::EXCLUDE_ALL),
                $interval->withBoundaryType(Period::INCLUDE_ALL),
                false,
            ],
            [
                $interval->withBoundaryType(Period::EXCLUDE_ALL),
                $endingDate,
                false,
            ],
            [
                $interval->withBoundaryType(Period::INCLUDE_ALL),
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

    public function equalsDataProvider(): array
    {
        return [
            'returns true' => [
                Period::fromDatepoint(new DateTime('2012-01-01 00:00:00'), new DateTime('2012-01-03 00:00:00')),
                Period::fromDatepoint(new DateTime('2012-01-01 00:00:00'), new DateTime('2012-01-03 00:00:00')),
                true,
            ],
            'returns false' => [
                Period::fromDatepoint(new DateTime('2012-01-01'), new DateTime('2012-01-15')),
                Period::fromDatepoint(new DateTime('2012-01-01'), new DateTime('2012-01-07')),
                false,
            ],
            'returns false is argument order independent' => [
                Period::fromDatepoint(new DateTime('2012-01-01'), new DateTime('2012-01-07')),
                Period::fromDatepoint(new DateTime('2012-01-01'), new DateTime('2012-01-15')),
                false,
            ],
            'returns false with different range type' => [
                Period::fromDatepoint(new DateTime('2012-01-01'), new DateTime('2012-01-15'), Period::INCLUDE_ALL),
                Period::fromDatepoint(new DateTime('2012-01-01'), new DateTime('2012-01-15'), Period::EXCLUDE_ALL),
                false,
            ],
        ];
    }

    public function testIntersect(): void
    {
        $orig = Period::fromDatepoint(new DateTime('2011-12-01'), new DateTime('2012-04-01'));
        $alt = Period::fromDatepoint(new DateTime('2012-01-01'), new DateTime('2012-03-01'));
        self::assertTrue($orig->intersect($alt)->equals(Period::fromDatepoint('2012-01-01', '2012-03-01')));
    }

    public function testIntersectThrowsExceptionWithNoOverlappingTimeRange(): void
    {
        $this->expectException(InvalidTimeRange::class);
        $orig = Period::fromDatepoint(new DateTime('2013-01-01'), new DateTime('2013-02-01'));
        $alt = Period::fromDatepoint(new DateTime('2012-01-01'), new DateTime('2012-03-01'));
        $orig->intersect($alt);
    }

    /**
     * @dataProvider intersectBoundaryResultProvider
     */
    public function testIntersectBoundaryTypeResult(string $boundary1, string $boundary2, string $expected): void
    {
        $interval0 = Period::fromDatepoint('2014-03-01', '2014-06-01', $boundary1);
        $interval1 = Period::fromDatepoint('2014-05-01', '2014-08-01', $boundary2);
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
        $orig = Period::fromDatepoint(new DateTime('2011-12-01'), new DateTime('2012-02-01'));
        $alt = Period::fromDatepoint(new DateTime('2012-06-01'), new DateTime('2012-09-01'));
        $gap = $orig->gap($alt);

        self::assertEquals($orig->getEndDate(), $gap->getStartDate());
        self::assertEquals($alt->getStartDate(), $gap->getEndDate());
        self::assertTrue($gap->equals($alt->gap($orig)));
    }

    public function testGapThrowsExceptionWithOverlapsInterval(): void
    {
        $this->expectException(InvalidTimeRange::class);
        $orig = Period::fromDatepoint(new DateTime('2011-12-01'), new DateTime('2012-02-01'));
        $alt = Period::fromDatepoint(new DateTime('2011-12-10'), new DateTime('2011-12-15'));
        $orig->gap($alt);
    }

    public function testGapWithSameStartingInterval(): void
    {
        $this->expectException(InvalidTimeRange::class);
        $orig = Period::fromDatepoint(new DateTime('2011-12-01'), new DateTime('2012-02-01'));
        $alt = Period::fromDatepoint(new DateTime('2011-12-01'), new DateTime('2011-12-15'));
        $orig->gap($alt);
    }

    public function testGapWithSameEndingInterval(): void
    {
        $this->expectException(InvalidTimeRange::class);
        $orig = Period::fromDatepoint(new DateTime('2011-12-01'), new DateTime('2012-02-01'));
        $alt = Period::fromDatepoint(new DateTime('2012-01-15'), new DateTime('2012-02-01'));
        $orig->gap($alt);
    }

    public function testGapWithAdjacentInterval(): void
    {
        $orig = Period::fromDatepoint(new DateTime('2011-12-01'), new DateTime('2012-02-01'));
        $alt = Period::fromDatepoint(new DateTime('2012-02-01'), new DateTime('2012-02-02'));
        self::assertEquals(0, $orig->gap($alt)->getTimestampInterval());
    }

    /**
     * @dataProvider gapBoundaryResultProvider
     */
    public function testGapBoundaryTypeResult(string $boundary1, string $boundary2, string $expected): void
    {
        $interval0 = Period::fromDatepoint('2014-03-01', '2014-06-01', $boundary1);
        $interval1 = Period::fromDatepoint('2014-07-01', '2014-09-01', $boundary2);
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
        $interval1 = Period::fromDatepoint(new DateTimeImmutable('2015-01-01'), new DateTimeImmutable('2016-01-01'));
        $interval2 = Period::fromDatepoint(new DateTimeImmutable('2013-01-01'), new DateTimeImmutable('2014-01-01'));

        $this->expectException(InvalidTimeRange::class);
        $interval1->diff($interval2);
    }

    public function testDiffWithEqualsPeriod(): void
    {
        $period = Period::fromDatepoint(new DateTimeImmutable('2013-01-01'), new DateTimeImmutable('2014-01-01'));
        $alt = Period::fromDatepoint(new DateTimeImmutable('2013-01-01'), new DateTimeImmutable('2014-01-01'));

        self::assertTrue($alt->diff($period)->isEmpty());
        self::assertEquals($alt->diff($period), $period->diff($alt));
    }

    public function testDiffWithPeriodSharingStartingDatepoints(): void
    {
        $period = Period::fromDatepoint(new DateTimeImmutable('2013-01-01'), new DateTimeImmutable('2014-01-01'));
        $alt = Period::fromDatepoint(new DateTimeImmutable('2013-01-01'), new DateTimeImmutable('2013-04-01'));
        $sequence = $alt->diff($period);

        self::assertCount(1, $sequence);
        self::assertEquals(new DateTimeImmutable('2013-04-01'), $sequence[0]->getStartDate());
        self::assertEquals(new DateTimeImmutable('2014-01-01'), $sequence[0]->getEndDate());
        self::assertEquals($alt->diff($period), $period->diff($alt));
    }

    public function testDiffWithPeriodSharingEndingDatepoints(): void
    {
        $period = Period::fromDatepoint(new DateTimeImmutable('2013-01-01'), new DateTimeImmutable('2014-01-01'));
        $alt = Period::fromDatepoint(new DateTimeImmutable('2013-10-01'), new DateTimeImmutable('2014-01-01'));
        $sequence = $alt->diff($period);

        self::assertCount(1, $sequence);
        self::assertEquals(new DateTimeImmutable('2013-01-01'), $sequence[0]->getStartDate());
        self::assertEquals(new DateTimeImmutable('2013-10-01'), $sequence[0]->getEndDate());
        self::assertEquals($alt->diff($period), $period->diff($alt));
    }

    public function testDiffWithOverlapsPeriod(): void
    {
        $period = Period::fromDatepoint(new DateTimeImmutable('2013-01-01 10:00:00'), new DateTimeImmutable('2013-01-01 13:00:00'));
        $alt = Period::fromDatepoint(new DateTimeImmutable('2013-01-01 11:00:00'), new DateTimeImmutable('2013-01-01 14:00:00'));
        $sequence = $alt->diff($period);

        self::assertCount(2, $sequence);
        self::assertSame(3600.0, $sequence[0]->getTimestampInterval());
        self::assertSame(3600.0, $sequence[1]->getTimestampInterval());
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
        $interval0 = Period::fromDatepoint('2014-03-01', '2014-06-01', $boundary1);
        $interval1 = Period::fromDatepoint('2014-05-01', '2014-09-01', $boundary2);
        $sequence = $interval0->diff($interval1);

        if (0 < count($sequence)) {
            self::assertSame($expected1, $sequence[0]->getBoundaryType());
        }

        if (1 < count($sequence)) {
            self::assertSame($expected2, $sequence[1]->getBoundaryType());
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
                $interval0 = Period::fromDatepoint('2014-03-01', '2014-06-01', $bound1);
                $interval1 = Period::fromDatepoint('2014-05-01', '2014-08-01', $bound2);
                $sequence = $interval0->diff($interval1);
                $intersect = $interval0->intersect($interval1);

                if (0 < count($sequence)) {
                    self::assertTrue($sequence[0]->bordersOnStart($intersect));
                }

                if (1 < count($sequence)) {
                    self::assertTrue($sequence[1]->bordersOnEnd($intersect));
                }

                $sequence->push($intersect);
                $boundaries = $sequence->boundaries();
                if (null !== $boundaries) {
                    self::assertTrue($boundaries->equals($interval0->merge($interval1)));
                }
            }
        }
    }

    public function testSubtractWithOverlappingUnequalPeriods(): void
    {
        $periodA = Period::after('2000-01-01 10:00:00', '8 HOURS');
        $periodB = Period::after('2000-01-01 14:00:00', '6 HOURS');

        $diff1 = $periodA->subtract($periodB);

        self::assertCount(1, $diff1);
        self::assertEquals($periodA->getStartDate(), $diff1[0]->getStartDate());
        self::assertEquals($periodB->getStartDate(), $diff1[0]->getEndDate());

        $diff2 = $periodB->subtract($periodA);

        self::assertCount(1, $diff2);
        self::assertEquals($periodA->getEndDate(), $diff2[0]->getStartDate());
        self::assertEquals($periodB->getEndDate(), $diff2[0]->getEndDate());
    }

    public function testSubtractWithSeparatePeriods(): void
    {
        $periodA = Period::after('2000-01-01 10:00:00', '4 HOURS');
        $periodB = Period::after('2000-01-01 15:00:00', '3 HOURS');

        $diff1 = $periodA->subtract($periodB);

        self::assertCount(1, $diff1);
        self::assertTrue($diff1[0]->equals($periodA));

        $diff2 = $periodB->subtract($periodA);

        self::assertCount(1, $diff2);
        self::assertTrue($diff2[0]->equals($periodB));
    }

    public function testSubtractWithOnePeriodContainedInAnother(): void
    {
        $periodA = Period::after('2000-01-01 10:00:00', '8 HOURS');
        $periodB = Period::after('2000-01-01 15:00:00', '1 HOUR');

        $diff1 = $periodA->subtract($periodB);

        self::assertCount(2, $diff1);
        self::assertEquals($periodA->getStartDate(), $diff1[0]->getStartDate());
        self::assertEquals($periodB->getStartDate(), $diff1[0]->getEndDate());
        self::assertEquals($periodB->getEndDate(), $diff1[1]->getStartDate());
        self::assertEquals($periodA->getEndDate(), $diff1[1]->getEndDate());

        $diff2 = $periodB->subtract($periodA);

        self::assertCount(0, $diff2);
    }

    public function testSubtractWithEqualPeriodObjec(): void
    {
        $periodA = Period::after('2000-01-01 10:00:00', '8 HOURS');
        $diff = $periodA->subtract($periodA);

        self::assertCount(0, $diff);
        self::assertEquals($diff, $periodA->subtract($periodA));
    }
}
