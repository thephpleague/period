<?php

/**
 * League.Period (https://period.thephpleague.com)
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
use DateTimeZone;
use Generator;
use League\Period\Exception;
use League\Period\Period;
use TypeError;

/**
 * @coversDefaultClass \League\Period\Period
 */
class PeriodPropertiesTest extends TestCase
{
    public function testConstructorThrowExceptionIfUnknownBounday(): void
    {
        self::expectException(Exception::class);
        new Period(new DateTime('2014-01-13'), new DateTime('2014-01-20'), 'foobar');
    }

    public function testGetDateInterval(): void
    {
        $interval = new Period(new DateTimeImmutable('2012-02-01'), new DateTimeImmutable('2012-02-02'));
        self::assertSame(1, $interval->getDateInterval()->days);
    }

    public function testGetTimestampInterval(): void
    {
        $interval = new Period(new DateTimeImmutable('2012-02-01'), new DateTimeImmutable('2012-02-02'));
        self::assertSame(86400.0, $interval->getTimestampInterval());
    }

    /**
     * @dataProvider providerGetRangType
     */
    public function testGetRangeType(
        Period $interval,
        string $rangeType,
        bool $startIncluded,
        bool $startExcluded,
        bool $endIncluded,
        bool $endExcluded
    ): void {
        self::assertSame($rangeType, $interval->getBoundaryType());
        self::assertSame($startIncluded, $interval->isStartDateIncluded());
        self::assertSame($startExcluded, $interval->isStartDateExcluded());
        self::assertSame($endIncluded, $interval->isEndDateIncluded());
        self::assertSame($endExcluded, $interval->isEndDateExcluded());
    }

    public function providerGetRangType(): array
    {
        return [
            'left open right close' => [
                'interval' => Period::fromDay(2012, 8, 12),
                'rangeType' => Period::INCLUDE_START_EXCLUDE_END,
                'startIncluded' => true,
                'startExcluded' => false,
                'endIncluded' => false,
                'endExcluded' => true,
            ],
            'left close right open' => [
                'interval' => Period::around('2012-08-12', '1 HOUR', Period::EXCLUDE_START_INCLUDE_END),
                'rangeType' => Period::EXCLUDE_START_INCLUDE_END,
                'startIncluded' => false,
                'startExcluded' => true,
                'endIncluded' => true,
                'endExcluded' => false,
            ],
            'left open right open' => [
                'interval' => Period::after('2012-08-12', '1 DAY', Period::INCLUDE_ALL),
                'rangeType' => Period::INCLUDE_ALL,
                'startIncluded' => true,
                'startExcluded' => false,
                'endIncluded' => true,
                'endExcluded' => false,
            ],
            'left close right close' => [
                'interval' => Period::before('2012-08-12', '1 WEEK', Period::EXCLUDE_ALL),
                'rangeType' => Period::EXCLUDE_ALL,
                'startIncluded' => false,
                'startExcluded' => true,
                'endIncluded' => false,
                'endExcluded' => true,
            ],
        ];
    }

    /**
     * @dataProvider providerGetDatePeriod
     *
     * @param DateInterval|int|string $interval
     */
    public function testGetDatePeriod($interval, int $option, int $count): void
    {
        $period = new Period(new DateTime('2012-01-12'), new DateTime('2012-01-13'));
        $range = $period->getDatePeriod($interval, $option);
        self::assertCount($count, iterator_to_array($range));
    }

    public function providerGetDatePeriod(): array
    {
        return [
            'useDateInterval' => [new DateInterval('PT1H'), 0, 24],
            'useString' => ['2 HOUR', 0, 12],
            'useInt' => [9600, 0, 9],
            'useFloat' => [14400.0, 0, 6],
            'exclude start date useDateInterval' => [new DateInterval('PT1H'), DatePeriod::EXCLUDE_START_DATE, 23],
            'exclude start date useString' => ['2 HOUR', DatePeriod::EXCLUDE_START_DATE, 11],
            'exclude start date useInt' => [9600, DatePeriod::EXCLUDE_START_DATE, 8],
            'exclude start date useFloat' => [14400.0, DatePeriod::EXCLUDE_START_DATE, 5],
        ];
    }

    /**
     * @dataProvider providerGetDatePeriodBackwards
     *
     * @param DateInterval|int|string $interval
     */
    public function testGetDatePeriodBackwards($interval, int $option, int $count): void
    {
        $period = new Period(new DateTime('2012-01-12'), new DateTime('2012-01-13'));
        $range = $period->getDatePeriodBackwards($interval, $option);
        self::assertInstanceOf(Generator::class, $range);
        self::assertCount($count, iterator_to_array($range));
    }

