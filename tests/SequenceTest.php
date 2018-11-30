<?php

/**
 * League.Period (https://period.thephpleague.com).
 *
 * (c) Ignace Nyamagana Butera <nyamsprod@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace LeagueTest\Period;

use DateTimeImmutable;
use League\Period\InvalidIndex;
use League\Period\Period;
use League\Period\Sequence;
use PHPUnit\Framework\TestCase;
use function League\Period\day;
use function League\Period\interval_after;
use function League\Period\interval_around;
use function League\Period\iso_week;
use function League\Period\month;

/**
 * @coversDefaultClass League\Period\Sequence
 */
final class SequenceTest extends TestCase
{
    public function testIsEmpty(): void
    {
        $sequence = new Sequence();
        self::assertTrue($sequence->isEmpty());
        self::assertCount(0, $sequence);
        self::assertNull($sequence->getBoundaries());
        $sequence->push(day('2012-06-23'));
        self::assertFalse($sequence->isEmpty());
        self::assertCount(1, $sequence);
        self::assertInstanceOf(Period::class, $sequence->getBoundaries());
    }

    public function testConstructor(): void
    {
        $sequence = new Sequence(day('2012-06-23'), day('2012-06-23'));
        self::assertCount(2, $sequence);
        foreach ($sequence as $event) {
            self::assertInstanceOf(Period::class, $event);
        }
    }

    public function testRemove(): void
    {
        $event1 = day('2012-06-23');
        $event2 = day('2012-06-23');
        $sequence = new Sequence($event1, $event2);
        self::assertSame($event1, $sequence->remove(0));
        self::assertTrue($sequence->contains($event1));
        self::assertCount(1, $sequence);
        self::assertSame($event2, $sequence->remove(0));
        self::assertCount(0, $sequence);
        self::assertFalse($sequence->contains($event2));
        self::expectException(InvalidIndex::class);
        $sequence->remove(1);
    }

    public function testGetter(): void
    {
        $event1 = day('2012-06-23');
        $event2 = day('2012-06-23');
        $event3 = day('2012-06-25');
        $sequence = new Sequence($event1, $event2);
        self::assertInstanceOf(Period::class, $sequence->getBoundaries());
        self::assertTrue($sequence->contains($event2));
        self::assertTrue($sequence->contains($event1));
        self::assertTrue($sequence->contains(day('2012-06-23')));
        self::assertSame($event2, $sequence->get(1));
        self::assertSame(0, $sequence->indexOf(day('2012-06-23')));
        self::assertNull($sequence->indexOf(day('2014-06-23')));
        $sequence->push($event3);
        self::assertCount(3, $sequence);
        self::assertSame(2, $sequence->indexOf($event3));
        $sequence->clear();
        self::assertTrue($sequence->isEmpty());
        self::assertNull($sequence->getBoundaries());
    }

    public function testGetThrowsException(): void
    {
        self::expectException(InvalidIndex::class);
        (new Sequence())->get(3);
    }

    public function testSetter(): void
    {
        $sequence = new Sequence(day('2011-06-23'), day('2011-06-23'));
        $sequence->set(0, day('2011-06-23'));
        self::assertEquals(day('2011-06-23'), $sequence->get(0));
        $sequence->set(1, day('2012-06-23'));
        $sequence->set(0, day('2013-06-23'));
        self::assertEquals(day('2012-06-23'), $sequence->get(1));
        self::assertEquals(day('2013-06-23'), $sequence->get(0));
        self::expectException(InvalidIndex::class);
        $sequence->set(3, day('2013-06-23'));
    }

    public function testFilterReturnsNewInstance(): void
    {
        $sequence =new Sequence(day('2012-06-23'), day('2012-06-12'));

        $filter = function (Period $period) {
            return $period->getStartDate() == new DateTimeImmutable('2012-06-23');
        };

        $newCollection = $sequence->filter($filter);

        self::assertNotEquals($newCollection, $sequence);
        self::assertCount(1, $newCollection);
    }


    public function testFilterReturnsSameInstance(): void
    {
        $sequence = new Sequence(day('2012-06-23'), day('2012-06-12'));

        $filter = static function (Period $interval) {
            return true;
        };

        self::assertSame($sequence, $sequence->filter($filter));
    }

    public function testSortedReturnsSameInstance(): void
    {
        $sequence = new Sequence(day('2012-06-23'), day('2012-06-12'));
        $sort = function (Period $event1, Period $event2) {
            return 0;
        };

        self::assertSame($sequence, $sequence->sorted($sort));
    }