    public function providerGetDatePeriodBackwards(): array
    {
        return [
            'useDateInterval' => [new DateInterval('PT1H'), 0, 24],
            'useString' => ['2 HOUR', 0, 12],
            'useInt' => [9600, 0, 9],
            'useFloat' => [14400.0, 0, 6],
            'exclude start date useDateInterval' => [new DateInterval('PT1H'), DatePeriod::EXCLUDE_START_DATE, 23],
            'exclude start date useString' => ['2 HOUR', DatePeriod::EXCLUDE_START_DATE, 11],
            'exclude start date useInt' => [9600, DatePeriod::EXCLUDE_START_DATE, 8],
            'exclude start date useFloat' => [14400.0, DatePeriod::EXCLUDE_START_DATE, 5],
        ];
    }
    public function testToString(): void
    {
        date_default_timezone_set('Africa/Nairobi');
        $period = new Period('2014-05-01', '2014-05-08');
        $res = (string) $period;
        self::assertContains('2014-04-30T21:00:00', $res);
        self::assertContains('2014-05-07T21:00:00', $res);
    }

    public function testJsonSerialize(): void
    {
        $period = Period::fromMonth(2015, 4);
        $json = json_encode($period);
        self::assertInternalType('string', $json);
        $res = json_decode($json);

        self::assertEquals($period->getStartDate(), new DateTimeImmutable($res->startDate));
        self::assertEquals($period->getEndDate(), new DateTimeImmutable($res->endDate));
    }

    public function testFormat(): void
    {
        date_default_timezone_set('Africa/Nairobi');
        self::assertSame('[2015-04, 2015-05)', Period::fromMonth(2015, 4)->format('Y-m'));
        self::assertSame(
            '[2015-04-01 Africa/Nairobi, 2015-04-01 Africa/Nairobi)',
            (new Period('2015-04-01', '2015-04-01'))->format('Y-m-d e')
        );
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
        self::assertEquals(-3600, $orig->timestampIntervalDiff($alt));
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
        $period = new Period(new DateTime('2012-01-12'), new DateTime('2012-01-13'));
        $range = $period->split(new DateInterval('PT1H'));
        $i = 0;
        foreach ($range as $innerPeriod) {
            ++$i;
        }
        self::assertSame(24, $i);
    }

    public function testSplitMustRecreateParentObject(): void
    {
        $period = new Period(new DateTime('2012-01-12'), new DateTime('2012-01-13'));
        $range = $period->split(new DateInterval('PT1H'));
        $total = null;
        foreach ($range as $part) {
            if (null === $total) {
                $total = $part;
                continue;
            }
            $total = $total->endingOn($part->getEndDate());
        }
        self::assertInstanceOf(Period::class, $total);
        self::assertTrue($total->equals($period));
    }

    public function testSplitWithLargeInterval(): void
    {
        $period = new Period(new DateTime('2012-01-12'), new DateTime('2012-01-13'));
        $range = [];
        foreach ($period->split(new DateInterval('P1Y')) as $innerPeriod) {
            $range[] = $innerPeriod;
        }
        self::assertCount(1, $range);
        self::assertTrue($range[0]->equals($period));
    }

    public function testSplitWithInconsistentInterval(): void
    {
        $last = null;
        $period = new Period(new DateTime('2012-01-12'), new DateTime('2012-01-13'));

        foreach ($period->split(new DateInterval('PT10H')) as $innerPeriod) {
            $last = $innerPeriod;
        }
        self::assertNotNull($last);
        self::assertSame(14400.0, $last->getTimestampInterval());
    }

    public function testSplitBackwards(): void
    {
        $period = new Period(new DateTime('2015-01-01'), new DateTime('2015-01-04'));
        $range = $period->splitBackwards(new DateInterval('P1D'));
        $list = [];
        foreach ($range as $innerPeriod) {
            $list[] = $innerPeriod;
        }

        $result = array_map(function (Period $range) {
            return [
                'start' => $range->getStartDate()->format('Y-m-d H:i:s'),
                'end'   => $range->getEndDate()->format('Y-m-d H:i:s'),
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
        $period = new Period(new DateTime('2010-01-01'), new DateTime('2010-01-02'));
        $last = null;
        foreach ($period->splitBackwards(new DateInterval('PT10H')) as $innerPeriod) {
            $last = $innerPeriod;
        }

        self::assertNotNull($last);
        self::assertEquals(14400.0, $last->getTimestampInterval());
    }

    public function testSplitDaylightSavingsDayIntoHoursEndInterval(): void
    {
        date_default_timezone_set('Canada/Central');
        $period = new Period(new DateTime('2018-11-04 00:00:00.000000'), new DateTime('2018-11-04 05:00:00.000000'));
        $splits = $period->split(new DateInterval('PT30M'));
        $i = 0;
        foreach ($splits as $inner_period) {
            ++$i;
        }
        self::assertSame(10, $i);
    }

    public function testSplitBackwardsDaylightSavingsDayIntoHoursStartInterval(): void
    {
        date_default_timezone_set('Canada/Central');
        $period = new Period(new DateTime('2018-04-11 00:00:00.000000'), new DateTime('2018-04-11 05:00:00.000000'));
        $splits = $period->splitBackwards(new DateInterval('PT30M'));
        $i = 0;
        foreach ($splits as $inner_period) {
            ++$i;
        }
        self::assertSame(10, $i);
    }
}