    public function testSortedReturnsNewInstance(): void
    {
        $sequence = new Sequence(month(2012, 6), day('2012-06-23'), iso_week(2018, 3));
        $sort = static function (Period $event1, Period $event2) {
            return $event1->durationCompare($event2);
        };

        self::assertNotSame($sequence, $sequence->sorted($sort));
    }

    public function testSort(): void
    {
        $day1 = day('2012-06-23');
        $day2 = day('2012-06-12');
        $sequence = new Sequence($day1, $day2);
        self::assertSame([0 => $day1, 1 => $day2], $sequence->toArray());
        $compare = static function (Period $period1, Period $period2): int {
            return $period1->getStartDate() <=> $period2->getStartDate();
        };
        $sequence->sort($compare);
        self::assertSame([1 => $day2, 0 => $day1], $sequence->toArray());
    }

    public function testSome(): void
    {
        $interval = interval_after('2012-02-01 12:00:00', '1 HOUR');
        $predicate = static function (Period $event) use ($interval) {
            return $interval->overlaps($event);
        };
        $sequence = new Sequence(day('2012-02-01'), day('2013-02-01'), day('2014-02-01'));
        self::assertTrue($sequence->some($predicate));
        self::assertFalse((new Sequence())->some($predicate));
    }

    public function testEvery(): void
    {
        $sequence = new Sequence(day('2012-02-01'), day('2013-02-01'), day('2014-02-01'));

        $interval = interval_after('2012-01-01', '5 YEARS');
        $predicate = function (Period $event) use ($interval) {
            return $interval->contains($event);
        };

        self::assertTrue($sequence->every($predicate));
        self::assertFalse((new Sequence())->every($predicate));
    }

    /**
     * Intersections test 1.
     *
     *               [------------)
     *                    [--)
     *                    [-------)
     *
     *                 =
     *
     *                    [--)
     */
    public function testGetIntersections1(): void
    {
        $sequence = new Sequence(
            new Period('2018-01-01', '2018-01-31'),
            new Period('2018-01-10', '2018-01-15'),
            new Period('2018-01-10', '2018-01-31')
        );
        $intersections = $sequence->getIntersections();
        self::assertCount(1, $intersections);
        self::assertSame('[2018-01-10, 2018-01-15)', $intersections->get(0)->format('Y-m-d'));
    }

    /**
     * Intersections test 2.
     *
     *        [--------)
     *                     [--)
     *                            [------)
     *               [---------------)
     *
     *                 =
     *
     *               [-)   [--)   [--)
     */
    public function testGetIntersections2(): void
    {
        $sequence = new Sequence(
            new Period('2018-01-01', '2018-01-31'),
            new Period('2018-02-10', '2018-02-20'),
            new Period('2018-03-01', '2018-03-31'),
            new Period('2018-01-20', '2018-03-10')
        );
        $intersections = $sequence->getIntersections();
        self::assertCount(3, $intersections);
        self::assertSame('[2018-01-20, 2018-01-31)', $intersections->get(0)->format('Y-m-d'));
        self::assertSame('[2018-02-10, 2018-02-20)', $intersections->get(1)->format('Y-m-d'));
        self::assertSame('[2018-03-01, 2018-03-10)', $intersections->get(2)->format('Y-m-d'));
    }

    /**
     * gaps test 1.
     *
     *           [--)
     *                    [----)
     *        [-------)
     *
     *                 =
     *
     *                [---)
     */
    public function testGaps1(): void
    {
        $sequence = new Sequence(
            day('2018-11-29'),
            interval_after('2018-11-29 + 7 DAYS', '1 DAY'),
            interval_around('2018-11-29', '4 DAYS')
        );

        $gaps = $sequence->getGaps();
        self::assertCount(1, $gaps);
        self::assertSame('[2018-12-03, 2018-12-06)', $gaps->get(0)->format('Y-m-d'));
    }

    /**
     * gaps test 2.
     *
     * No gaps expected
     *
     *          [--)
     *         [----)
     */
    public function testGaps2(): void
    {
        $sequence = new Sequence(
            day('2018-11-29'),
            interval_around('2018-11-29', '4 DAYS')
        );

        $gaps = $sequence->getGaps();
        self::assertTrue($gaps->isEmpty());
    }
}
